<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryFlagsSeeder extends Seeder
{
    public function run()
    {
        // 1. Categories with BOTH Size and Color
        DB::table('categories')
            ->whereIn('name', ['Vestidos', 'Blusas', 'Pantalones', 'Tops', 'Abrigos'])
            ->update([
                'supports_size' => true,
                'supports_color' => true
            ]);

        // 2. Categories with ONLY Color
        DB::table('categories')
            ->whereIn('name', ['Carteras', 'Accesorios'])
            ->update([
                'supports_size' => false,
                'supports_color' => true
            ]);

        // 3. Any other logic if needed
    }
}
