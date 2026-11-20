<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Crée les rendez-vous et leurs liens vers le client et le véhicule concernés. */
return new class extends Migration
{
    /**
     * Crée les rendez-vous avec leurs clés étrangères métier.
     */
    public function up(): void
    {
        Schema::create('rdv', function (Blueprint $table) {
            $table->id('id_rdv');
            $table->dateTime('date_rdv');
            $table->string('motif_rdv', 255);

            $table->foreignId('id_vehicule')
                ->constrained('vehicule', 'id_vehicule');

            $table->foreignId('id_utilisateur')
                ->constrained('utilisateur', 'id_utilisateur');
        });
    }

    /**
     * Supprime la table des rendez-vous.
     */
    public function down(): void
    {
        Schema::dropIfExists('rdv');
    }
};
