<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Service, Product, ProductCase, CaseMaterial};

class ResineProductsSeeder extends Seeder
{
    public function run(): void
    {
        $service = Service::where('code', 'resine_sol')->firstOrFail();

        $resine = Product::updateOrCreate(['code' => 'resine_epoxy_sol'], [
            'service_id'    => $service->id,
            'name'          => 'Résine Époxy Sol',
            'subcategory'   => null,
            'category'      => 'premium',
            'price_min'     => 200, 'price_max' => 350,
            'price_unit'    => 'm2', 'warranty_years' => 10,
            'norme'         => 'Règles professionnelles',
            'description'   => 'Revêtement sol époxy — primaire, couche autolissante, finition antidérapante',
            'devis_text'    => "Réalisation d'un revêtement de sol en résine époxy comprenant primaire, couche autolissante et finition antidérapante.",
            'is_active'     => true, 'display_order' => 1,
        ]);

        // Case 1: Sol neuf
        $caseNeuf = ProductCase::updateOrCreate(
            ['product_id' => $resine->id, 'code' => 'neuf'],
            ['name' => 'Sol neuf', 'description' => 'Dalle béton neuve, propre et sèche. Humidité résiduelle < 4%. Préparation mécanique légère.', 'icon_type' => 'check', 'display_order' => 1]
        );
        CaseMaterial::updateOrCreate(['product_case_id' => $caseNeuf->id, 'step_order' => 1], ['name' => 'Primaire époxy',          'type' => 'primaire', 'formula_type' => 'surface', 'formula_factor' => 0.4, 'unit' => 'kg']);
        CaseMaterial::updateOrCreate(['product_case_id' => $caseNeuf->id, 'step_order' => 2], ['name' => 'Résine autolissante',     'type' => 'resine',   'formula_type' => 'surface', 'formula_factor' => 3,   'unit' => 'kg']);
        CaseMaterial::updateOrCreate(['product_case_id' => $caseNeuf->id, 'step_order' => 3], ['name' => 'Finition antidérapante', 'type' => 'finition', 'formula_type' => 'surface', 'formula_factor' => 1,   'unit' => 'kg']);

        // Case 2: Sol fissuré
        $caseFissure = ProductCase::updateOrCreate(
            ['product_id' => $resine->id, 'code' => 'fissure'],
            ['name' => 'Sol fissuré', 'description' => 'Dalle avec fissures ou microfissures. Traitement préalable des fissures par bande avant application.', 'icon_type' => 'warning', 'display_order' => 2]
        );
        CaseMaterial::updateOrCreate(['product_case_id' => $caseFissure->id, 'step_order' => 1], ['name' => 'Bande de traitement fissures', 'type' => 'bande',    'formula_type' => 'longueur', 'formula_factor' => 1, 'unit' => 'ml', 'is_optional' => true]);
        CaseMaterial::updateOrCreate(['product_case_id' => $caseFissure->id, 'step_order' => 2], ['name' => 'Primaire époxy',               'type' => 'primaire', 'formula_type' => 'surface',  'formula_factor' => 0.4, 'unit' => 'kg']);
        CaseMaterial::updateOrCreate(['product_case_id' => $caseFissure->id, 'step_order' => 3], ['name' => 'Résine autolissante',          'type' => 'resine',   'formula_type' => 'surface',  'formula_factor' => 3,   'unit' => 'kg']);
        CaseMaterial::updateOrCreate(['product_case_id' => $caseFissure->id, 'step_order' => 4], ['name' => 'Finition antidérapante',       'type' => 'finition', 'formula_type' => 'surface',  'formula_factor' => 1,   'unit' => 'kg']);

        $this->command->info('✅ 1 Résine de Sol product seeded (2 cases)');
    }
}