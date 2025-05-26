<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@admin.com',
            'unite' => 'ALL',
            'matricule' => 'ADMIN001',
            'password' => Hash::make('password'),
            'role' => 1,
        ]);
    }
}
