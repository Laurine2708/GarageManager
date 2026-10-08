<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Crée le catalogue des pièces et leurs quantités en stock. */
return new class extends Migration
{
    /**
     * Crée les colonnes de désignation, prix et stock des pièces.
     */
    public function up(): void
    {
        Schema::create('piece', function (Blueprint $table) {
            $table->id('id_piece');
            $table->string('nom_piece', 50);
            $table->decimal('prix_piece', 10, 2);
            $table->integer('quantite_stock_piece');
        });
    }

    /**
     * Supprime la table du catalogue de pièces.
     */
    public function down(): void
    {
        Schema::dropIfExists('piece');
    }
};
