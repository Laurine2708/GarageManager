<?php

namespace Database\Seeders;

use App\Models\Appartient;
use Illuminate\Database\Seeder;

class AppartientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Appartient::create([
            'id_vehicule' => 1,
            'id_utilisateur' => 1,
        ]);

        Appartient::create([
            'id_vehicule' => 2,
            'id_utilisateur' => 2,
        ]);

        Appartient::create([
            'id_vehicule' => 3,
            'id_utilisateur' => 3,
        ]);
    }
}