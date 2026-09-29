<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UtilisateurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Utilisateur::create([
            'nom_utilisateur' => 'Dupont',
            'prenom_utilisateur' => 'Jean',
            'adresse_utilisateur' => '10 rue de la République',
            'CP_utilisateur' => 87000,
            'ville_utilisateur' => 'Limoges',
            'login_utilisateur' => 'jean.dupont',
            'mdp_utilisateur' => Hash::make('password'),
            'role_utilisateur' => 'client',
        ]);

        Utilisateur::create([
            'nom_utilisateur' => 'Martin',
            'prenom_utilisateur' => 'Sophie',
            'adresse_utilisateur' => '25 avenue Jean Jaurès',
            'CP_utilisateur' => 87000,
            'ville_utilisateur' => 'Limoges',
            'login_utilisateur' => 'sophie.martin',
            'mdp_utilisateur' => Hash::make('password'),
            'role_utilisateur' => 'client',
        ]);

        Utilisateur::create([
            'nom_utilisateur' => 'Bernard',
            'prenom_utilisateur' => 'Thomas',
            'adresse_utilisateur' => '5 rue Victor Hugo',
            'CP_utilisateur' => 87000,
            'ville_utilisateur' => 'Limoges',
            'login_utilisateur' => 'thomas.bernard',
            'mdp_utilisateur' => Hash::make('password'),
            'role_utilisateur' => 'client',
        ]);

        Utilisateur::create([
    'nom_utilisateur' => 'Leroy',
    'prenom_utilisateur' => 'Pierre',
    'adresse_utilisateur' => '15 rue des Écoles',
    'CP_utilisateur' => 87000,
    'ville_utilisateur' => 'Limoges',
    'login_utilisateur' => 'pierre.leroy',
    'mdp_utilisateur' => Hash::make('password'),
    'role_utilisateur' => 'mecanicien',
]);

Utilisateur::create([
    'nom_utilisateur' => 'Penaud',
    'prenom_utilisateur' => 'Laurine',
    'adresse_utilisateur' => '14 fontenille',
    'CP_utilisateur' => 87300,
    'ville_utilisateur' => 'Berneuil',
    'login_utilisateur' => 'laurine.penaud',
    'mdp_utilisateur' => Hash::make('Eclipse16!'),
    'role_utilisateur' => 'administrateur',
]);
    }
}