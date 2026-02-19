<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('devis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('devis_number')->unique();

            // Project info
            $table->string('project_name')->nullable();
            $table->string('project_location')->nullable();
            $table->text('notes')->nullable();

            // Service/Product selection
            $table->foreignId('service_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->string('subcategory')->nullable();   // graphique | mur | sol | etc.
            $table->foreignId('product_case_id')->constrained('product_cases');

            // Dimensions (raw inputs — surface auto-calculated)
            $table->decimal('longueur', 8, 2)->nullable();
            $table->decimal('largeur', 8, 2)->nullable();
            $table->decimal('hauteur', 8, 2)->nullable();
            $table->integer('nombre_murs')->default(4);
            $table->decimal('surface_area', 10, 2);         // auto-calculated

            // Pricing factors applied
            $table->decimal('factor_height', 5, 2)->default(1.00);
            $table->decimal('factor_condition', 5, 2)->default(1.00);
            $table->decimal('factor_complexity', 5, 2)->default(1.00);
            $table->decimal('factor_region', 5, 2)->default(1.00);

            // Calculated prices
            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('price_with_factors', 12, 2)->default(0);
            $table->decimal('fixed_costs', 12, 2)->default(0);
            $table->decimal('subtotal_ht', 12, 2)->default(0);
            $table->decimal('tva_rate', 5, 2)->default(20);
            $table->decimal('tva_amount', 12, 2)->default(0);
            $table->decimal('total_ttc', 12, 2)->default(0);

            // Calculated materials list (JSON — from DTU formulas)
            $table->json('calculated_materials')->nullable();

            // Timeline
            $table->integer('estimated_days')->nullable();
            $table->integer('preparation_days')->nullable();
            $table->integer('drying_days')->nullable();

            // Status
            $table->enum('status', ['draft', 'saved', 'submitted', 'reviewed'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('devis'); }
};