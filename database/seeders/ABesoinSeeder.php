<?php

namespace Database\Seeders;

use App\Models\ABesoin;
use Illuminate\Database\Seeder;

/** Associe les pièces de démonstration aux interventions qui les requièrent. */
class ABesoinSeeder extends Seeder
{
    /** Insère les besoins en pièces du jeu de données initial. */
    public function run(): void
    {
        // Intervention 1 - Révision complète
        ABesoin::create([
            'id_intervention' => 1,
            'id_piece' => 1,
        ]);

        ABesoin::create([
            'id_intervention' => 1,
            'id_piece' => 2,
        ]);

        // Intervention 2 - Freins
        ABesoin::create([
            'id_intervention' => 2,
            'id_piece' => 4,
        ]);

        ABesoin::create([
            'id_intervention' => 2,
            'id_piece' => 5,
        ]);

        // Intervention 3 - Voyant moteur
        ABesoin::create([
            'id_intervention' => 3,
            'id_piece' => 3,
        ]);

        // Intervention 4 - Entretien
        ABesoin::create([
            'id_intervention' => 4,
            'id_piece' => 1,
        ]);

        ABesoin::create([
            'id_intervention' => 4,
            'id_piece' => 7,
        ]);

        // Intervention 5 - Batterie
        ABesoin::create([
            'id_intervention' => 5,
            'id_piece' => 6,
        ]);
    }
}