<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Service, Product, ProductCase, CaseMaterial};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ServicesSeeder::class,
            FacadeProductsSeeder::class,
            EtancheiteProductsSeeder::class,
            ResineProductsSeeder::class,
            ToitureProductsSeeder::class,
            PricingFactorsSeeder::class,
        ]);
    }
}