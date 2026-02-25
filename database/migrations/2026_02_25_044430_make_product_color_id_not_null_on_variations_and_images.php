<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Validación estricta previa a la migración
        $nullVariations = DB::table('product_variations')->whereNull('product_color_id')->count();
        if ($nullVariations > 0) {
            throw new \Exception("Abortando migración: Existen {$nullVariations} registros en product_variations con product_color_id NULL.");
        }

        $nullImages = DB::table('product_images')->whereNull('product_color_id')->count();
        if ($nullImages > 0) {
            throw new \Exception("Abortando migración: Existen {$nullImages} registros en product_images con product_color_id NULL.");
        }

        // Modificamos las columnas para hacerlas obligatorias (harden) respetando foreign keys existentes
        Schema::table('product_variations', function (Blueprint $table) {
            $table->unsignedBigInteger('product_color_id')->nullable(false)->change();
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->unsignedBigInteger('product_color_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->unsignedBigInteger('product_color_id')->nullable()->change();
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->unsignedBigInteger('product_color_id')->nullable()->change();
        });
    }
};
