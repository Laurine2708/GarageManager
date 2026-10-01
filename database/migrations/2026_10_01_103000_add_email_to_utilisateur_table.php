<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utilisateur', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('login_utilisateur');
        });

        // Preserve existing contact addresses where login_utilisateur already contained an email.
        DB::table('utilisateur')
            ->where('login_utilisateur', 'like', '%@%')
            ->update(['email' => DB::raw('login_utilisateur')]);
    }

    public function down(): void
    {
        Schema::table('utilisateur', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn('email');
        });
    }
};
