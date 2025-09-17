<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $roles = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => 'Admin',
                'password' => 'password123',
            ],
            [
                'name' => 'Employee User',
                'email' => 'employee@example.com',
                'role' => 'Employee',
                'password' => 'password123',
            ],
            [
                'name' => 'CEO User',
                'email' => 'ceo@example.com',
                'role' => 'CEO',
                'password' => 'password123',
            ],
            [
                'name' => 'Supervisor User',
                'email' => 'supervisor@example.com',
                'role' => 'Supervisor',
                'password' => 'password123',
            ],
        ];

        foreach ($roles as $userData) {
            $userData['password'] = bcrypt($userData['password']);
            User::factory()->create($userData);
        }
    }
}
