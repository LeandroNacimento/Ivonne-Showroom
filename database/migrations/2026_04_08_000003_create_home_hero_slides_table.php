<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_hero_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_hero_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->string('alt_text');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['home_hero_id', 'is_active', 'position', 'id'], 'home_hero_slides_public_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_hero_slides');
    }
};
