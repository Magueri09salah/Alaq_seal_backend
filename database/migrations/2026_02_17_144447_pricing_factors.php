<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pricing_factors', function (Blueprint $table) {
            $table->id();
            $table->string('type');          // height | condition | complexity | region
            $table->string('code');          // rdc | r1_r3 | r4_plus | bon | moyen | mauvais ...
            $table->string('name');          // "RDC (≤ 3m)" | "Bon état"
            $table->text('description')->nullable();
            $table->decimal('multiplier', 5, 2);
            $table->boolean('is_default')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->unique(['type', 'code']);
            $table->index('type');
        });
    }
    public function down(): void { Schema::dropIfExists('pricing_factors'); }
};