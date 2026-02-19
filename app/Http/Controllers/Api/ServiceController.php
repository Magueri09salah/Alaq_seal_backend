<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Service, PricingFactor};
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * GET /api/v1/services
     */
    public function index()
    {
        $services = Service::active()->ordered()->get();
        return response()->json(['data' => $services]);
    }

    /**
     * GET /api/v1/services/{id}/products
     * ?subcategory=graphique|minerale|organique|urbaine|mur|sol
     */
    public function products(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        $query   = $service->products()->active()->ordered();

        if ($request->filled('subcategory')) {
            $query->where('subcategory', $request->subcategory);
        }

        // Eager-load cases + materials so frontend has everything for Step 4
        $products = $query->with(['cases' => function ($q) {
            $q->orderBy('display_order')->with(['materials' => function ($q2) {
                $q2->orderBy('step_order');
            }]);
        }])->get();

        return response()->json(['data' => $products]);
    }

    /**
     * GET /api/v1/pricing-factors
     * Returns factors grouped by type: { height: [...], condition: [...], ... }
     */
    public function pricingFactors()
    {
        $grouped = PricingFactor::orderBy('type')
            ->orderBy('display_order')
            ->get()
            ->groupBy('type')
            ->map(fn($group) => $group->values());

        return response()->json(['data' => $grouped]);
    }
}