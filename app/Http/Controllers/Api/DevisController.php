<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Devis;
use App\Services\DevisCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DevisController extends Controller
{
    public function __construct(private DevisCalculatorService $calculator) {}

    /**
     * GET /api/v1/devis
     */
    public function index(Request $request)
    {
        $query = Devis::where('user_id', Auth::id())
            ->with(['service', 'product', 'productCase'])
            ->recent();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $devis = $query->paginate($request->per_page ?? 10);
        return response()->json(['data' => $devis]);
    }

    /**
     * GET /api/v1/devis/stats
     */
    public function stats()
    {
        $uid = Auth::id();
        return response()->json(['data' => [
            'total'      => Devis::where('user_id', $uid)->count(),
            'draft'      => Devis::where('user_id', $uid)->where('status', 'draft')->count(),
            'saved'      => Devis::where('user_id', $uid)->where('status', 'saved')->count(),
            'submitted'  => Devis::where('user_id', $uid)->where('status', 'submitted')->count(),
            'reviewed'   => Devis::where('user_id', $uid)->where('status', 'reviewed')->count(),
            'total_value'=> Devis::where('user_id', $uid)->sum('total_ttc'),
        ]]);
    }

    /**
     * GET /api/v1/devis/{id}
     */
    public function show($id)
    {
        $devis = Devis::where('user_id', Auth::id())
            ->with(['service', 'product', 'productCase.materials'])
            ->findOrFail($id);

        return response()->json(['data' => $devis]);
    }

    /**
     * POST /api/v1/devis/calculate
     * Preview calculation — does NOT save
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'product_id'        => 'required|exists:products,id',
            'product_case_id'   => 'required|exists:product_cases,id',
            'longueur'          => 'required|numeric|min:0.1',
            'largeur'           => 'required|numeric|min:0.1',
            'hauteur'           => 'nullable|numeric|min:0',
            'nombre_murs'       => 'nullable|integer|min:1|max:20',
            // 'factor_height'     => 'nullable|numeric',
            // 'factor_condition'  => 'nullable|numeric',
            // 'factor_complexity' => 'nullable|numeric',
            // 'factor_region'     => 'nullable|numeric',
        ]);

        $result = $this->calculator->calculate($validated);

        return response()->json(['data' => $result]);
    }

    /**
     * POST /api/v1/devis
     * Create and save a devis
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id'        => 'required|exists:services,id',
            'product_id'        => 'required|exists:products,id',
            'product_case_id'   => 'required|exists:product_cases,id',
            'subcategory'       => 'nullable|string',
            'status'            => 'nullable|in:draft,saved',
            'longueur'          => 'required|numeric|min:0.1',
            'largeur'           => 'required|numeric|min:0.1',
            'hauteur'           => 'nullable|numeric|min:0',
            'nombre_murs'       => 'nullable|integer|min:1|max:20',
            'project_name'      => 'nullable|string|max:255',
            'project_location'  => 'nullable|string|max:255',
            'notes'             => 'nullable|string',
            // 'factor_height'     => 'nullable|numeric',
            // 'factor_condition'  => 'nullable|numeric',
            // 'factor_complexity' => 'nullable|numeric',
            // 'factor_region'     => 'nullable|numeric',
        ]);

        $calc = $this->calculator->calculate($validated);

        $devis = Devis::create([
            'user_id'              => Auth::id(),
            'devis_number'         => Devis::generateNumber(Auth::id()),
            'service_id'           => $validated['service_id'],
            'product_id'           => $validated['product_id'],
            'product_case_id'      => $validated['product_case_id'],
            'subcategory'          => $validated['subcategory'] ?? null,
            'status'               => $validated['status'] ?? 'draft',
            'project_name'         => $validated['project_name'] ?? null,
            'project_location'     => $validated['project_location'] ?? null,
            'notes'                => $validated['notes'] ?? null,
            'longueur'             => $validated['longueur'],
            'largeur'              => $validated['largeur'],
            'hauteur'              => $validated['hauteur'] ?? 0,
            'nombre_murs'          => $validated['nombre_murs'] ?? 4,
            'surface_area'         => $calc['surface_area'],
            // 'factor_height'        => $calc['factor_height'],
            // 'factor_condition'     => $calc['factor_condition'],
            // 'factor_complexity'    => $calc['factor_complexity'],
            // 'factor_region'        => $calc['factor_region'],
            'base_price'           => $calc['base_price'],
            // 'price_with_factors'   => $calc['price_with_factors'],
            'fixed_costs'          => $calc['fixed_costs'],
            'subtotal_ht'          => $calc['subtotal_ht'],
            'tva_rate'             => $calc['tva_rate'],
            'tva_amount'           => $calc['tva_amount'],
            'total_ttc'            => $calc['total_ttc'],
            'calculated_materials' => $calc['calculated_materials'],
            // 'estimated_days'       => $calc['estimated_days'],
            // 'preparation_days'     => $calc['preparation_days'],
            // 'drying_days'          => $calc['drying_days'],
        ]);

        return response()->json(['data' => $devis->load(['service', 'product', 'productCase'])], 201);
    }

    /**
     * POST /api/v1/devis/{id}/submit
     */
    public function submit($id)
    {
        $devis = Devis::where('user_id', Auth::id())->findOrFail($id);

        if (!$devis->canEdit()) {
            return response()->json(['error' => 'Ce devis ne peut plus être modifié'], 422);
        }

        $devis->submit();

        return response()->json(['data' => $devis->fresh(['service', 'product', 'productCase'])]);
    }

    /**
     * DELETE /api/v1/devis/{id}
     */
    public function destroy($id)
    {
        $devis = Devis::where('user_id', Auth::id())->findOrFail($id);

        if (!$devis->canDelete()) {
            return response()->json(['error' => 'Ce devis ne peut pas être supprimé'], 422);
        }

        $devis->delete();

        return response()->json(['message' => 'Devis supprimé avec succès']);
    }
}