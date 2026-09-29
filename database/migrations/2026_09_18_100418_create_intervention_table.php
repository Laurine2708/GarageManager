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
        Schema::create('intervention', function (Blueprint $table) {
            $table->id('id_intervention');
            $table->string('description_intervention', 255);
            $table->string('travaux_intervention', 255);
            $table->decimal('temps_intervention', 10, 2);
            $table->date('date_depart_intervention');

            $table->foreignId('id_rdv')
                ->constrained('rdv', 'id_rdv');

            $table->foreignId('id_tarif')
                ->constrained('tarif', 'id_tarif');

            $table->foreignId('id_utilisateur')
                ->constrained('utilisateur', 'id_utilisateur');

            $table->foreignId('id_statut')
                ->constrained('statut', 'id_statut');

            $table->foreignId('id_vehicule')
                ->constrained('vehicule', 'id_vehicule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intervention');
    }
};
