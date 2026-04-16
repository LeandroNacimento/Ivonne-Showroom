<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_color_id',
        'size',
        'stock',
        'price',
        'sale_price',
        'sku',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function setSizeAttribute($value)
    {
        if (is_string($value) || is_int($value)) {
            $this->attributes['size'] = Product::normalizeSize($value);
        } else {
            $this->attributes['size'] = $value;
        }
    }

    /**
     * Mutator para garantizar que el stock nunca sea negativo.
     */
    public function setStockAttribute($value)
    {
        $stock = (int) $value;

        if ($stock < 0) {
            throw new \InvalidArgumentException('Stock cannot be negative');
        }

        $this->attributes['stock'] = $stock;
    }

    /**
     * Verifica si hay stock suficiente
     */
    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }

    /**
     * Disminuye el stock. Lanza excepción si queda negativo (capturado por el mutator).
     */
    public function decreaseStock(int $quantity = 1): void
    {
        $this->stock -= $quantity;
        $this->save();
    }

    /**
     * Incrementa el stock.
     */
    public function increaseStock(int $quantity = 1): void
    {
        $this->stock += $quantity;
        $this->save();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productColor()
    {
        return $this->belongsTo(ProductColor::class);
    }

    public function getOriginalPriceAttribute(): ?string
    {
        return $this->price;
    }

    public function getEffectivePriceAttribute(): ?string
    {
        if ($this->sale_price !== null && (float) $this->sale_price < (float) $this->price) {
            return $this->sale_price;
        }

        return $this->price;
    }

    public function getHasActiveOfferAttribute(): bool
    {
        return $this->sale_price !== null && (float) $this->sale_price < (float) $this->price;
    }

    public function getCartImageAttribute()
    {
        $colorImage = $this->productColor?->images?->first();
        $productImage = $this->product?->images?->first();

        return $colorImage?->path ?? $productImage?->path;
    }
}
