<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeHero extends Model
{
    use HasFactory;

    public const SINGLETON_KEY = 'home';

    protected $fillable = [
        'eyebrow',
        'title',
        'description',
        'cta_label',
        'cta_url',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $hero): void {
            $hero->singleton_key = self::SINGLETON_KEY;
        });
    }

    public static function singleton(): self
    {
        return static::query()->firstOrCreate([
            'singleton_key' => self::SINGLETON_KEY,
        ]);
    }

    public function slides()
    {
        return $this->hasMany(HomeHeroSlide::class)
            ->orderBy('position')
            ->orderBy('id');
    }

    public function activeSlides()
    {
        return $this->slides()->where('is_active', true);
    }

    public function getIsRenderableAttribute(): bool
    {
        if (! $this->hasConsistentCta()) {
            return false;
        }

        if ($this->relationLoaded('activeSlides')) {
            return $this->activeSlides->isNotEmpty();
        }

        return $this->activeSlides()->exists();
    }

    public function hasConsistentCta(): bool
    {
        $labelFilled = filled(trim((string) $this->cta_label));
        $urlFilled = filled(trim((string) $this->cta_url));

        return ($labelFilled && $urlFilled) || (! $labelFilled && ! $urlFilled);
    }
}
