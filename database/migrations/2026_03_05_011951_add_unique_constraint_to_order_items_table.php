<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Detectar y eliminar duplicados antes de agregar el constraint
        // (elimina el ítem de mayor id cuando hay duplicados)
        $duplicates = DB::table('order_items')
            ->select('order_id', 'variation_id', DB::raw('MAX(id) as max_id'))
            ->groupBy('order_id', 'variation_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('order_items')
                ->where('order_id', $duplicate->order_id)
                ->where('variation_id', $duplicate->variation_id)
                ->where('id', '!=', $duplicate->max_id)
                ->delete();
        }

        Schema::table('order_items', function (Blueprint $table) {
            $table->unique(['order_id', 'variation_id'], 'order_items_order_variation_unique');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropUnique('order_items_order_variation_unique');
        });
    }
};
