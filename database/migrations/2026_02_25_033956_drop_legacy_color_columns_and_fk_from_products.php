<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            // Respaldar la foreign key creando un index simple para product_id
            $table->index('product_id', 'product_variations_product_id_index');

            // Eliminar el índice único viejo que dependía de la columna 'color'
            $table->dropUnique('product_variations_unique_combo');
            $table->dropColumn('color');

            // Crear el nuevo índice único usando la nueva relación
            $table->unique(['product_id', 'product_color_id', 'size'], 'product_variations_color_size_unique');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['color', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropUnique('product_variations_color_size_unique');
            $table->string('color')->nullable()->after('product_color_id');
            // Nota: Para recrear el índice viejo, habría de llenarse color con los datos, por lo que el rollback completo técnico es inviable sin data loss.
            $table->unique(['product_id', 'size', 'color'], 'product_variations_unique_combo');

            // Quitar el index simple que agregamos arriba ya que el combo vuelve a servirle a la FK
            $table->dropIndex('product_variations_product_id_index');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->string('color')->nullable()->after('product_color_id');
            // Recreamos product_id solo como foreign key estática sin relación activa real para permitir rollback (o simple biginteger)
            $table->unsignedBigInteger('product_id')->nullable()->after('id');
        });
    }
};
