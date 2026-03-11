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
             // MUR specific fields
            $table->string('water_level')->nullable()->after('type');
            $table->boolean('drain')->default(false)->after('water_level');
            $table->decimal('hauteur_technique', 8, 2)->nullable()->after('hauteur');
            
            // SALLE_BAIN specific fields
            $table->string('sdb_type')->nullable()->after('type');
            $table->string('support')->nullable()->after('sdb_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('toiture_devis', function (Blueprint $table) {
            $table->dropColumn(['water_level', 'drain', 'hauteur_technique', 'sdb_type', 'support']);
        });
    }
};
