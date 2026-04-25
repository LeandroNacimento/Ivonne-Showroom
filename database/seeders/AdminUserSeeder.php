<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) config('admin-user.email');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => (string) config('admin-user.name'),
                'password' => (string) config('admin-user.password'),
                'role' => 'admin',
            ]
        );
    }
}
