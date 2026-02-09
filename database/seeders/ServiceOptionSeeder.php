<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ServiceOption;

class ServiceOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $options = [
            // Résine de sol - Époxy standard (service_id: 1)
            ['service_id' => 1, 'option_name' => '1 couche', 'option_type' => 'layers', 'multiplier' => 1.00, 'is_default' => true],
            ['service_id' => 1, 'option_name' => '2 couches', 'option_type' => 'layers', 'multiplier' => 1.30, 'is_default' => false],
            ['service_id' => 1, 'option_name' => '3 couches', 'option_type' => 'layers', 'multiplier' => 1.60, 'is_default' => false],
            ['service_id' => 1, 'option_name' => 'Finition standard', 'option_type' => 'finish', 'multiplier' => 1.00, 'is_default' => true],
            ['service_id' => 1, 'option_name' => 'Finition anti-dérapante', 'option_type' => 'finish', 'multiplier' => 1.10, 'is_default' => false],
            ['service_id' => 1, 'option_name' => 'Finition brillante', 'option_type' => 'finish', 'multiplier' => 1.15, 'is_default' => false],

            // Façades - Peinture extérieure (service_id: 4)
            ['service_id' => 4, 'option_name' => 'Rez-de-chaussée', 'option_type' => 'height', 'multiplier' => 1.00, 'is_default' => true],
            ['service_id' => 4, 'option_name' => '2-4 étages', 'option_type' => 'height', 'multiplier' => 1.25, 'is_default' => false],
            ['service_id' => 4, 'option_name' => '5+ étages', 'option_type' => 'height', 'multiplier' => 1.50, 'is_default' => false],
            ['service_id' => 4, 'option_name' => 'Sans échafaudage', 'option_type' => 'scaffolding', 'multiplier' => 1.00, 'is_default' => true],
            ['service_id' => 4, 'option_name' => 'Avec échafaudage', 'option_type' => 'scaffolding', 'multiplier' => 1.35, 'is_default' => false],

            // Étanchéité murs (service_id: 6)
            ['service_id' => 6, 'option_name' => 'Intérieur', 'option_type' => 'location', 'multiplier' => 1.00, 'is_default' => true],
            ['service_id' => 6, 'option_name' => 'Extérieur', 'option_type' => 'location', 'multiplier' => 1.20, 'is_default' => false],
            ['service_id' => 6, 'option_name' => '1 couche', 'option_type' => 'layers', 'multiplier' => 1.00, 'is_default' => true],
            ['service_id' => 6, 'option_name' => '2 couches', 'option_type' => 'layers', 'multiplier' => 1.25, 'is_default' => false],
        ];

        foreach ($options as $option) {
            ServiceOption::create($option);
        }
    }
}
