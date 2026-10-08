<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Crée la table descriptive des véhicules suivis par le garage. */
return new class extends Migration
{
    /**
     * Crée les colonnes d’identification et de motorisation des véhicules.
     */
    public function up(): void
    {
        Schema::create('vehicule', function (Blueprint $table) {
            $table->id('id_vehicule');
            $table->string('marque_vehicule', 50);
            $table->string('modele_vehicule', 50);
            $table->string('immatriculation_vehicule', 50);
            $table->date('date_mec_vehicule');
            $table->string('motorisation_vehicule', 50);
            $table->string('vin_vehicule', 255);
            $table->string('code_moteur_vehicule', 50);
        });
    }

    /**
     * Supprime la table des véhicules.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicule');
    }
};
