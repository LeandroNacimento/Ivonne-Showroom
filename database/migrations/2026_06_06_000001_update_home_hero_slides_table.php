<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_hero_slides', function (Blueprint $table) {
            $table->string('name', 100)->nullable()->after('home_hero_id');
            // Temporal con default vacío para poder agregar NOT NULL
            $table->string('desktop_image_path')->default('')->after('name');
            $table->string('mobile_image_path')->nullable()->after('desktop_image_path');
            $table->enum('link_type', ['none', 'external'])->default('none')->after('alt_text');
            $table->string('link_url', 2048)->nullable()->after('link_type');
        });

        // Copiar image_path existente a desktop_image_path
        DB::statement('UPDATE home_hero_slides SET desktop_image_path = image_path');

        Schema::table('home_hero_slides', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('home_hero_slides', function (Blueprint $table) {
            // Restaurar image_path con default temporal
            $table->string('image_path')->default('')->after('home_hero_id');
        });

        DB::statement('UPDATE home_hero_slides SET image_path = desktop_image_path');

        Schema::table('home_hero_slides', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'desktop_image_path',
                'mobile_image_path',
                'link_type',
                'link_url',
            ]);
        });
    }
};
