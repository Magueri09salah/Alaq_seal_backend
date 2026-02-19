<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Service, Product, ProductCase, CaseMaterial};

class ToitureProductsSeeder extends Seeder
{
    public function run(): void
    {
        $service = Service::where('code', 'etancheite_toiture')->firstOrFail();

        $toiture = Product::updateOrCreate(['code' => 'toiture_terrasse'], [
            'service_id'    => $service->id,
            'name'          => 'Toiture Terrasse',
            'subcategory'   => null,
            'category'      => 'premium',
            'price_min'     => 250,
            'price_max' => 420,
            'price_unit'    => 'm2',
            'warranty_years' => 20,
            'norme'         => 'DTU 43.1',
            'description'   => 'Étanchéité toiture terrasse complète — DTU 43.1',
            'devis_text'    => 'Étanchéité de toiture terrasse comprenant pare-vapeur, isolation thermique, membrane bicouche avec relevés et protection, conforme au DTU 43.1.',
            'is_active'     => true,
            'display_order' => 1,
        ]);

        // Case 1: Non isolée
        $caseNonIsolee = ProductCase::updateOrCreate(
            ['product_id' => $toiture->id, 'code' => 'non_isolee'],
            ['name' => 'Terrasse non isolée', 'description' => 'Terrasse sans isolation thermique. Local non chauffé en dessous (garage, cave). Pare-vapeur selon besoin.', 'icon_type' => 'sun', 'display_order' => 1]
        );
        CaseMaterial::updateOrCreate(['product_case_id' => $caseNonIsolee->id, 'step_order' => 1], ['name' => 'Pare-vapeur',         'type' => 'pare_vapeur', 'formula_type' => 'surface',   'formula_factor' => 1, 'unit' => 'm²', 'is_optional' => true]);
        CaseMaterial::updateOrCreate(['product_case_id' => $caseNonIsolee->id, 'step_order' => 2], ['name' => 'Membrane couche 1',   'type' => 'membrane',    'formula_type' => 'surface',   'formula_factor' => 1, 'unit' => 'm²']);
        CaseMaterial::updateOrCreate(['product_case_id' => $caseNonIsolee->id, 'step_order' => 3], ['name' => 'Membrane couche 2',   'type' => 'membrane',    'formula_type' => 'surface',   'formula_factor' => 1, 'unit' => 'm²']);
        CaseMaterial::updateOrCreate(['product_case_id' => $caseNonIsolee->id, 'step_order' => 4], ['name' => 'Bandes de relevé',    'type' => 'bande',       'formula_type' => 'perimetre', 'formula_factor' => 1, 'unit' => 'ml']);

        // Case 2: Isolée
        $caseIsolee = ProductCase::updateOrCreate(
            ['product_id' => $toiture->id, 'code' => 'isolee'],
            ['name' => 'Terrasse isolée', 'description' => 'Terrasse avec isolation thermique complète. Local chauffé en dessous. Pare-vapeur obligatoire.', 'icon_type' => 'layers', 'display_order' => 2]
        );
        CaseMaterial::updateOrCreate(['product_case_id' => $caseIsolee->id, 'step_order' => 1], ['name' => 'Pare-vapeur',         'type' => 'pare_vapeur', 'formula_type' => 'surface',   'formula_factor' => 1, 'unit' => 'm²']);
        CaseMaterial::updateOrCreate(['product_case_id' => $caseIsolee->id, 'step_order' => 2], ['name' => 'Isolation thermique', 'type' => 'isolation',   'formula_type' => 'surface',   'formula_factor' => 1, 'unit' => 'm²']);
        CaseMaterial::updateOrCreate(['product_case_id' => $caseIsolee->id, 'step_order' => 3], ['name' => 'Membrane couche 1',   'type' => 'membrane',    'formula_type' => 'surface',   'formula_factor' => 1, 'unit' => 'm²']);
        CaseMaterial::updateOrCreate(['product_case_id' => $caseIsolee->id, 'step_order' => 4], ['name' => 'Membrane couche 2',   'type' => 'membrane',    'formula_type' => 'surface',   'formula_factor' => 1, 'unit' => 'm²']);
        CaseMaterial::updateOrCreate(['product_case_id' => $caseIsolee->id, 'step_order' => 5], ['name' => 'Bandes de relevé',    'type' => 'bande',       'formula_type' => 'perimetre', 'formula_factor' => 1, 'unit' => 'ml']);

        // Case 3: Accessible
        $caseAccessible = ProductCase::updateOrCreate(
            ['product_id' => $toiture->id, 'code' => 'accessible'],
            ['name' => 'Terrasse accessible', 'description' => 'Terrasse accessible aux personnes. Protection lourde par gravillons (50 kg/m²) obligatoire.', 'icon_type' => 'sun', 'display_order' => 3]
        );
        CaseMaterial::updateOrCreate(['product_case_id' => $caseAccessible->id, 'step_order' => 1], ['name' => 'Pare-vapeur',         'type' => 'pare_vapeur', 'formula_type' => 'surface',   'formula_factor' => 1,  'unit' => 'm²']);
        CaseMaterial::updateOrCreate(['product_case_id' => $caseAccessible->id, 'step_order' => 2], ['name' => 'Isolation thermique', 'type' => 'isolation',   'formula_type' => 'surface',   'formula_factor' => 1,  'unit' => 'm²']);
        CaseMaterial::updateOrCreate(['product_case_id' => $caseAccessible->id, 'step_order' => 3], ['name' => 'Membrane couche 1',   'type' => 'membrane',    'formula_type' => 'surface',   'formula_factor' => 1,  'unit' => 'm²']);
        CaseMaterial::updateOrCreate(['product_case_id' => $caseAccessible->id, 'step_order' => 4], ['name' => 'Membrane couche 2',   'type' => 'membrane',    'formula_type' => 'surface',   'formula_factor' => 1,  'unit' => 'm²']);
        CaseMaterial::updateOrCreate(['product_case_id' => $caseAccessible->id, 'step_order' => 5], ['name' => 'Bandes de relevé',    'type' => 'bande',       'formula_type' => 'perimetre', 'formula_factor' => 1,  'unit' => 'ml']);
        CaseMaterial::updateOrCreate(['product_case_id' => $caseAccessible->id, 'step_order' => 6], ['name' => 'Gravillons protection', 'type' => 'gravillon',   'formula_type' => 'surface',   'formula_factor' => 50, 'unit' => 'kg']);

        $this->command->info('✅ 1 Toiture Terrasse product seeded (3 cases: Non isolée, Isolée, Accessible)');
    }
}
