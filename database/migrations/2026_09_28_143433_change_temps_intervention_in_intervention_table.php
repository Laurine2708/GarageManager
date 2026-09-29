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
        Schema::table('intervention', function (Blueprint $table) {
            $table->integer('temps_intervention')->change();
        });
    }

    public function down(): void
    {
        Schema::table('intervention', function (Blueprint $table) {
            $table->string('temps_intervention', 50)->change();
        });
    }
};
