<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['code' => 'facade',             'name' => 'Façade',               'description' => 'Revêtements et finitions de façades — Graphique, Minérale, Organique, Urbaine', 'has_subtypes' => true,  'display_order' => 1],
            ['code' => 'etancheite',          'name' => 'Étanchéité',           'description' => 'Imperméabilisation des murs et des sols — DTU 52.2, DTU 20.1, CSTB',          'has_subtypes' => true,  'display_order' => 2],
            ['code' => 'resine_sol',          'name' => 'Résine de Sol',        'description' => 'Revêtements de sol en résine époxy autolissante et antidérapante',             'has_subtypes' => false, 'display_order' => 3],
            ['code' => 'etancheite_toiture',  'name' => 'Étanchéité Toiture',   'description' => 'Étanchéité de toitures terrasses — DTU 43.1',                                  'has_subtypes' => false, 'display_order' => 4],
            ['code' => 'coffrage',            'name' => 'Coffrage Modulaire',   'description' => 'Systèmes de coffrage modulaire pour construction',                             'has_subtypes' => false, 'display_order' => 5],
        ];

        foreach ($services as $s) {
            Service::updateOrCreate(['code' => $s['code']], array_merge($s, ['is_active' => true]));
        }

        $this->command->info('✅ 5 services seeded');
    }
}
