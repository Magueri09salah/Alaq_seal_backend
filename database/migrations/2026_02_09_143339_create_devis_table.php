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
        Schema::create('devis', function (Blueprint $table) {
            $table->id();
            $table->string('devis_number', 50)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Project details
            $table->string('project_name')->nullable();
            $table->text('project_location')->nullable();
            $table->text('notes')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'saved', 'submitted', 'reviewed'])->default('draft');
            
            // Pricing
            $table->decimal('subtotal_ht', 12, 2)->default(0);
            $table->decimal('tva_rate', 5, 2)->default(20.00);
            $table->decimal('tva_amount', 12, 2)->default(0);
            $table->decimal('total_ttc', 12, 2)->default(0);
            
            // PDF
            $table->string('pdf_path', 500)->nullable();
            
            // Timestamps
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('devis_number');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devis');
    }
};
