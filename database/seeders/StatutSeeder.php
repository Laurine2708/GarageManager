<?php

namespace Database\Seeders;

use App\Models\Statut;
use Illuminate\Database\Seeder;

/** Définit les statuts de référence utilisés dans le suivi des travaux. */
class StatutSeeder extends Seeder
{
    /**
     * Insère les états initiaux des tâches et interventions.
     */
    public function run(): void
    {
        Statut::create([
            'nom_statut' => 'À faire',
        ]);

        Statut::create([
            'nom_statut' => 'En cours',
        ]);

        Statut::create([
            'nom_statut' => 'Terminée',
        ]);

    }
}
