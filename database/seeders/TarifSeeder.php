<?php

namespace Database\Seeders;

use App\Models\Tarif;
use Illuminate\Database\Seeder;

class TarifSeeder extends Seeder
{
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