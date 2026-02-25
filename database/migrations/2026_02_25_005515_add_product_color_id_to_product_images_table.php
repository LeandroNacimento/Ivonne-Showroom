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
        Schema::table('product_images', function (Blueprint $table) {
            $table->foreignId('product_color_id')->nullable()->constrained('product_colors')->cascadeOnDelete();
            $table->index(['product_color_id', 'position'], 'idx_images_color_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropForeign(['product_color_id']);
            $table->dropIndex('idx_images_color_position');
            $table->dropColumn('product_color_id');
        });
    }
};
