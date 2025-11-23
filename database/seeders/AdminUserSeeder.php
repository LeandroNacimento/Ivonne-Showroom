<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ivonneshowroom.com'],
            [
                'name' => 'Admin Ivonne',
                'password' => Hash::make('password'), // Default password
                'role' => 'admin',
            ]
        );
    }
}
