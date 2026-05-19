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
        return $this->hasMany(ProductImage::class)
            ->orderBy('position')
            ->orderBy('id');
    }

    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->ofMany('position', 'min');
    }

    public function getCoverImageAttribute()
    {
        return $this->images()->first();
    }

    public function getPublicPrimaryImageUrlAttribute(): string
    {
        return $this->public_gallery_urls[0] ?? $this->placeholderImageUrl();
    }

    public function getPublicGalleryUrlsAttribute(): array
    {
        $canonicalUrls = $this->resolveCanonicalImageUrls();

        if ($canonicalUrls !== []) {
            return $canonicalUrls;
        }

        if ($legacyUrl = $this->resolveLegacyImageUrl()) {
            return [$legacyUrl];
        }

        return [$this->placeholderImageUrl()];
    }

    public function resolvePrimaryVariation(): ?ProductVariation
    {
        $variations = $this->relationLoaded('variations')
            ? $this->variations
            : $this->variations()->get();

        if ($variations->isEmpty()) {
            return null;
        }

        if ($this->relationLoaded('product') && $this->product) {
            $sorted = $this->product->sortVariationCollectionBySize($variations);
        } else {
            $sorted = $variations->sortBy('id');
        }

        $available = $sorted->firstWhere('stock', '>', 0);

        if ($available) {
            return $available;
        }

        return $sorted->first();
    }

    public function getDisplayPriceAttribute(): ?float
    {
        $variation = $this->resolvePrimaryVariation();

        return $variation ? (float) $variation->effective_price : null;
    }

    public function getDisplayOriginalPriceAttribute(): ?float
    {
        $variation = $this->resolvePrimaryVariation();

        return $variation ? (float) $variation->original_price : null;
    }

    public function getDisplayHasActiveOfferAttribute(): bool
    {
        $variation = $this->resolvePrimaryVariation();

        return $variation ? $variation->has_active_offer : false;
    }

    /**
     * Resolve image to a full URL (handles external URLs and storage paths).
     */
    public function getImageUrlAttribute(): string
    {
        if (! $this->image) {
            return 'https://via.placeholder.com/600x750';
        }

        return str_starts_with($this->image, 'http')
            ? $this->image
            : \Illuminate\Support\Facades\Storage::url($this->image);
    }

    protected function resolveCanonicalImageUrls(): array
    {
        $images = $this->relationLoaded('images')
            ? $this->images
            : $this->images()->get();

        return $images
            ->pluck('path')
            ->filter()
            ->map(fn (string $path) => $this->resolveStorageImageUrl($path))
            ->values()
            ->all();
    }

    protected function resolveLegacyImageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return $this->getImageUrlAttribute();
    }

    protected function resolveStorageImageUrl(string $path): string
    {
        return str_starts_with($path, 'http')
            ? $path
            : \Illuminate\Support\Facades\Storage::url($path);
    }

    protected function placeholderImageUrl(): string
    {
        return asset('img/placeholder-product.jpg');
    }
}
