<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Crée le référentiel des tarifs applicables aux interventions. */
return new class extends Migration
{
    /**
     * Crée les libellés et montants de tarifs.
     */
    public function up(): void
    {
        Schema::create('tarif', function (Blueprint $table) {
            $table->id('id_tarif');
            $table->string('libelle_tarif', 50);
            $table->decimal('montant_tarif', 10, 2);
        });
    }

    /**
     * Supprime le référentiel des tarifs.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarif');
    }
};
