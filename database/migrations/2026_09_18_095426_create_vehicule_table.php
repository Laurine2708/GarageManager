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
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicule');
    }
};
