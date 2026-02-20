<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

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

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)
            ->orderBy('position')
            ->orderBy('id');
    }

    /**
     * Precio mínimo entre todas las variaciones (usa query, no collection).
     */
    public function getMinPriceAttribute()
    {
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
        if ($this->relationLoaded('variations')) {
            $min = $this->variations->min('price') ?? 0;
            $max = $this->variations->max('price') ?? 0;
        } else {
            $min = $this->variations()->min('price') ?? 0;
            $max = $this->variations()->max('price') ?? 0;
        }

        if ($min == $max) {
            return '$' . number_format($min, 0, ',', '.');
        }

        return '$' . number_format($min, 0, ',', '.') . ' - $' . number_format($max, 0, ',', '.');
    }

    /**
     * Stock total sumando todas las variaciones.
     */
    public function getTotalStockAttribute()
    {
        return $this->variations()->sum('stock');
    }

    /**
     * URL de la imagen de portada (primera por position/id).
     */
    public function getCoverUrlAttribute()
    {
        $cover = $this->images()->orderBy('position')->orderBy('id')->first();

        if ($cover) {
            return asset('storage/' . $cover->path);
        }

        return asset('img/placeholder-product.jpg');
    }
}
