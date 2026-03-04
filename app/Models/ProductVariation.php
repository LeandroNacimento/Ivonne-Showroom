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
        'sku',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public const ALLOWED_SIZES = [
        'XS',
        'S',
        'M',
        'L',
        'XL',
        'XXL',
        'ÚNICO'
    ];

    /**
     * Mutator para normalizar automáticamente el tamaño y validar estrictamente.
     * Elimina Non-breaking spaces (NBSP), hace trim, convierte a mayúsculas y valida.
     */
    public function setSizeAttribute($value)
    {
        if (is_string($value)) {
            // Reemplaza el Non-breaking space (Unicode \xC2\xA0 / CHAR(160)) por un espacio normal
            $normalized = str_replace("\xC2\xA0", ' ', $value);

            // Aplica trim regular y convierte a mayúsculas
            $size = mb_strtoupper(trim($normalized), 'UTF-8');

            if (!in_array($size, self::ALLOWED_SIZES)) {
                throw new \InvalidArgumentException("Invalid size: '{$size}'. Allowed sizes are: " . implode(', ', self::ALLOWED_SIZES));
            }

            $this->attributes['size'] = $size;
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
            throw new \InvalidArgumentException("Stock cannot be negative");
        }

        $this->attributes['stock'] = $stock;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productColor()
    {
        return $this->belongsTo(ProductColor::class);
    }
}
