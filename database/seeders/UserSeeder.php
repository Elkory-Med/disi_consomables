<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('34105905'),
            'role' => 1,
            'username' => 'admin',
            'unite' => 'Administration',
            'matricule' => '2308',
            'administration' => 'DISI',
            'status' => 'approved'
        ]);
         // Create admin user
        User::create([
            'name' => 'Moulay',
            'email' => 'Moulay@admin.com',
            'password' => Hash::make('36308334'),
            'role' => 1,
            'username' => 'Moulay-admin',
            'unite' => 'Administration',
            'matricule' => '2107',
            'administration' => 'DISI',
            'status' => 'approved'
        ]);
        // Create regular user
        User::create([
            'name' => 'Utilisateur',
            'email' => 'user@example.com',
            'password' => Hash::make('00000000'),
            'role' => 0,
            'username' => 'user',
            'unite' => 'DISI Test',
            'matricule' => '2025',
            'administration' => 'DISI',
            'status' => 'approved'
        ]);
    }
}
