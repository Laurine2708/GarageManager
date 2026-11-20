<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Convertit la durée d’intervention en nombre entier de minutes. */
return new class extends Migration
{
    /**
     * Remplace le type texte de la durée par un entier.
     */
    public function up(): void
    {
        Schema::table('intervention', function (Blueprint $table) {
            $table->integer('temps_intervention')->change();
        });
    }

    /** Rétablit le type texte antérieur à cette conversion. */
    public function down(): void
    {
        Schema::table('intervention', function (Blueprint $table) {
            $table->string('temps_intervention', 50)->change();
        });
    }
};
