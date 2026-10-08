<?php

namespace Database\Seeders;

use App\Models\Tarif;
use Illuminate\Database\Seeder;

/** Insère les niveaux tarifaires utilisés par les interventions d’exemple. */
class TarifSeeder extends Seeder
{
    /** Peuple le référentiel initial des tarifs horaires. */
    public function run(): void
    {
        Tarif::create([
            'libelle_tarif' => 'T1',
            'montant_tarif' => 50.00,
        ]);

        Tarif::create([
            'libelle_tarif' => 'T2',
            'montant_tarif' => 75.00,
        ]);

        Tarif::create([
            'libelle_tarif' => 'T3',
            'montant_tarif' => 100.00,
        ]);
    }
}