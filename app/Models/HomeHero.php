<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeHero extends Model
{
    use HasFactory;

    public const SINGLETON_KEY = 'home';

    protected $fillable = [
        'singleton_key',
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
        if ($this->relationLoaded('activeSlides')) {
            return $this->activeSlides->isNotEmpty();
        }

        return $this->activeSlides()->exists();
    }
}
