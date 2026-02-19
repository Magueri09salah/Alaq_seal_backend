<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Service, Product, ProductCase};

class FacadeProductsSeeder extends Seeder
{
    public function run(): void
    {
        $service = Service::where('code', 'facade')->firstOrFail();

        $products = [
            // ── GRAPHIQUE (7) ──────────────────────────────────────
            ['code' => 'graphic_graphique',  'name' => 'Graphic',      'subcategory' => 'graphique', 'category' => 'standard', 'price_min' => 350, 'price_max' => 500, 'warranty_years' => 15],
            ['code' => 'mosaik_graphique',   'name' => 'Mosaïk',       'subcategory' => 'graphique', 'category' => 'premium',  'price_min' => 400, 'price_max' => 550, 'warranty_years' => 20],
            ['code' => 'nordik_graphique',   'name' => 'Nordik',        'subcategory' => 'graphique', 'category' => 'standard', 'price_min' => 320, 'price_max' => 480, 'warranty_years' => 15],
            ['code' => 'quadria_graphique',  'name' => 'Quadria',       'subcategory' => 'graphique', 'category' => 'standard', 'price_min' => 340, 'price_max' => 490, 'warranty_years' => 15],
            ['code' => 'striana_graphique',  'name' => 'Striana',       'subcategory' => 'graphique', 'category' => 'premium',  'price_min' => 380, 'price_max' => 530, 'warranty_years' => 18],
            ['code' => 'vector_graphique',   'name' => 'Vector',        'subcategory' => 'graphique', 'category' => 'standard', 'price_min' => 330, 'price_max' => 470, 'warranty_years' => 15],
            ['code' => 'vetria_graphique',   'name' => 'Vetria',        'subcategory' => 'graphique', 'category' => 'premium',  'price_min' => 420, 'price_max' => 580, 'warranty_years' => 20],

            // ── MINÉRALE (10) ─────────────────────────────────────
            ['code' => 'altura_minerale',      'name' => 'Altura',       'subcategory' => 'minerale', 'category' => 'premium',  'price_min' => 450, 'price_max' => 620, 'warranty_years' => 25],
            ['code' => 'arenia_minerale',      'name' => 'Arenia',       'subcategory' => 'minerale', 'category' => 'standard', 'price_min' => 360, 'price_max' => 510, 'warranty_years' => 18],
            ['code' => 'basalte_minerale',     'name' => 'Basalte',      'subcategory' => 'minerale', 'category' => 'premium',  'price_min' => 480, 'price_max' => 650, 'warranty_years' => 25],
            ['code' => 'coralia_minerale',     'name' => 'Coralia',      'subcategory' => 'minerale', 'category' => 'standard', 'price_min' => 340, 'price_max' => 490, 'warranty_years' => 18],
            ['code' => 'graf_granit_minerale', 'name' => 'Graf Granit',  'subcategory' => 'minerale', 'category' => 'premium',  'price_min' => 500, 'price_max' => 680, 'warranty_years' => 25],
            ['code' => 'graf_titan_minerale',  'name' => 'Graf Titan',   'subcategory' => 'minerale', 'category' => 'premium',  'price_min' => 520, 'price_max' => 700, 'warranty_years' => 25],
            ['code' => 'pierre_azur_minerale', 'name' => "Pierre D'Azur",'subcategory' => 'minerale', 'category' => 'premium',  'price_min' => 460, 'price_max' => 630, 'warranty_years' => 22],
            ['code' => 'slatium_minerale',     'name' => 'Slatium',      'subcategory' => 'minerale', 'category' => 'standard', 'price_min' => 380, 'price_max' => 530, 'warranty_years' => 20],
            ['code' => 'terra_sud_minerale',   'name' => 'Terra Sud',    'subcategory' => 'minerale', 'category' => 'standard', 'price_min' => 350, 'price_max' => 500, 'warranty_years' => 18],
            ['code' => 'tessera_nova_minerale','name' => 'Tessera Nova', 'subcategory' => 'minerale', 'category' => 'premium',  'price_min' => 490, 'price_max' => 670, 'warranty_years' => 23],

            // ── ORGANIQUE (6) ─────────────────────────────────────
            ['code' => 'lyrae_organique',    'name' => 'Lyrae',    'subcategory' => 'organique', 'category' => 'standard', 'price_min' => 320, 'price_max' => 460, 'warranty_years' => 15],
            ['code' => 'magma_organique',    'name' => 'Magma',    'subcategory' => 'organique', 'category' => 'premium',  'price_min' => 410, 'price_max' => 570, 'warranty_years' => 20],
            ['code' => 'moon_organique',     'name' => 'Moon',     'subcategory' => 'organique', 'category' => 'premium',  'price_min' => 430, 'price_max' => 590, 'warranty_years' => 20],
            ['code' => 'sahara_organique',   'name' => 'Sahara',   'subcategory' => 'organique', 'category' => 'standard', 'price_min' => 330, 'price_max' => 480, 'warranty_years' => 16],
            ['code' => 'selena_organique',   'name' => 'Selena',   'subcategory' => 'organique', 'category' => 'standard', 'price_min' => 340, 'price_max' => 490, 'warranty_years' => 17],
            ['code' => 'woodline_organique', 'name' => 'Woodline', 'subcategory' => 'organique', 'category' => 'premium',  'price_min' => 440, 'price_max' => 600, 'warranty_years' => 18],

            // ── URBAINE (5) ───────────────────────────────────────
            ['code' => 'metro_urbaine',     'name' => 'Metro',     'subcategory' => 'urbaine', 'category' => 'standard', 'price_min' => 350, 'price_max' => 500, 'warranty_years' => 15],
            ['code' => 'boulevard_urbaine', 'name' => 'Boulevard', 'subcategory' => 'urbaine', 'category' => 'standard', 'price_min' => 360, 'price_max' => 510, 'warranty_years' => 16],
            ['code' => 'skyline_urbaine',   'name' => 'Skyline',   'subcategory' => 'urbaine', 'category' => 'premium',  'price_min' => 420, 'price_max' => 580, 'warranty_years' => 20],
            ['code' => 'avenue_urbaine',    'name' => 'Avenue',    'subcategory' => 'urbaine', 'category' => 'standard', 'price_min' => 340, 'price_max' => 490, 'warranty_years' => 15],
            ['code' => 'plaza_urbaine',     'name' => 'Plaza',     'subcategory' => 'urbaine', 'category' => 'standard', 'price_min' => 370, 'price_max' => 520, 'warranty_years' => 17],
        ];

        foreach ($products as $i => $data) {
            $product = Product::updateOrCreate(['code' => $data['code']], array_merge($data, [
                'service_id'    => $service->id,
                'description'   => $data['name'] . ' — Revêtement de façade ' . ucfirst($data['subcategory']),
                'price_unit'    => 'm2',
                'is_active'     => true,
                'display_order' => $i,
            ]));

            // Facade products have a single generic case (no application variants)
            ProductCase::updateOrCreate(
                ['product_id' => $product->id, 'code' => 'standard'],
                ['name' => 'Application standard', 'description' => 'Application sur support sain et préparé', 'icon_type' => 'check', 'display_order' => 1]
            );
        }

        $this->command->info('✅ 28 Façade products seeded (Graphique×7, Minérale×10, Organique×6, Urbaine×5)');
    }
}