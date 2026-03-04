<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('toiture_devis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Devis identification
            $table->string('devis_number')->unique();
            $table->enum('status', ['draft', 'saved', 'submitted', 'reviewed'])->default('draft');
            
            // Project info
            $table->string('project_name')->nullable();
            $table->string('project_location')->nullable();
            $table->text('notes')->nullable();
            
            // Type configuration
            $table->enum('type', ['toiture', 'mur', 'salle_bain']);
            $table->enum('toiture_type', ['accessible', 'non_accessible'])->nullable(); // For toiture only
            $table->boolean('isolation')->nullable(); // For toiture only
            $table->enum('finition', ['autoprotegee', 'lestage'])->nullable(); // For non-accessible toiture
            
            // Dimensions
            $table->decimal('longueur', 10, 2);
            $table->decimal('largeur', 10, 2);
            $table->decimal('perimetre', 10, 2)->nullable(); // For toiture
            $table->decimal('hauteur_acrotere', 10, 2)->nullable(); // For toiture
            $table->decimal('hauteur', 10, 2)->nullable(); // For mur
            $table->integer('nombre_murs')->nullable(); // For mur
            
            // Toiture specific options
            $table->integer('nombre_evacuations')->default(1);
            $table->boolean('chape_existante')->default(true);
            
            // Calculated values
            $table->decimal('surface_brute', 10, 2);
            $table->decimal('surface_technique', 10, 2)->nullable(); // For toiture
            $table->decimal('surface_releves', 10, 2)->nullable(); // For toiture
            
            // Pricing
            $table->decimal('total_ht', 10, 2);
            $table->decimal('tva_rate', 5, 2)->default(20.00);
            $table->decimal('tva_amount', 10, 2);
            $table->decimal('total_ttc', 10, 2);
            
            // Materials (JSON)
            $table->json('materials');
            
            // Timestamps
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toiture_devis');
    }
};