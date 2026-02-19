<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();        // facade | etancheite | resine_sol | etancheite_toiture | coffrage
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('has_subtypes')->default(false); // true for facade & etancheite
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('services'); }
};