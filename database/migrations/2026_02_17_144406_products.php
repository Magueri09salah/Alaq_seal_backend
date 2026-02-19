<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            // For Façade: graphique|minerale|organique|urbaine
            // For Étanchéité: mur|sol
            // For others: null
            $table->string('subcategory')->nullable();

            // Quality tier
            $table->enum('category', ['economique', 'standard', 'premium'])->default('standard');

            // Pricing
            $table->decimal('price_min', 10, 2);
            $table->decimal('price_max', 10, 2);
            $table->string('price_unit')->default('m2');

            // Quality scores
            $table->integer('warranty_years')->nullable();
            $table->integer('score_technical')->default(80);
            $table->integer('score_durability')->default(80);
            $table->integer('score_maintenance')->default(80);

            // DTU/CSTB reference
            $table->string('norme')->nullable();           // DTU 52.2 | DTU 43.1 | CSTB | DTU 20.1

            // Auto-generated quote text
            $table->text('devis_text')->nullable();

            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index(['service_id', 'subcategory', 'is_active']);
        });
    }
    public function down(): void { Schema::dropIfExists('products'); }
};