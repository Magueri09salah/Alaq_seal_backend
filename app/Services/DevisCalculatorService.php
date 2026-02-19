<?php

namespace App\Services;

use App\Models\{Product, ProductCase, CaseMaterial};

class DevisCalculatorService
{
    /**
     * Calculate everything for a devis
     *
     * @param array $data {
     *   product_id, product_case_id,
     *   longueur, largeur, hauteur, nombre_murs,
     *   factor_height, factor_condition, factor_complexity, factor_region
     * }
     */
    public function calculate(array $data): array
    {
        $product     = Product::with('service')->findOrFail($data['product_id']);
        $productCase = ProductCase::with('materials')->findOrFail($data['product_case_id']);

        // ── 1. Calculate surfaces & perimeters ──────────────────────
        $longueur    = (float) ($data['longueur']    ?? 0);
        $largeur     = (float) ($data['largeur']     ?? 0);
        $hauteur     = (float) ($data['hauteur']     ?? 0);
        $nombreMurs  = (int)   ($data['nombre_murs'] ?? 4);

        $surfaceMurs = $longueur * $hauteur * $nombreMurs;
        $surfaceSol  = $longueur * $largeur;
        $perimetreMurs = $longueur * $nombreMurs;
        $perimetreSol  = 2 * ($longueur + $largeur);

        // Determine primary surface based on product subcategory
        $subcategory = $product->subcategory;
        if ($subcategory === 'sol' || in_array($product->code, ['sel_sol_salle_eau', 'resine_epoxy_sol'])) {
            $surfacePrimaire   = $surfaceSol;
            $perimetrePrimaire = $perimetreSol;
        } elseif ($product->code === 'toiture_terrasse') {
            $surfacePrimaire   = $surfaceSol;   // longueur × largeur
            $perimetrePrimaire = $perimetreSol;
        } else {
            $surfacePrimaire   = $surfaceMurs;
            $perimetrePrimaire = $perimetreMurs;
        }

        $surfaceArea = $surfacePrimaire; // the "surface" saved on the devis

        // ── 2. Calculate materials from DTU formulas ─────────────────
        $materials = [];
        foreach ($productCase->materials as $mat) {
            $quantity = match ($mat->formula_type) {
                'surface'   => $surfacePrimaire * $mat->formula_factor,
                'perimetre' => $perimetrePrimaire * $mat->formula_factor,
                'longueur'  => $longueur * $mat->formula_factor,
                'unite'     => $mat->formula_factor,              // fixed units (e.g. 1 siphon collar)
                default     => $surfacePrimaire * $mat->formula_factor,
            };

            $materials[] = [
                'step'        => $mat->step_order,
                'name'        => $mat->name,
                'type'        => $mat->type,
                'quantity'    => round($quantity, 2),
                'unit'        => $mat->unit,
                'is_optional' => $mat->is_optional,
            ];
        }

        // ── 3. Price calculation ─────────────────────────────────────
        $pricePerM2  = ($product->price_min + $product->price_max) / 2;
        $basePrice   = $surfaceArea * $pricePerM2;

        $factorHeight     = (float) ($data['factor_height']     ?? 1.00);
        $factorCondition  = (float) ($data['factor_condition']  ?? 1.00);
        $factorComplexity = (float) ($data['factor_complexity'] ?? 1.00);
        $factorRegion     = (float) ($data['factor_region']     ?? 1.00);

        $combinedFactor   = $factorHeight * $factorCondition * $factorComplexity * $factorRegion;
        $priceWithFactors = $basePrice * $combinedFactor;

        $fixedCosts  = $this->getFixedCosts($product->service->code);
        $subtotalHt  = $priceWithFactors + $fixedCosts;
        $tvaRate     = 20;
        $tvaAmount   = $subtotalHt * ($tvaRate / 100);
        $totalTtc    = $subtotalHt + $tvaAmount;

        // ── 4. Duration estimate ──────────────────────────────────────
        [$prepDays, $dryDays] = $this->estimateDuration($product->service->code, $surfaceArea);
        $estimatedDays = $prepDays + $dryDays;

        return [
            // Dimensions
            'longueur'          => $longueur,
            'largeur'           => $largeur,
            'hauteur'           => $hauteur,
            'nombre_murs'       => $nombreMurs,
            'surface_area'      => round($surfaceArea, 2),
            'surface_murs'      => round($surfaceMurs, 2),
            'surface_sol'       => round($surfaceSol, 2),
            'perimetre_murs'    => round($perimetreMurs, 2),
            'perimetre_sol'     => round($perimetreSol, 2),

            // Materials list (DTU)
            'calculated_materials' => $materials,

            // Factors
            'factor_height'     => $factorHeight,
            'factor_condition'  => $factorCondition,
            'factor_complexity' => $factorComplexity,
            'factor_region'     => $factorRegion,
            'combined_factor'   => round($combinedFactor, 4),

            // Prices
            'price_per_m2'      => round($pricePerM2, 2),
            'base_price'        => round($basePrice, 2),
            'price_with_factors' => round($priceWithFactors, 2),
            'fixed_costs'       => round($fixedCosts, 2),
            'subtotal_ht'       => round($subtotalHt, 2),
            'tva_rate'          => $tvaRate,
            'tva_amount'        => round($tvaAmount, 2),
            'total_ttc'         => round($totalTtc, 2),

            // Duration
            'preparation_days'  => $prepDays,
            'drying_days'       => $dryDays,
            'estimated_days'    => $estimatedDays,

            // Product info
            'product'           => $product,
            'product_case'      => $productCase,
            'devis_text'        => $product->devis_text,
        ];
    }

    private function getFixedCosts(string $serviceCode): float
    {
        return match ($serviceCode) {
            'facade'              => 2000,
            'etancheite'          => 1500,
            'resine_sol'          => 1200,
            'etancheite_toiture'  => 2500,
            default               => 1000,
        };
    }

    private function estimateDuration(string $serviceCode, float $surface): array
    {
        // [preparation_days, drying_days]
        $factor = max(1, ceil($surface / 50));
        return match ($serviceCode) {
            'facade'              => [2 * $factor, 1 * $factor],
            'etancheite'          => [1 * $factor, 2 * $factor],
            'resine_sol'          => [1 * $factor, 1 * $factor],
            'etancheite_toiture'  => [2 * $factor, 2 * $factor],
            default               => [1 * $factor, 1 * $factor],
        };
    }
}
