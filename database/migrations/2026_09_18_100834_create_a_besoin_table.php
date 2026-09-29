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
        Schema::create('a_besoin', function (Blueprint $table) {
            $table->foreignId('id_intervention')
              ->constrained('intervention', 'id_intervention');

            $table->foreignId('id_piece')
                ->constrained('piece', 'id_piece');

            $table->primary(['id_intervention', 'id_piece']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('a_besoin');
    }
};
