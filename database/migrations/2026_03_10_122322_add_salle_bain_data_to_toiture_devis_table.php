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
        Schema::table('toiture_devis', function (Blueprint $table) {
            $table->json('salle_bain_data')->nullable()->after('materials');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('toiture_devis', function (Blueprint $table) {
            $table->dropColumn('salle_bain_data');
        });
    }
};
