<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Crée la relation entre les pièces nécessaires et les interventions. */
return new class extends Migration
{
    /**
     * Crée la table pivot avec une clé primaire composée des deux références.
     */
    public function up(): void
    {
        Schema::create('a_besoin', function (Blueprint $table) {
            $table->foreignId('id_intervention')
              ->constrained('intervention', 'id_intervention');

            $table->foreignId('id_piece')
                ->constrained('piece', 'id_piece');

            $table->primary(['id_intervention', 'id_piece']);
        });
    }

    /**
     * Supprime la table d’association intervention-pièce.
     */
    public function down(): void
    {
        Schema::dropIfExists('a_besoin');
    }
};
