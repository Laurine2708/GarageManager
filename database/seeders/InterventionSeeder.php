<?php

namespace Database\Seeders;

use App\Models\Intervention;
use Illuminate\Database\Seeder;

class InterventionSeeder extends Seeder
{
    public function run(): void
    {
        Intervention::create([
            'description_intervention' => 'Révision complète du véhicule',
            'temps_intervention' => 120,
            'date_depart_intervention' => '2026-10-05',
            'id_rdv' => 1,
            'id_tarif' => 1,
            'id_utilisateur' => 4,
            'id_statut' => 2,
            'id_vehicule' => 1,
            'kilometrage_intervention' => 65000,
        ]);

        Intervention::create([
            'description_intervention' => 'Remplacement des freins avant',
            'temps_intervention' => 90,
            'date_depart_intervention' => '2026-10-06',
            'id_rdv' => 2,
            'id_tarif' => 2,
            'id_utilisateur' => 4,
            'id_statut' => 1,
            'id_vehicule' => 2,
            'kilometrage_intervention' => 48000,
        ]);

        Intervention::create([
            'description_intervention' => 'Diagnostic du voyant moteur',
            'temps_intervention' => 60,
            'date_depart_intervention' => '2026-10-07',
            'id_rdv' => 3,
            'id_tarif' => 1,
            'id_utilisateur' => 4,
            'id_statut' => 2,
            'id_vehicule' => 3,
            'kilometrage_intervention' => 92000,
        ]);

        Intervention::create([
            'description_intervention' => 'Entretien du véhicule',
            'temps_intervention' => 150,
            'date_depart_intervention' => '2026-10-08',
            'id_rdv' => 4,
            'id_tarif' => 3,
            'id_utilisateur' => 4,
            'id_statut' => 3,
            'id_vehicule' => 1,
            'kilometrage_intervention' => 67000,
        ]);

        Intervention::create([
            'description_intervention' => 'Remplacement de la batterie',
            'temps_intervention' => 45,
            'date_depart_intervention' => '2026-10-09',
            'id_rdv' => 5,
            'id_tarif' => 2,
            'id_utilisateur' => 4,
            'id_statut' => 4,
            'id_vehicule' => 2,
            'kilometrage_intervention' => 50000,
        ]);
    }
}