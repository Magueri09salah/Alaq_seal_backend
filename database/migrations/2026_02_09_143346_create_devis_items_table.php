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
        Schema::create('devis_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devis_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('restrict');
            
            // Item details
            $table->text('description');
            $table->decimal('surface_m2', 10, 2);
            $table->decimal('unit_price', 10, 2);
            
            // Options applied
            $table->json('selected_options')->nullable();
            $table->decimal('option_multiplier', 5, 2)->default(1.00);
            
            // Calculated
            $table->decimal('subtotal', 12, 2);
            
            // Order
            $table->integer('order_index')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('devis_id');
            $table->index('service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devis_items');
    }
};
