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
        Schema::create('appartient', function (Blueprint $table) {
            $table->foreignId('id_vehicule')
              ->constrained('vehicule', 'id_vehicule');

            $table->foreignId('id_utilisateur')
                ->constrained('utilisateur', 'id_utilisateur');

            $table->primary(['id_vehicule', 'id_utilisateur']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appartient');
    }
};
