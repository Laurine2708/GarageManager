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
        Schema::create('rdv', function (Blueprint $table) {
            $table->id('id_rdv');
            $table->dateTime('date_rdv');
            $table->string('motif_rdv', 255);

            $table->foreignId('id_vehicule')
                ->constrained('vehicule', 'id_vehicule');

            $table->foreignId('id_utilisateur')
                ->constrained('utilisateur', 'id_utilisateur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rdv');
    }
};
