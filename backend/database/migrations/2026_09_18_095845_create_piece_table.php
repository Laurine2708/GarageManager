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
        Schema::create('piece', function (Blueprint $table) {
            $table->id('id_piece');
            $table->string('nom_piece', 50);
            $table->decimal('prix_piece', 10, 2);
            $table->integer('quantite_stock_piece');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piece');
    }
};
