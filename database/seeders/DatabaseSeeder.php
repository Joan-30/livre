<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Utilisateur de test par défaut (Laravel)
        User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Données de démonstration pour le moteur d'affinité hybride
        $this->call(AffiniteSeeder::class);
    }
}
