<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Ajoute un numéro de téléphone facultatif aux utilisateurs. */
return new class extends Migration
{
    /**
     * Ajoute les coordonnées téléphoniques au schéma utilisateur.
     */
    public function up(): void
    {
        Schema::table('utilisateur', function (Blueprint $table) {
            $table->string('tel_utilisateur', 50)->nullable();
        });
    }

    /** Retire la colonne téléphone pour annuler l’évolution. */
    public function down(): void
    {
        Schema::table('utilisateur', function (Blueprint $table) {
            $table->dropColumn('tel_utilisateur');
        });
    }
};
