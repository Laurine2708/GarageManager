<?php

namespace Database\Seeders;

use App\Models\Piece;
use Illuminate\Database\Seeder;

class PieceSeeder extends Seeder
{
    public function run(): void
    {
        Piece::create([
            'nom_piece' => 'Filtre à huile',
            'reference_piece' => 'FIL-HUI-001',
            'prix_piece' => 12.50,
            'quantite_stock_piece' => 15,
        ]);

        Piece::create([
            'nom_piece' => 'Filtre à air',
            'reference_piece' => 'FIL-AIR-001',
            'prix_piece' => 18.90,
            'quantite_stock_piece' => 10,
        ]);

        Piece::create([
            'nom_piece' => 'Filtre à carburant',
            'reference_piece' => 'FIL-CAR-001',
            'prix_piece' => 25.00,
            'quantite_stock_piece' => 8,
        ]);

        Piece::create([
            'nom_piece' => 'Plaquettes de frein avant',
            'reference_piece' => 'PLA-FRE-001',
            'prix_piece' => 45.00,
            'quantite_stock_piece' => 6,
        ]);

        Piece::create([
            'nom_piece' => 'Disques de frein avant',
            'reference_piece' => 'DIS-FRE-001',
            'prix_piece' => 80.00,
            'quantite_stock_piece' => 4,
        ]);

        Piece::create([
            'nom_piece' => 'Batterie 12V',
            'reference_piece' => 'BAT-12V-001',
            'prix_piece' => 110.00,
            'quantite_stock_piece' => 5,
        ]);

        Piece::create([
            'nom_piece' => "Bougies d'allumage",
            'reference_piece' => 'BOU-ALL-001',
            'prix_piece' => 8.50,
            'quantite_stock_piece' => 20,
        ]);

        Piece::create([
            'nom_piece' => 'Courroie de distribution',
            'reference_piece' => 'COU-DIS-001',
            'prix_piece' => 95.00,
            'quantite_stock_piece' => 3,
        ]);
    }
}