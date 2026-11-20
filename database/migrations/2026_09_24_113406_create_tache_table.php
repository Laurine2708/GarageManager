<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Crée les tâches rattachées à une intervention et à un statut. */
return new class extends Migration
{
    /**
     * Crée les tâches et supprime leurs dépendances en cascade avec l’intervention.
     */
    public function up(): void
    {
        Schema::create('tache', function (Blueprint $table) {
            $table->id('id_tache');
            $table->string('libelle_tache', 255);

            $table->foreignId('id_statut')
                ->constrained('statut', 'id_statut');

            $table->foreignId('id_intervention')
                ->constrained('intervention', 'id_intervention')
                ->onDelete('cascade');
        });
    }

    /**
     * Supprime la table des tâches.
     */
    public function down(): void
    {
        Schema::dropIfExists('tache');
    }
};
