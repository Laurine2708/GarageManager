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
        Schema::create('tache', function (Blueprint $table) {
            $table->id('id_tache');
            $table->string('libelle_tache', 255);

            $table->foreignId('id_statut')
                ->constrained('statut', 'id_statut');

            $table->foreignId('id_intervention')
                ->constrained('intervention', 'id_intervention')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tache');
    }
};
