<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Crée le référentiel des statuts utilisés par les interventions et tâches. */
return new class extends Migration
{
    /**
     * Crée le référentiel des libellés de statut.
     */
    public function up(): void
    {
        Schema::create('statut', function (Blueprint $table) {
            $table->id('id_statut');
            $table->string('nom_statut', 50);
        });
    }

    /**
     * Supprime le référentiel des statuts.
     */
    public function down(): void
    {
        Schema::dropIfExists('statut');
    }
};
