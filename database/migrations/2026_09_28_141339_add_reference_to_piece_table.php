<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Ajoute une référence unique pour identifier chaque pièce du catalogue. */
return new class extends Migration
{
    /**
     * Ajoute la référence unique à la table des pièces.
     */
    public function up(): void
    {
        Schema::table('piece', function (Blueprint $table) {
            $table->string('reference_piece', 255)->unique();
        });
    }

    /** Supprime la référence du catalogue lors de l’annulation. */
    public function down(): void
    {
        Schema::table('piece', function (Blueprint $table) {
            $table->dropColumn('reference_piece');
        });
    }
};
