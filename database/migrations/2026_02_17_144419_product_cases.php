<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('code');              // sain | fissure | siphon | drainage | isole | accessible | neuf
            $table->string('name');              // "Support sain" | "Support fissuré"
            $table->text('description');         // Shown in Step 4 card
            $table->string('icon_type');         // check | warning | water | layers | sun (for SVG icon selection)
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'code']);
            $table->index('product_id');
        });
    }
    public function down(): void { Schema::dropIfExists('product_cases'); }
};