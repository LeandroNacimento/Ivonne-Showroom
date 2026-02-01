<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
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
        return $this->hasMany(ProductImage::class);
    }

    public function getTotalStockAttribute()
    {
        return $this->variations->sum('stock');
    }

    public function getCoverUrlAttribute()
    {
        // Regla: La imagen principal es la de menor ID (la primera subida).
        // Determinismo: Ordenamos explícitamente.
        $cover = $this->images()->orderBy('id', 'asc')->first();

        if ($cover) {
            return asset('storage/' . $cover->path);
        }

        // Placeholder por defecto si no hay imagen
        return asset('img/placeholder-product.jpg');
    }
}
