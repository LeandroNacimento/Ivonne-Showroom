<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    public const DEFAULT_SIZE_TYPE = 'alpha';

    public const ONE_SIZE_TYPE = 'one_size';

    public const ONE_SIZE_VALUE = 'UNICO';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'size_type',
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

    public static function sizeTypeRegistry(): array
    {
        return config('product_sizes.types', []);
    }

    public static function sizeTypeOptions(): array
    {
        return collect(self::sizeTypeRegistry())
            ->mapWithKeys(fn (array $config, string $type) => [$type => $config['label']])
            ->all();
    }

    public static function isValidSizeType(?string $sizeType): bool
    {
        return is_string($sizeType) && array_key_exists($sizeType, self::sizeTypeRegistry());
    }

    public static function getSizeTypeConfig(?string $sizeType): array
    {
        $resolvedType = self::isValidSizeType($sizeType)
            ? $sizeType
            : config('product_sizes.default', self::DEFAULT_SIZE_TYPE);

        return self::sizeTypeRegistry()[$resolvedType] ?? [];
    }

    public static function getAllowedSizes(?string $sizeType): array
    {
        return self::getSizeTypeConfig($sizeType)['values'] ?? [];
    }

    public static function normalizeSize(null|string|int $size): ?string
    {
        if ($size === null) {
            return null;
        }

        $normalized = str_replace("\xC2\xA0", ' ', (string) $size);
        $normalized = trim($normalized);

        if ($normalized === '') {
            return '';
        }

        $normalized = mb_strtoupper(Str::ascii($normalized), 'UTF-8');

        if ($normalized === 'UNICO') {
            return self::ONE_SIZE_VALUE;
        }

        return $normalized;
    }

    public static function presentSize(?string $size): string
    {
        $normalized = self::normalizeSize($size);

        if ($normalized === self::ONE_SIZE_VALUE) {
            return config('product_sizes.one_size_label', 'Único');
        }

        return $normalized ?? '';
    }

    public static function sortSizes(iterable $sizes, ?string $sizeType): array
    {
        $allowedSizes = self::getAllowedSizes($sizeType);
        $order = array_flip($allowedSizes);

        return collect($sizes)
            ->map(fn ($size) => self::normalizeSize($size))
            ->filter(fn ($size) => $size !== null && $size !== '')
            ->unique()
            ->sortBy(fn ($size) => $order[$size] ?? PHP_INT_MAX)
            ->values()
            ->all();
    }

    public static function sizeSortIndex(?string $size, ?string $sizeType): int
    {
        $normalized = self::normalizeSize($size);
        $order = array_flip(self::getAllowedSizes($sizeType));

        return $order[$normalized] ?? PHP_INT_MAX;
    }

    public static function inferSizeTypeFromSizes(array $sizes, ?bool $fallbackSupportsSize = null): string
    {
        $normalizedSizes = collect($sizes)
            ->map(fn ($size) => self::normalizeSize($size))
            ->filter(fn ($size) => $size !== null && $size !== '')
            ->unique()
            ->values()
            ->all();

        if ($normalizedSizes === []) {
            return $fallbackSupportsSize === false
                ? config('product_sizes.one_size_type', self::ONE_SIZE_TYPE)
                : config('product_sizes.default', self::DEFAULT_SIZE_TYPE);
        }

        foreach (self::sizeTypeRegistry() as $type => $config) {
            $allowedSizes = $config['values'] ?? [];

            if ($allowedSizes !== [] && count(array_diff($normalizedSizes, $allowedSizes)) === 0) {
                return $type;
            }
        }

        return $fallbackSupportsSize === false
            ? config('product_sizes.one_size_type', self::ONE_SIZE_TYPE)
            : config('product_sizes.default', self::DEFAULT_SIZE_TYPE);
    }

    public function sortVariationCollectionBySize(Collection $variations): Collection
    {
        return $variations
            ->sortBy(fn ($variation) => self::sizeSortIndex($variation->size ?? null, $this->resolved_size_type))
            ->values();
    }

    public function availableVariations()
    {
        return $this->variations()->where('stock', '>', 0);
    }

    public function getDisplayPriceAttribute(): ?float
    {
        if (array_key_exists('storefront_display_price', $this->attributes)) {
            $value = $this->attributes['storefront_display_price'];

            return $value !== null ? (float) $value : null;
        }

        $variation = $this->resolveDisplayVariation();

        return $variation ? (float) $variation->effective_price : null;
    }

    public function getDisplayOriginalPriceAttribute(): ?float
    {
        if (array_key_exists('storefront_display_original_price', $this->attributes)) {
            $value = $this->attributes['storefront_display_original_price'];

            return $value !== null ? (float) $value : null;
        }

        $variation = $this->resolveDisplayVariation();

        return $variation ? (float) $variation->original_price : null;
    }

    public function getDisplayHasActiveOfferAttribute(): bool
    {
        if (array_key_exists('storefront_display_has_active_offer', $this->attributes)) {
            return (bool) $this->attributes['storefront_display_has_active_offer'];
        }

        $variations = $this->resolveAvailableVariations();

        return $variations->contains(fn (ProductVariation $variation) => $variation->has_active_offer);
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
            return '$'.number_format((float) $min, 0, ',', '.');
        }

        return '$'.number_format((float) $min, 0, ',', '.').' - $'.number_format((float) $max, 0, ',', '.');
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
            return asset('storage/'.$cover->path);
        }

        return asset('img/placeholder-product.jpg');
    }

    public function getPublicPrimaryImageUrlAttribute(): string
    {
        $colors = $this->relationLoaded('colors')
            ? $this->colors
            : $this->colors()->with('images')->get();

        $primaryColor = $colors->first();

        if ($primaryColor) {
            return $primaryColor->public_primary_image_url;
        }

        return $this->cover_url;
    }

    public function getResolvedSizeTypeAttribute(): string
    {
        if (self::isValidSizeType($this->size_type)) {
            return $this->size_type;
        }

        $sizes = $this->relationLoaded('variations')
            ? $this->variations->pluck('size')->all()
            : $this->variations()->pluck('size')->all();

        return self::inferSizeTypeFromSizes($sizes, $this->category?->supports_size);
    }

    public function getAvailableSizesAttribute(): array
    {
        if (! $this->relationLoaded('variations')) {
            $this->load('variations');
        }

        $sortedSizes = self::sortSizes(
            $this->variations->where('stock', '>', 0)->pluck('size')->all(),
            $this->resolved_size_type
        );

        return array_map(fn ($size) => self::presentSize($size), $sortedSizes);
    }

    public function getHasSizesAttribute(): bool
    {
        return $this->resolved_size_type !== config('product_sizes.one_size_type', self::ONE_SIZE_TYPE);
    }

    public function getAvailabilityLabelAttribute(): string
    {
        if (! $this->has_sizes) {
            return $this->total_stock > 0
                ? config('product_sizes.one_size_availability_label', 'Talle único')
                : 'Sin stock';
        }

        $sizes = $this->available_sizes;

        if (count($sizes) === 0) {
            return 'Sin stock';
        }

        return 'Disponible en '.implode(' - ', $sizes);
    }

    /**
     * Whether the product was created within the last 15 days.
     */
    public function getIsNewAttribute(): bool
    {
        return $this->created_at->diffInDays(now()) <= 15;
    }

    /**
     * Whether the product has low stock (1-3 units total).
     */
    public function getIsLowStockAttribute(): bool
    {
        $total = $this->total_stock;

        return $total > 0 && $total <= 3;
    }

    public function scopeWithStorefrontPricing($query)
    {
        $effectivePriceExpression = 'MIN(CASE WHEN product_variations.sale_price IS NOT NULL AND product_variations.sale_price < product_variations.price THEN product_variations.sale_price ELSE product_variations.price END)';

        return $query
            ->addSelect([
                'storefront_display_price' => ProductVariation::query()
                    ->selectRaw($effectivePriceExpression)
                    ->whereColumn('product_variations.product_id', 'products.id')
                    ->where('product_variations.stock', '>', 0),
                'storefront_display_has_active_offer' => ProductVariation::query()
                    ->selectRaw('MAX(CASE WHEN product_variations.sale_price IS NOT NULL AND product_variations.sale_price < product_variations.price THEN 1 ELSE 0 END)')
                    ->whereColumn('product_variations.product_id', 'products.id')
                    ->where('product_variations.stock', '>', 0),
            ]);
    }

    private function resolveAvailableVariations(): Collection
    {
        if ($this->relationLoaded('variations')) {
            return $this->variations
                ->where('stock', '>', 0)
                ->values();
        }

        return $this->availableVariations()
            ->orderBy('id')
            ->get();
    }

    private function resolveDisplayVariation(): ?ProductVariation
    {
        $variations = $this->resolveAvailableVariations();

        if ($variations->isEmpty()) {
            return null;
        }

        return $variations
            ->sort(function (ProductVariation $left, ProductVariation $right) {
                $priceComparison = (float) $left->effective_price <=> (float) $right->effective_price;

                if ($priceComparison !== 0) {
                    return $priceComparison;
                }

                return $left->id <=> $right->id;
            })
            ->first();
    }
}
