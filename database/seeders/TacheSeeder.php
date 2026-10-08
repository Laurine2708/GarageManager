<?php

namespace Database\Seeders;

use App\Models\Tache;
use Illuminate\Database\Seeder;

/** Insère le détail des tâches de chaque intervention de démonstration. */
class TacheSeeder extends Seeder
{
    /** Crée des tâches d’exemple dans différents états d’avancement. */
    public function run(): void
    {
        // Intervention 1 - Révision complète
        Tache::create([
            'libelle_tache' => 'Vidange moteur',
            'id_statut' => 3,
            'id_intervention' => 1,
        ]);

        Tache::create([
            'libelle_tache' => 'Remplacement du filtre à huile',
            'id_statut' => 3,
            'id_intervention' => 1,
        ]);

        Tache::create([
            'libelle_tache' => 'Contrôle des niveaux',
            'id_statut' => 2,
            'id_intervention' => 1,
        ]);

        // Intervention 2 - Freins
        Tache::create([
            'libelle_tache' => 'Démontage des roues avant',
            'id_statut' => 3,
            'id_intervention' => 2,
        ]);

        Tache::create([
            'libelle_tache' => 'Remplacement des plaquettes',
            'id_statut' => 2,
            'id_intervention' => 2,
        ]);

        // Intervention 3 - Voyant moteur
        Tache::create([
            'libelle_tache' => 'Passage à la valise diagnostic',
            'id_statut' => 3,
            'id_intervention' => 3,
        ]);

        Tache::create([
            'libelle_tache' => 'Recherche de la panne',
            'id_statut' => 2,
            'id_intervention' => 3,
        ]);

        // Intervention 4 - Entretien
        Tache::create([
            'libelle_tache' => 'Contrôle général du véhicule',
            'id_statut' => 3,
            'id_intervention' => 4,
        ]);

        Tache::create([
            'libelle_tache' => 'Contrôle des freins',
            'id_statut' => 3,
            'id_intervention' => 4,
        ]);

        // Intervention 5 - Batterie
        Tache::create([
            'libelle_tache' => 'Test de la batterie',
            'id_statut' => 3,
            'id_intervention' => 5,
        ]);

        Tache::create([
            'libelle_tache' => 'Remplacement de la batterie',
            'id_statut' => 2,
            'id_intervention' => 5,
        ]);
    }
}