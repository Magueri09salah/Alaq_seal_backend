<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Service, Product, ProductCase, CaseMaterial};

class ToitureProductsSeeder extends Seeder
{
    public function run(): void
    {
        $service = Service::where('code', 'etancheite')->first();

        // PRODUCT 1: Bicouche APP
        $product1 = Product::create([
            'service_id' => $service->id,
            'subcategory' => 'toiture',
            'code' => 'toiture_bicouche_app',
            'name' => 'Étanchéité bicouche APP',
            'description' => 'Système d\'étanchéité bicouche à base d\'APP pour toitures-terrasses',
            'category' => 'standard',
            'norme' => 'DTU 43.1',
            'price_min' => 45.00,
            'price_max' => 65.00,
            // 'unit' => 'm²',
            'warranty_years' => 10,
            'is_active' => true,
        ]);

        $case1 = ProductCase::create([
            'product_id' => $product1->id,
            'code' => 'bicouche_app_accessible',
            'name' => 'Toiture accessible piétons',
            'description' => 'Pour terrasses accessibles aux piétons',
            'icon_type' => 'layers',
            // 'base_quantity_m2' => 1.0,
        ]);

        CaseMaterial::create(['product_case_id' => $case1->id, 'step_order' => 1, 'type' => 'primaire', 'name' => 'Primaire d\'accrochage', 'unit' => 'kg', 'formula_type' => 'fixed', 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case1->id, 'step_order' => 2, 'type' => 'membrane', 'name' => 'Membrane bitumineuse APP 1ère couche', 'unit' => 'm²', 'formula_type' => 'per_m2', 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case1->id, 'step_order' => 3, 'type' => 'membrane', 'name' => 'Membrane bitumineuse APP 2ème couche', 'unit' => 'm²', 'formula_type' => 'per_m2', 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case1->id, 'step_order' => 4, 'type' => 'isolation', 'name' => 'Dalle de protection béton 5cm', 'unit' => 'm²', 'formula_type' => 'per_m2', 'is_optional' => false]);

        $case2 = ProductCase::create([
            'product_id' => $product1->id,
            'code' => 'bicouche_app_non_accessible',
            'name' => 'Toiture non accessible',
            'description' => 'Pour toitures-terrasses techniques',
            'icon_type' => 'check',
            // 'base_quantity_m2' => 1.0,
        ]);

        CaseMaterial::create(['product_case_id' => $case2->id, 'step_order' => 1, 'type' => 'primaire', 'name' => 'Primaire d\'accrochage', 'unit' => 'kg', 'formula_type' => 'fixed', 'formula_factor' => 1.0, 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case2->id, 'step_order' => 2, 'type' => 'membrane', 'name' => 'Membrane bitumineuse APP 1ère couche', 'unit' => 'm²', 'formula_type' => 'per_m2', 'formula_factor' => 1.1, 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case2->id, 'step_order' => 3, 'type' => 'membrane', 'name' => 'Membrane bitumineuse APP 2ème couche finition', 'unit' => 'm²', 'formula_type' => 'per_m2', 'formula_factor' => 1.1, 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case2->id, 'step_order' => 4, 'type' => 'gravillon', 'name' => 'Gravillons de protection', 'unit' => 'kg', 'formula_type' => 'per_m2', 'formula_factor' => 40.0, 'is_optional' => false]);

        // PRODUCT 2: Monocouche SBS
        $product2 = Product::create([
            'service_id' => $service->id,
            'subcategory' => 'toiture',
            'code' => 'toiture_monocouche_sbs',
            'name' => 'Étanchéité monocouche SBS',
            'description' => 'Membrane monocouche élastomère SBS haute performance',
            'category' => 'premium',
            'norme' => 'DTU 43.1',
            'price_min' => 35.00,
            'price_max' => 50.00,
            'warranty_years' => 12,
            'is_active' => true,
        ]);

        $case3 = ProductCase::create([
            'product_id' => $product2->id,
            'code' => 'monocouche_sbs_standard',
            'name' => 'Application standard',
            'description' => 'Monocouche pour toiture-terrasse',
            'icon_type' => 'check',
        ]);

        CaseMaterial::create(['product_case_id' => $case3->id, 'step_order' => 1, 'type' => 'primaire', 'name' => 'Primaire bitumineux', 'unit' => 'kg', 'formula_type' => 'fixed', 'formula_factor' => 1.0, 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case3->id, 'step_order' => 2, 'type' => 'membrane', 'name' => 'Membrane SBS monocouche 5.2mm', 'unit' => 'm²', 'formula_type' => 'per_m2', 'formula_factor' => 1.1, 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case3->id, 'step_order' => 3, 'type' => 'gravillon', 'name' => 'Gravillons protection', 'unit' => 'kg', 'formula_type' => 'per_m2', 'formula_factor' => 40.0, 'is_optional' => true]);

        // PRODUCT 3: Résine PU
        $product3 = Product::create([
            'service_id' => $service->id,
            'subcategory' => 'toiture',
            'code' => 'toiture_resine_pu',
            'name' => 'Résine polyuréthane toiture',
            'description' => 'Étanchéité liquide en résine polyuréthane',
            'category' => 'premium',
            'norme' => 'DTU 43.3',
            'price_min' => 55.00,
            'price_max' => 75.00,
            // 'unit' => 'm²',
            'warranty_years' => 15,
            'is_active' => true,
        ]);

        $case4 = ProductCase::create([
            'product_id' => $product3->id,
            'code' => 'resine_pu_neuf',
            'name' => 'Support neuf',
            'description' => 'Application sur support béton neuf',
            'icon_type' => 'check',
            // 'base_quantity_m2' => 1.0,
        ]);

        CaseMaterial::create(['product_case_id' => $case4->id, 'step_order' => 1, 'type' => 'primaire', 'name' => 'Primaire d\'accrochage PU', 'unit' => 'kg', 'formula_type' => 'fixed', 'formula_factor' => 1.0, 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case4->id, 'step_order' => 2, 'type' => 'resine', 'name' => 'Résine polyuréthane 1ère couche', 'unit' => 'kg', 'formula_type' => 'per_m2', 'formula_factor' => 1.5, 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case4->id, 'step_order' => 3, 'type' => 'bande', 'name' => 'Bande d\'armature points singuliers', 'unit' => 'ml', 'formula_type' => 'per_m2', 'formula_factor' => 0.2, 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case4->id, 'step_order' => 4, 'type' => 'resine', 'name' => 'Résine polyuréthane 2ème couche', 'unit' => 'kg', 'formula_type' => 'per_m2', 'formula_factor' => 1.5, 'is_optional' => false]);


        $case5 = ProductCase::create([
            'product_id' => $product3->id,
            'code' => 'resine_pu_renovation',
            'name' => 'Rénovation',
            'description' => 'Rénovation d\'étanchéité existante',
            'icon_type' => 'warning',
            // 'base_quantity_m2' => 1.0,
        ]);

        CaseMaterial::create(['product_case_id' => $case5->id, 'step_order' => 1, 'type' => 'enduit', 'name' => 'Ragréage surface', 'unit' => 'kg', 'formula_type' => 'per_m2', 'formula_factor' => 2.0, 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case5->id, 'step_order' => 2, 'type' => 'primaire', 'name' => 'Primaire d\'accrochage PU', 'unit' => 'kg', 'formula_type' => 'fixed', 'formula_factor' => 1.0, 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case5->id, 'step_order' => 3, 'type' => 'resine', 'name' => 'Résine polyuréthane 1ère couche', 'unit' => 'kg', 'formula_type' => 'per_m2', 'formula_factor' => 1.8, 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case5->id, 'step_order' => 4, 'type' => 'bande', 'name' => 'Bande d\'armature renforcée', 'unit' => 'ml', 'formula_type' => 'per_m2', 'formula_factor' => 0.3, 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case5->id, 'step_order' => 5, 'type' => 'resine', 'name' => 'Résine polyuréthane 2ème couche', 'unit' => 'kg', 'formula_type' => 'per_m2', 'formula_factor' => 1.8, 'is_optional' => false]);

        // PRODUCT 4: EPDM
        $product4 = Product::create([
            'service_id' => $service->id,
            'subcategory' => 'toiture',
            'code' => 'toiture_epdm',
            'name' => 'EPDM membrane synthétique',
            'description' => 'Membrane EPDM élastomère pour toiture-terrasse',
            'category' => 'premium',
            'norme' => 'DTU 43.1',
            'price_min' => 40.00,
            'price_max' => 60.00,
            // 'unit' => 'm²',
            'warranty_years' => 20,
            'is_active' => true,
        ]);

        $case6 = ProductCase::create([
            'product_id' => $product4->id,
            'code' => 'epdm_leste',
            'name' => 'Système lesté',
            'description' => 'Membrane EPDM avec lestage gravillons',
            'icon_type' => 'layers',
            // 'base_quantity_m2' => 1.0,
        ]);

        CaseMaterial::create(['product_case_id' => $case6->id, 'step_order' => 1, 'type' => 'nappe', 'name' => 'Géotextile de protection', 'unit' => 'm²', 'formula_type' => 'per_m2', 'formula_factor' => 1.1, 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case6->id, 'step_order' => 2, 'type' => 'membrane', 'name' => 'Membrane EPDM 1.2mm', 'unit' => 'm²', 'formula_type' => 'per_m2', 'formula_factor' => 1.1, 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case6->id, 'step_order' => 3, 'type' => 'nappe', 'name' => 'Géotextile anti-poinçonnement', 'unit' => 'm²', 'formula_type' => 'per_m2', 'formula_factor' => 1.1, 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case6->id, 'step_order' => 4, 'type' => 'gravillon', 'name' => 'Gravillons lestage 40/60', 'unit' => 'kg', 'formula_type' => 'per_m2', 'formula_factor' => 50.0, 'is_optional' => false]);


        $case7 = ProductCase::create([
            'product_id' => $product4->id,
            'code' => 'epdm_colle',
            'name' => 'Système collé',
            'description' => 'Membrane EPDM collée en plein',
            'icon_type' => 'check',
            // 'base_quantity_m2' => 1.0,
        ]);

        CaseMaterial::create(['product_case_id' => $case7->id, 'step_order' => 1, 'type' => 'primaire', 'name' => 'Primaire d\'accrochage', 'unit' => 'kg', 'formula_type' => 'fixed', 'formula_factor' => 1.0, 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case7->id, 'step_order' => 2, 'type' => 'membrane', 'name' => 'Colle contact EPDM', 'unit' => 'kg', 'formula_type' => 'fixed', 'formula_factor' => 1.0, 'is_optional' => false]);
        CaseMaterial::create(['product_case_id' => $case7->id, 'step_order' => 3, 'type' => 'membrane', 'name' => 'Membrane EPDM 1.2mm', 'unit' => 'm²', 'formula_type' => 'per_m2', 'formula_factor' => 1.1, 'is_optional' => false]);

        echo "✅ 4 Toiture products seeded under Étanchéité\n";
    }
}