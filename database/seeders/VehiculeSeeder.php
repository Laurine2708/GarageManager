<?php

namespace Database\Seeders;

use App\Models\Vehicule;
use Illuminate\Database\Seeder;

class VehiculeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Vehicule::create([
            'marque_vehicule' => 'Peugeot',
            'modele_vehicule' => '308',
            'immatriculation_vehicule' => 'AB-123-CD',
            'date_mec_vehicule' => '2020-06-15',
            'motorisation_vehicule' => '1.2 PureTech 130',
            'vin_vehicule' => 'VF3ABCD1234567890',
            'code_moteur_vehicule' => 'EB2ADTS',
        ]);

        Vehicule::create([
            'marque_vehicule' => 'Renault',
            'modele_vehicule' => 'Clio V',
            'immatriculation_vehicule' => 'EF-456-GH',
            'date_mec_vehicule' => '2021-03-20',
            'motorisation_vehicule' => '1.0 TCe 90',
            'vin_vehicule' => 'VF1EFGH2345678901',
            'code_moteur_vehicule' => 'H4D',
        ]);

        Vehicule::create([
            'marque_vehicule' => 'Citroën',
            'modele_vehicule' => 'C3',
            'immatriculation_vehicule' => 'IJ-789-KL',
            'date_mec_vehicule' => '2019-09-10',
            'motorisation_vehicule' => '1.6 HDi 100',
            'vin_vehicule' => 'VF7IJKL3456789012',
            'code_moteur_vehicule' => 'BHY',
        ]);
    }
}