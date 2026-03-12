<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ToitureDevis;
use App\Services\ToitureCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ToitureDevisController extends Controller
{
    public function __construct(private ToitureCalculatorService $calculator) {}

    /**
     * GET /api/v1/toiture/devis
     * List all toiture devis for authenticated user
     */
    public function index(Request $request)
    {
        $query = ToitureDevis::where('user_id', Auth::id())->recent();

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        $devis = $query->paginate($request->per_page ?? 10);
        
        return response()->json(['data' => $devis]);
    }

    /**
     * GET /api/v1/toiture/devis/{id}
     * Show single toiture devis
     */
    public function show($id)
    {
        $devis = ToitureDevis::where('user_id', Auth::id())
            ->with('user')
            ->findOrFail($id);

        return response()->json(['data' => $devis]);
    }

    /**
     * POST /api/v1/toiture/calculate
     * Calculate toiture devis (preview - does not save)
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:toiture,mur,salle_bain',
            
            // Toiture specific
            'toiture_type' => 'required_if:type,toiture|in:accessible,non_accessible',
            'isolation' => 'required_if:type,toiture|boolean',
            'finition' => 'nullable|in:autoprotegee,lestage',
            
            // FIXED: longueur/largeur not required for salle_bain
            'longueur' => 'required_unless:type,salle_bain|numeric|min:0.1',
            'largeur' => 'nullable|numeric|min:0.1',
            
            'hauteur_acrotere' => 'nullable|numeric|min:0',
            'nombre_evacuations' => 'nullable|integer|min:1',
            'chape_existante' => 'nullable|boolean',
            
            // Mur specific
            'hauteur' => 'nullable|numeric|min:0',
            'nombre_murs' => 'nullable|integer|min:1',
            'drain' => 'nullable|boolean',
            'water_level' => 'nullable|in:humidite,infiltration,nappe',

            // Salle de bain specific
            'sdb_type' => 'required_if:type,salle_bain|in:avec_bac,italienne',
            'support' => 'required_if:type,salle_bain|in:ciment,carrelage',
            'surface_sol_totale' => 'required_if:type,salle_bain|numeric|min:0',
            
            // For avec_bac - only required if sdb_type is avec_bac
            'surface_bac' => 'nullable|numeric|min:0',
            'longueur_murs' => 'required_if:sdb_type,avec_bac|nullable|numeric|min:0',
            'largeur_murs' => 'required_if:sdb_type,avec_bac|nullable|numeric|min:0',
            'hauteur_murs' => 'required_if:sdb_type,avec_bac|nullable|numeric|min:0',
            
            // For italienne - only required if sdb_type is italienne
            'surface_zone_douche' => 'required_if:sdb_type,italienne|nullable|numeric|min:0',
            'longueur_murs_douche' => 'required_if:sdb_type,italienne|nullable|numeric|min:0',
            'largeur_murs_douche' => 'required_if:sdb_type,italienne|nullable|numeric|min:0',
            'hauteur_murs_douche' => 'required_if:sdb_type,italienne|nullable|numeric|min:0',
            'longueur_murs_piece' => 'required_if:sdb_type,italienne|nullable|numeric|min:0',
            'largeur_murs_piece' => 'required_if:sdb_type,italienne|nullable|numeric|min:0',
            'hauteur_murs_piece' => 'required_if:sdb_type,italienne|nullable|numeric|min:0',
        ]);

        $result = $this->calculator->calculate($validated);

        return response()->json(['data' => $result]);
    }

    /**
     * POST /api/v1/toiture/devis
     * Create and save toiture devis
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:toiture,mur,salle_bain',
            'status' => 'nullable|in:draft,saved',
            'calculation' => 'required|array',
            'project_name' => 'nullable|string|max:255',
            'project_location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            
            // TOITURE specific fields
            'toiture_type' => 'nullable|string',
            'isolation' => 'nullable|boolean',
            'finition' => 'nullable|string',
            'hauteur_acrotere' => 'nullable|numeric',
            'nombre_evacuations' => 'nullable|integer',
            'chape_existante' => 'nullable|boolean',
            
            // MUR specific fields
            'water_level' => 'nullable|string|in:humidite,infiltration,nappe',
            'drain' => 'nullable|boolean',
            'hauteur_technique' => 'nullable|numeric',
            
            // SALLE_BAIN specific fields - THESE WERE MISSING!
            'sdb_type' => 'nullable|string|in:avec_bac,italienne',
            'support' => 'nullable|string|in:ciment,carrelage',
            'salle_bain_data' => 'nullable|array',
            'surface_sol_totale' => 'nullable|numeric',
            'surface_bac' => 'nullable|numeric',
            'longueur_murs' => 'nullable|numeric',
            'largeur_murs' => 'nullable|numeric',
            'hauteur_murs' => 'nullable|numeric',
            'surface_zone_douche' => 'nullable|numeric',
            'longueur_murs_douche' => 'nullable|numeric',
            'largeur_murs_douche' => 'nullable|numeric',
            'hauteur_murs_douche' => 'nullable|numeric',
            'longueur_murs_piece' => 'nullable|numeric',
            'largeur_murs_piece' => 'nullable|numeric',
            'hauteur_murs_piece' => 'nullable|numeric',
            
            // Shared dimensional fields
            'longueur' => 'required|numeric',
            'largeur' => 'required|numeric',
            'perimetre' => 'nullable|numeric',
            'hauteur' => 'nullable|numeric',
            'nombre_murs' => 'nullable|integer',
        ]);

        // Build salle_bain_data if this is a salle_bain devis
        $salleBainData = null;
        if ($validated['type'] === 'salle_bain') {
            // If salle_bain_data is sent as a complete object (from frontend), use it
            if (isset($validated['salle_bain_data'])) {
                $salleBainData = $validated['salle_bain_data'];
            } else {
                // Otherwise, build it from individual fields
                $salleBainData = [
                    'sdb_type' => $validated['sdb_type'] ?? null,
                    'support' => $validated['support'] ?? null,
                    'surface_sol_totale' => $validated['surface_sol_totale'] ?? null,
                    'surface_bac' => $validated['surface_bac'] ?? null,
                    'longueur_murs' => $validated['longueur_murs'] ?? null,
                    'largeur_murs' => $validated['largeur_murs'] ?? null,
                    'hauteur_murs' => $validated['hauteur_murs'] ?? null,
                    'surface_zone_douche' => $validated['surface_zone_douche'] ?? null,
                    'longueur_murs_douche' => $validated['longueur_murs_douche'] ?? null,
                    'largeur_murs_douche' => $validated['largeur_murs_douche'] ?? null,
                    'hauteur_murs_douche' => $validated['hauteur_murs_douche'] ?? null,
                    'longueur_murs_piece' => $validated['longueur_murs_piece'] ?? null,
                    'largeur_murs_piece' => $validated['largeur_murs_piece'] ?? null,
                    'hauteur_murs_piece' => $validated['hauteur_murs_piece'] ?? null,
                ];
            }
        }

        $calc = $validated['calculation'];

        $devis = ToitureDevis::create([
            'user_id' => Auth::id(),
            'devis_number' => ToitureDevis::generateNumber(Auth::id()),
            'status' => $validated['status'] ?? 'draft',
            'project_name' => $validated['project_name'] ?? null,
            'project_location' => $validated['project_location'] ?? null,
            'notes' => $validated['notes'] ?? null,
            
            // Type
            'type' => $validated['type'],
            
            // TOITURE fields
            'toiture_type' => $validated['toiture_type'] ?? null,
            'isolation' => $validated['isolation'] ?? null,
            'finition' => $validated['finition'] ?? null,
            'hauteur_acrotere' => $validated['hauteur_acrotere'] ?? null,
            'nombre_evacuations' => $validated['nombre_evacuations'] ?? 1,
            'chape_existante' => $validated['chape_existante'] ?? true,
            
            // MUR fields
            'water_level' => $validated['water_level'] ?? null,
            'drain' => $validated['drain'] ?? false,
            'hauteur_technique' => $validated['hauteur_technique'] ?? $calc['hauteur_technique'] ?? null,
            
            // SALLE_BAIN fields
            'sdb_type' => $validated['sdb_type'] ?? null,
            'support' => $validated['support'] ?? null,
            
            // Dimensional fields
            'longueur' => $validated['longueur'],
            'largeur' => $validated['largeur'],
            'perimetre' => $validated['perimetre'] ?? $calc['perimetre'] ?? null,
            'hauteur' => $validated['hauteur'] ?? null,
            'nombre_murs' => $validated['nombre_murs'] ?? null,
            
            // Calculated values
            'surface_brute' => $calc['surface_brute'],
            'surface_technique' => $calc['surface_technique'] ?? null,
            'surface_releves' => $calc['surface_releves'] ?? null,
            
            // Financial
            'total_ht' => $calc['total_ht'],
            'tva_rate' => 20.00,
            'tva_amount' => $calc['total_ht'] * 0.2,
            'total_ttc' => $calc['total_ht'] * 1.2,
            
            // Materials and salle_bain_data
            'materials' => $calc['materials'],
            'salle_bain_data' => $salleBainData,
        ]);

        return response()->json(['data' => $devis->load('user')], 201);
    }

    /**
     * POST /api/v1/toiture/devis/{id}/submit
     * Submit devis to AlaqSeal
     */
    public function submit($id)
    {
        $devis = ToitureDevis::where('user_id', Auth::id())->findOrFail($id);

        if (!$devis->canEdit()) {
            return response()->json(['error' => 'Ce devis ne peut plus être modifié'], 422);
        }

        $devis->submit();

        return response()->json(['data' => $devis->fresh('user')]);
    }

    /**
     * DELETE /api/v1/toiture/devis/{id}
     * Delete devis
     */
    public function destroy($id)
    {
        $devis = ToitureDevis::where('user_id', Auth::id())->findOrFail($id);

        if (!$devis->canDelete()) {
            return response()->json(['error' => 'Ce devis ne peut pas être supprimé'], 422);
        }

        $devis->delete();

        return response()->json(['message' => 'Devis supprimé avec succès']);
    }

    /**
     * GET /api/v1/toiture/stats
     * Get statistics
     */
    public function stats()
    {
        $uid = Auth::id();
        
        return response()->json(['data' => [
            'total' => ToitureDevis::where('user_id', $uid)->count(),
            'draft' => ToitureDevis::where('user_id', $uid)->byStatus('draft')->count(),
            'saved' => ToitureDevis::where('user_id', $uid)->byStatus('saved')->count(),
            'submitted' => ToitureDevis::where('user_id', $uid)->byStatus('submitted')->count(),
            'reviewed' => ToitureDevis::where('user_id', $uid)->byStatus('reviewed')->count(),
            'total_value' => ToitureDevis::where('user_id', $uid)->sum('total_ttc'),
            'by_type' => [
                'toiture' => ToitureDevis::where('user_id', $uid)->byType('toiture')->count(),
                'mur' => ToitureDevis::where('user_id', $uid)->byType('mur')->count(),
                'salle_bain' => ToitureDevis::where('user_id', $uid)->byType('salle_bain')->count(),
            ],
        ]]);
    }

    /**
     * GET /api/v1/toiture/devis/{id}/download-pdf
     * Download PDF
     */
    public function downloadPdf($id)
    {
        $devis = ToitureDevis::where('user_id', Auth::id())
            ->with('user')
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.toiture_devis', ['devis' => $devis])
            ->setPaper('a4', 'portrait');

        $filename =  $devis->devis_number . '.pdf';

        return $pdf->download($filename);
    }
}