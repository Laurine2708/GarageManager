<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Crée le stockage SQL des sessions utilisateur. */
return new class extends Migration
{
    /**
     * Crée les colonnes de session nécessaires au pilote base de données.
     */
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Supprime la table de stockage des sessions.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
