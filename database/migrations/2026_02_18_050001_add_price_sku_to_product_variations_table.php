<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0);
            $table->string('sku')->nullable();
            $table->unique(['product_id', 'color', 'size'], 'product_variations_unique_combo');
        });

        // Migrate existing product prices to their variations (MySQL only — not needed on fresh SQLite test DBs)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('
                UPDATE product_variations
                SET price = COALESCE((
                    SELECT price FROM products WHERE products.id = product_variations.product_id
                ), 0)
            ');
        }
    }

    public function down(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropUnique('product_variations_unique_combo');
            $table->dropColumn(['price', 'sku']);
        });
    }
};
