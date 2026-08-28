<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Populates the database with the team user and the collection points.
     */
    public function run(): void
    {
        // The team user (the admin login). Prototype credentials: admin / admin.
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Equipe EcoDescarte',
                'email' => 'admin@ecodescarte.local',
                'password' => Hash::make('admin'),
            ],
        );

        $this->call(CollectionPointSeeder::class);
    }
}
