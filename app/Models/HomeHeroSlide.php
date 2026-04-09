<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomeHeroSlide extends Model
{
    use HasFactory;

    public const STORAGE_DIRECTORY = 'home-hero';

    protected $fillable = [
        'image_path',
        'alt_text',
        'position',
        'is_active',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_active' => 'boolean',
    ];

    public function homeHero()
    {
        return $this->belongsTo(HomeHero::class);
    }

    public function getPublicImageUrlAttribute(): string
    {
        return Storage::url($this->image_path);
    }
}
