<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomeHeroSlide extends Model
{
    use HasFactory;

    public const STORAGE_DIRECTORY_DESKTOP = 'home-hero/desktop';

    public const STORAGE_DIRECTORY_MOBILE = 'home-hero/mobile';

    protected $fillable = [
        'name',
        'desktop_image_path',
        'mobile_image_path',
        'link_type',
        'link_url',
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

    /**
     * URL pública de la imagen desktop.
     */
    public function getPublicDesktopImageUrlAttribute(): string
    {
        return Storage::url($this->desktop_image_path);
    }

    /**
     * URL pública de la imagen mobile.
     * Fallback a la desktop si no hay imagen mobile cargada.
     */
    public function getPublicMobileImageUrlAttribute(): string
    {
        return Storage::url($this->mobile_image_path ?? $this->desktop_image_path);
    }

    /**
     * Indica si el slide tiene una imagen mobile propia (no fallback).
     */
    public function getHasMobileImageAttribute(): bool
    {
        return $this->mobile_image_path !== null;
    }

    /**
     * URL final resuelta para el frontend.
     * Retorna null si no hay destino configurado.
     */
    public function getResolvedLinkUrlAttribute(): ?string
    {
        if ($this->link_type === 'external' && filled($this->link_url)) {
            return $this->link_url;
        }

        return null;
    }
}
