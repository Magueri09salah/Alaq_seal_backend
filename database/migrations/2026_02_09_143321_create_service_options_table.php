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
        Schema::create('service_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('option_name');
            $table->string('option_type', 100);
            $table->decimal('multiplier', 5, 2)->default(1.00);
            $table->boolean('is_default')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('service_id');
            $table->index('option_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_options');
    }
};
