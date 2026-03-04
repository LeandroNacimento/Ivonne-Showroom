<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    public const SIZE_ORDER = [
        'XS' => 1,
        'S' => 2,
        'M' => 3,
        'L' => 4,
        'XL' => 5,
        'XXL' => 6,
    ];

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'is_featured',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function mainColor()
    {
        return $this->hasOne(ProductColor::class)->where('is_main', true);
    }

    public function colors()
    {
        return $this->hasMany(ProductColor::class)->orderBy('position');
    }

    public function variations()
    {
        return $this->hasManyThrough(ProductVariation::class, ProductColor::class);
    }

    public function images()
    {
        return $this->hasManyThrough(ProductImage::class, ProductColor::class)
            ->orderBy('product_images.position')
            ->orderBy('product_images.id');
    }

    /**
     * Precio mínimo entre todas las variaciones (usa query agregada o collection).
     */
    public function getMinPriceAttribute()
    {
        if (array_key_exists('variations_min_price', $this->attributes)) {
            return $this->attributes['variations_min_price'] ?? 0;
        }

        if ($this->relationLoaded('variations')) {
            return $this->variations->min('price') ?? 0;
        }

        return $this->variations()->min('price') ?? 0;
    }

    /**
     * Rango de precios: "$1.000" o "$1.000 - $2.000"
     */
    public function getPriceRangeAttribute(): string
    {
        if (array_key_exists('variations_min_price', $this->attributes) && array_key_exists('variations_max_price', $this->attributes)) {
            $min = $this->attributes['variations_min_price'] ?? 0;
            $max = $this->attributes['variations_max_price'] ?? 0;
        } elseif ($this->relationLoaded('variations')) {
            $min = $this->variations->min('price') ?? 0;
            $max = $this->variations->max('price') ?? 0;
        } else {
            $min = $this->variations()->min('price') ?? 0;
            $max = $this->variations()->max('price') ?? 0;
        }

        if ($min == $max) {
            return '$' . number_format((float) $min, 0, ',', '.');
        }

        return '$' . number_format((float) $min, 0, ',', '.') . ' - $' . number_format((float) $max, 0, ',', '.');
    }

    /**
     * Stock total sumando todas las variaciones.
     */
    public function getTotalStockAttribute()
    {
        if (array_key_exists('variations_sum_stock', $this->attributes)) {
            return $this->attributes['variations_sum_stock'] ?? 0;
        }

        if ($this->relationLoaded('variations')) {
            return $this->variations->sum('stock');
        }
        return $this->variations()->sum('stock');
    }

    public function getCoverUrlAttribute()
    {
        if ($this->relationLoaded('images')) {
            $cover = $this->images->first();
        } else {
            $cover = $this->images()->orderBy('position')->orderBy('id')->first();
        }

        if ($cover) {
            return asset('storage/' . $cover->path);
        }

        return asset('img/placeholder-product.jpg');
    }

    public function getAvailableSizesAttribute(): array
    {
        if (!$this->relationLoaded('variations')) {
            $this->load('variations');
        }

        return $this->variations
            ->where('stock', '>', 0)
            ->pluck('size')
            ->filter()
            ->unique()
            ->sortBy(fn($size) => self::SIZE_ORDER[$size] ?? 999)
            ->values()
            ->toArray();
    }

    public function getHasSizesAttribute(): bool
    {
        return $this->category?->supports_size ?? false;
    }

    public function getAvailabilityLabelAttribute(): string
    {
        if (!$this->has_sizes) {
            return $this->total_stock > 0 ? 'Disponible' : 'Sin stock';
        }

        $sizes = $this->available_sizes;

        if (count($sizes) === 0) {
            return 'Sin stock';
        }

        if (count($sizes) === 1) {
            return 'Talle único';
        }

        return 'Disponible en ' . implode(' - ', $sizes);
    }
}
