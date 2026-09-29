<?php

namespace Database\Seeders;

use App\Models\Rdv;
use Illuminate\Database\Seeder;

class RdvSeeder extends Seeder
{
    public function run(): void
    {
        Rdv::create([
            'date_rdv' => '2026-10-05 09:00:00',
            'motif_rdv' => 'Révision',
            'id_vehicule' => 1,
            'id_utilisateur' => 1,
        ]);

        Rdv::create([
            'date_rdv' => '2026-10-06 14:00:00',
            'motif_rdv' => 'Problème de freinage',
            'id_vehicule' => 2,
            'id_utilisateur' => 2,
        ]);

        Rdv::create([
            'date_rdv' => '2026-10-07 10:30:00',
            'motif_rdv' => 'Voyant moteur',
            'id_vehicule' => 3,
            'id_utilisateur' => 3,
        ]);

        Rdv::create([
            'date_rdv' => '2026-10-08 15:30:00',
            'motif_rdv' => 'Entretien',
            'id_vehicule' => 1,
            'id_utilisateur' => 1,
        ]);

        Rdv::create([
            'date_rdv' => '2026-10-09 08:30:00',
            'motif_rdv' => 'Changement de batterie',
            'id_vehicule' => 2,
            'id_utilisateur' => 2,
        ]);
    }
}