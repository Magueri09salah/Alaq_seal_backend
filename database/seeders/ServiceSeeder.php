<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;
class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Résine de sol - Époxy standard',
                'slug' => 'resine-sol-epoxy-standard',
                'category' => 'resine',
                'description' => 'Revêtement de sol en résine époxy standard, finition lisse et durable',
                'base_price_per_m2' => 250.00,
                'min_price' => 5000.00,
                'is_active' => true,
                'order_display' => 1,
            ],
            [
                'name' => 'Résine de sol - Époxy décorative',
                'slug' => 'resine-sol-epoxy-decorative',
                'category' => 'resine',
                'description' => 'Résine époxy avec effets décoratifs et finitions personnalisées',
                'base_price_per_m2' => 350.00,
                'min_price' => 7000.00,
                'is_active' => true,
                'order_display' => 2,
            ],
            [
                'name' => 'Résine de sol - Polyuréthane',
                'slug' => 'resine-sol-polyurethane',
                'category' => 'resine',
                'description' => 'Revêtement polyuréthane haute résistance',
                'base_price_per_m2' => 300.00,
                'min_price' => 6000.00,
                'is_active' => true,
                'order_display' => 3,
            ],
            [
                'name' => 'Façades - Peinture extérieure',
                'slug' => 'facades-peinture-exterieure',
                'category' => 'facades',
                'description' => 'Peinture de façade professionnelle avec préparation complète',
                'base_price_per_m2' => 150.00,
                'min_price' => 8000.00,
                'is_active' => true,
                'order_display' => 4,
            ],
            [
                'name' => 'Façades - Revêtement moderne',
                'slug' => 'facades-revetement-moderne',
                'category' => 'facades',
                'description' => 'Revêtement de façade moderne avec isolation thermique',
                'base_price_per_m2' => 280.00,
                'min_price' => 15000.00,
                'is_active' => true,
                'order_display' => 5,
            ],
            [
                'name' => 'Étanchéité murs - Membrane liquide',
                'slug' => 'etancheite-murs-membrane-liquide',
                'category' => 'etancheite_murs',
                'description' => 'Application de membrane d\'étanchéité liquide pour murs',
                'base_price_per_m2' => 200.00,
                'min_price' => 4000.00,
                'is_active' => true,
                'order_display' => 6,
            ],
            [
                'name' => 'Étanchéité murs - Injection',
                'slug' => 'etancheite-murs-injection',
                'category' => 'etancheite_murs',
                'description' => 'Traitement par injection pour murs humides',
                'base_price_per_m2' => 250.00,
                'min_price' => 5000.00,
                'is_active' => true,
                'order_display' => 7,
            ],
            [
                'name' => 'Étanchéité sols - Membrane bitume',
                'slug' => 'etancheite-sols-membrane-bitume',
                'category' => 'etancheite_sols',
                'description' => 'Étanchéité par membrane bitumineuse multicouche',
                'base_price_per_m2' => 180.00,
                'min_price' => 5000.00,
                'is_active' => true,
                'order_display' => 8,
            ],
            [
                'name' => 'Étanchéité sols - PVC armé',
                'slug' => 'etancheite-sols-pvc-arme',
                'category' => 'etancheite_sols',
                'description' => 'Membrane PVC armée haute résistance',
                'base_price_per_m2' => 220.00,
                'min_price' => 6000.00,
                'is_active' => true,
                'order_display' => 9,
            ],
            [
                'name' => 'Coffrage modulaire',
                'slug' => 'coffrage-modulaire',
                'category' => 'coffrage',
                'description' => 'Location de système de coffrage modulaire professionnel',
                'base_price_per_m2' => 80.00,
                'min_price' => 2000.00,
                'is_active' => true,
                'order_display' => 10,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
