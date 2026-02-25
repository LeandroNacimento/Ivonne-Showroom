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
            $table->foreignId('product_color_id')->nullable()->constrained('product_colors')->cascadeOnDelete();
            $table->index(['product_color_id', 'stock'], 'idx_variations_color_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropForeign(['product_color_id']);
            $table->dropIndex('idx_variations_color_stock');
            $table->dropColumn('product_color_id');
        });
    }
};
