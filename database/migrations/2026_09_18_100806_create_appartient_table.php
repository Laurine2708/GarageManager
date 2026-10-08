<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Crée la relation plusieurs-à-plusieurs entre utilisateurs et véhicules. */
return new class extends Migration
{
    /**
     * Crée la table pivot avec une clé primaire composée des deux références.
     */
    public function up(): void
    {
        Schema::create('appartient', function (Blueprint $table) {
            $table->foreignId('id_vehicule')
              ->constrained('vehicule', 'id_vehicule');

            $table->foreignId('id_utilisateur')
                ->constrained('utilisateur', 'id_utilisateur');

            $table->primary(['id_vehicule', 'id_utilisateur']);
        });
    }

    /**
     * Supprime la table d’association utilisateur-véhicule.
     */
    public function down(): void
    {
        Schema::dropIfExists('appartient');
    }
};
