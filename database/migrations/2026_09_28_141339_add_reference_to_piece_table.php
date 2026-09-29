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
        Schema::table('piece', function (Blueprint $table) {
            $table->string('reference_piece', 255)->unique();
        });
    }

    public function down(): void
    {
        Schema::table('piece', function (Blueprint $table) {
            $table->dropColumn('reference_piece');
        });
    }
};
