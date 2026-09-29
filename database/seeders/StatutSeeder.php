<?php

namespace Database\Seeders;

use App\Models\Statut;
use Illuminate\Database\Seeder;

class StatutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Statut::create([
            'nom_statut' => 'En attente',
        ]);

        Statut::create([
            'nom_statut' => 'En cours',
        ]);

        Statut::create([
            'nom_statut' => 'Terminée',
        ]);

        Statut::create([
            'nom_statut' => 'À contrôler',
        ]);
    }
}
