<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@ivonneshowroom.com')],
            [
                'name' => 'Admin Ivonne',
                'password' => env('ADMIN_PASSWORD', 'password'),
                'role' => 'admin',
            ]
        );
    }
}
