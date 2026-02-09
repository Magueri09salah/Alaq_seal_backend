<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PriceModifier;

class PriceModifierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modifiers = [
            [
                'name' => 'Urgence - Délai court (< 10 jours)',
                'slug' => 'urgence-court',
                'type' => 'surcharge',
                'value' => 15.00,
                'is_percentage' => true,
                'description' => 'Majoration pour intervention urgente dans les 10 jours',
                'is_active' => true,
            ],
            [
                'name' => 'Urgence - Très urgent (< 5 jours)',
                'slug' => 'urgence-tres-urgent',
                'type' => 'surcharge',
                'value' => 25.00,
                'is_percentage' => true,
                'description' => 'Majoration pour intervention très urgente dans les 5 jours',
                'is_active' => true,
            ],
            [
                'name' => 'Accessibilité difficile',
                'slug' => 'accessibilite-difficile',
                'type' => 'surcharge',
                'value' => 20.00,
                'is_percentage' => true,
                'description' => 'Zone difficile d\'accès (hauteur, éloignement)',
                'is_active' => true,
            ],
            [
                'name' => 'Projet volumineux (> 300m²)',
                'slug' => 'projet-volumineux',
                'type' => 'discount',
                'value' => 5.00,
                'is_percentage' => true,
                'description' => 'Remise pour surface importante (> 300m²)',
                'is_active' => true,
            ],
            [
                'name' => 'Projet très volumineux (> 500m²)',
                'slug' => 'projet-tres-volumineux',
                'type' => 'discount',
                'value' => 10.00,
                'is_percentage' => true,
                'description' => 'Remise majorée pour très grande surface (> 500m²)',
                'is_active' => true,
            ],
        ];

        foreach ($modifiers as $modifier) {
            PriceModifier::create($modifier);
        }
    }
}
