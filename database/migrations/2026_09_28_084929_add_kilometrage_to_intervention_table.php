<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Ajoute le kilométrage relevé lors d’une intervention. */
return new class extends Migration
{
    /**
     * Ajoute un kilométrage facultatif aux interventions existantes.
     */
    public function up(): void
    {
        Schema::table('intervention', function (Blueprint $table) {
            $table->integer('kilometrage_intervention')->nullable();
        });
    }

    /**
     * Retire le kilométrage lors du retour au schéma précédent.
     */
    public function down(): void
    {
        Schema::table('intervention', function (Blueprint $table) {
            $table->dropColumn('kilometrage_intervention');
        });
    }
};
