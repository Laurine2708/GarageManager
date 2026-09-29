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
        Schema::create('utilisateur', function (Blueprint $table) {
            $table->id('id_utilisateur');
            $table->string('nom_utilisateur', 50);
            $table->string('prenom_utilisateur', 50);
            $table->string('adresse_utilisateur', 50);
            $table->integer('CP_utilisateur');
            $table->string('ville_utilisateur', 50);
            $table->string('login_utilisateur', 50);
            $table->string('mdp_utilisateur', 255);
            $table->string('role_utilisateur', 50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateur');
    }
};
