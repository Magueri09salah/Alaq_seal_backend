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
            'longueur' => 'required|numeric|min:0.1',
            'largeur' => 'required|numeric|min:0.1',
            'perimetre' => 'required_if:type,toiture|numeric|min:0',
            'hauteur_acrotere' => 'nullable|numeric|min:0',
            'nombre_evacuations' => 'nullable|integer|min:1',
            'chape_existante' => 'nullable|boolean',
            // Standard (mur/salle_bain)
            'hauteur' => 'nullable|numeric|min:0',
            'nombre_murs' => 'nullable|integer|min:1',
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
            // Type-specific data
            'toiture_type' => 'nullable|string',
            'isolation' => 'nullable|boolean',
            'finition' => 'nullable|string',
            'longueur' => 'required|numeric',
            'largeur' => 'required|numeric',
            'perimetre' => 'nullable|numeric',
            'hauteur_acrotere' => 'nullable|numeric',
            'nombre_evacuations' => 'nullable|integer',
            'chape_existante' => 'nullable|boolean',
            'hauteur' => 'nullable|numeric',
            'nombre_murs' => 'nullable|integer',
        ]);

        $calc = $validated['calculation'];

        $devis = ToitureDevis::create([
            'user_id' => Auth::id(),
            'devis_number' => ToitureDevis::generateNumber(Auth::id()),
            'status' => $validated['status'] ?? 'draft',
            'project_name' => $validated['project_name'] ?? null,
            'project_location' => $validated['project_location'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'type' => $validated['type'],
            'toiture_type' => $validated['toiture_type'] ?? null,
            'isolation' => $validated['isolation'] ?? null,
            'finition' => $validated['finition'] ?? null,
            'longueur' => $validated['longueur'],
            'largeur' => $validated['largeur'],
            'perimetre' => $validated['perimetre'] ?? null,
            'hauteur_acrotere' => $validated['hauteur_acrotere'] ?? null,
            'hauteur' => $validated['hauteur'] ?? null,
            'nombre_murs' => $validated['nombre_murs'] ?? null,
            'nombre_evacuations' => $validated['nombre_evacuations'] ?? 1,
            'chape_existante' => $validated['chape_existante'] ?? true,
            'surface_brute' => $calc['surface_brute'],
            'surface_technique' => $calc['surface_technique'] ?? null,
            'surface_releves' => $calc['surface_releves'] ?? null,
            'total_ht' => $calc['total_ht'],
            'tva_rate' => 20.00,
            'tva_amount' => $calc['total_ht'] * 0.2,
            'total_ttc' => $calc['total_ht'] * 1.2,
            'materials' => $calc['materials'],
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