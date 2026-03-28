<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_color_id',
        'path',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * Acceder al producto mediante: $image->productColor->product
     */
    public function productColor()
    {
        return $this->belongsTo(ProductColor::class);
    }
}
