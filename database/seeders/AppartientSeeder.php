<?php

namespace Database\Seeders;

use App\Models\Appartient;
use Illuminate\Database\Seeder;

/** Associe les véhicules de démonstration à leurs propriétaires. */
class AppartientSeeder extends Seeder
{
    /**
     * Insère les liens utilisateur-véhicule du jeu de données initial.
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