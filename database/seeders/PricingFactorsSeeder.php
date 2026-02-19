<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PricingFactor;

class PricingFactorsSeeder extends Seeder
{
    public function run(): void
    {
        $factors = [
            // ── HEIGHT ───────────────────────────────────────────────
            ['type' => 'height', 'code' => 'rdc',      'name' => 'RDC (≤ 3m)',           'description' => 'Rez-de-chaussée, travaux sans échafaudage',            'multiplier' => 1.00, 'is_default' => true,  'display_order' => 1],
            ['type' => 'height', 'code' => 'r1_r3',    'name' => 'R+1 à R+3',            'description' => 'Étages 1 à 3, échafaudage léger nécessaire',           'multiplier' => 1.15, 'is_default' => false, 'display_order' => 2],
            ['type' => 'height', 'code' => 'r4_plus',  'name' => 'R+4 et plus',          'description' => 'Grande hauteur, échafaudage lourd obligatoire',         'multiplier' => 1.35, 'is_default' => false, 'display_order' => 3],

            // ── CONDITION ────────────────────────────────────────────
            ['type' => 'condition', 'code' => 'bon',     'name' => 'Bon état',            'description' => 'Support sain, propre, sans fissures ni décollements',   'multiplier' => 1.00, 'is_default' => true,  'display_order' => 1],
            ['type' => 'condition', 'code' => 'moyen',   'name' => 'État moyen',          'description' => 'Quelques fissures superficielles, légère dégradation',  'multiplier' => 1.15, 'is_default' => false, 'display_order' => 2],
            ['type' => 'condition', 'code' => 'mauvais', 'name' => 'Mauvais état',        'description' => 'Fissures importantes, humidité, décollements',          'multiplier' => 1.40, 'is_default' => false, 'display_order' => 3],

            // ── COMPLEXITY ───────────────────────────────────────────
            ['type' => 'complexity', 'code' => 'simple',   'name' => 'Simple',            'description' => 'Surface plane, peu d\'obstacles, accès facile',         'multiplier' => 1.00, 'is_default' => true,  'display_order' => 1],
            ['type' => 'complexity', 'code' => 'moyen',    'name' => 'Complexité moyenne','description' => 'Quelques angles, modénatures ou traversées',            'multiplier' => 1.20, 'is_default' => false, 'display_order' => 2],
            ['type' => 'complexity', 'code' => 'complexe', 'name' => 'Complexe',          'description' => 'Nombreux obstacles, forme irrégulière, accès difficile','multiplier' => 1.50, 'is_default' => false, 'display_order' => 3],

            // ── REGION ───────────────────────────────────────────────
            ['type' => 'region', 'code' => 'standard', 'name' => 'Région standard',      'description' => 'Zone urbaine ou semi-urbaine classique',                'multiplier' => 1.00, 'is_default' => true,  'display_order' => 1],
            ['type' => 'region', 'code' => 'littoral',  'name' => 'Zone littorale',      'description' => 'Proximité mer, contraintes salines et humidité',        'multiplier' => 1.10, 'is_default' => false, 'display_order' => 2],
            ['type' => 'region', 'code' => 'montagne',  'name' => 'Zone montagne',       'description' => 'Altitude, contraintes thermiques et gel',               'multiplier' => 1.20, 'is_default' => false, 'display_order' => 3],
        ];

        foreach ($factors as $f) {
            PricingFactor::updateOrCreate(
                ['type' => $f['type'], 'code' => $f['code']],
                $f
            );
        }

        $this->command->info('✅ 12 pricing factors seeded (height×3, condition×3, complexity×3, region×3)');
    }
}