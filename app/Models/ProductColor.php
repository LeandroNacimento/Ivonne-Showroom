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
        'image',
        'is_main',
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
        return $this->hasOne(ProductImage::class)->ofMany('position', 'min');
    }

    public function getCoverImageAttribute()
    {
        return $this->images()->first();
    }
}
