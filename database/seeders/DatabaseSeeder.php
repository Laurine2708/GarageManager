<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/** Orchestre le peuplement initial des données de développement. */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Crée un compte de démonstration pour les essais et le développement.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
