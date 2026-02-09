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
        Schema::create('devis_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devis_id')->constrained()->onDelete('cascade');
            $table->foreignId('modifier_id')->references('id')->on('price_modifiers')->onDelete('restrict');
            $table->decimal('applied_value', 10, 2);
            $table->timestamps();
            
            // Indexes
            $table->index('devis_id');
            $table->index('modifier_id');
            $table->unique(['devis_id', 'modifier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devis_modifiers');
    }
};
