<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductColor extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'position',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)
            ->where('is_main', true);
    }

    /**
     * Get the cover image for this color.
     */
    public function getCoverImageAttribute()
    {
        return $this->images()->where('is_cover', true)->first()
            ?? $this->images()->first();
    }
}
