<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('case_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_case_id')->constrained('product_cases')->onDelete('cascade');
            $table->integer('step_order');           // 1, 2, 3, 4... (application order per DTU)
            $table->string('name');                  // "Barbotine d'accrochage" | "Mortier couche 1"
            $table->string('type');                  // barbotine|mortier|primaire|resine|bande|membrane|isolation|finition|drain|gravillon
            $table->string('formula_type');          // surface | perimetre | longueur | unite (how quantity is calculated)
            $table->decimal('formula_factor', 8, 4); // multiplier: 2 | 4 | 0.3 | 1 | 50
            $table->string('unit');                  // kg | L | m² | ml | unité
            $table->boolean('is_optional')->default(false);
            $table->timestamps();

            $table->index('product_case_id');
        });
    }
    public function down(): void { Schema::dropIfExists('case_materials'); }
};