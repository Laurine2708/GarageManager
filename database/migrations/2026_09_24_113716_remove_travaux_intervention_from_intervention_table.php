<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Retire le champ texte des travaux, désormais détaillés par les tâches. */
return new class extends Migration
{
    /**
     * Supprime la colonne remplacée par le suivi détaillé des tâches.
     */
    public function up(): void
    {
        Schema::table('intervention', function (Blueprint $table) {
            $table->dropColumn('travaux_intervention');
        });
    }

    /**
     * Restaure la colonne de travaux pour annuler cette évolution.
     */
    public function down(): void
    {
        Schema::table('intervention', function (Blueprint $table) {
            $table->string('travaux_intervention', 255);
        });
    }
};
