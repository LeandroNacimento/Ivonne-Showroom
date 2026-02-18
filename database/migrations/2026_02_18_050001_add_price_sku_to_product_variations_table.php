<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0)->after('stock');
            $table->string('sku')->nullable()->after('price');
            $table->unique(['product_id', 'color', 'size'], 'product_variations_unique_combo');
        });

        // Migrate existing product prices to their variations
        DB::statement('
            UPDATE product_variations
            SET price = (
                SELECT price FROM products WHERE products.id = product_variations.product_id
            )
        ');
    }

    public function down(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropUnique('product_variations_unique_combo');
            $table->dropColumn(['price', 'sku']);
        });
    }
};
