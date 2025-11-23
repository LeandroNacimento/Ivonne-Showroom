<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'whatsapp_number' => '+54 9 370 455-0445',
            'instagram_url' => 'https://instagram.com/ivonneshowroom',
            'facebook_url' => 'https://facebook.com/ivonneshowroom',
            'address' => 'Napoleón Uriburu 1366, Formosa Capital',
            'email' => 'contacto@ivonneshowroom.com',
            'hours' => 'Lunes a Sábado: 9:00 - 12:30 / 17:00 - 21:00',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
