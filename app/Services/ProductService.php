<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductVariation;

class ProductService
{
    /**
     * Handles the dual-write creation/update of Product colors and variations.
     *
     * @param  Product  $product  The base product instance
     * @param  array  $variationsData  Array of flat variations from the request
     */
    public function syncVariations(Product $product, array $variationsData): void
    {
        // 1. Group input by normalized color name
        $colorGroups = collect($variationsData)->groupBy(function ($v) {
            return strtolower(trim($v['color']));
        });

        $colorIdMap = []; // ['normalized_color' => $product_color_id]
        $processedIds = [];
        $position = 0;

        foreach ($colorGroups as $normalizedName => $vars) {
            $originalName = trim($vars->first()['color']);
            $submittedColorId = $vars->first()['color_id'] ?? null;

            if ($submittedColorId) {
                $productColor = ProductColor::where('id', $submittedColorId)
                    ->where('product_id', $product->id)
                    ->first();

                if ($productColor) {
                    $productColor->update([
                        'name' => $originalName,
                        'position' => $position++,
                    ]);
                    $colorIdMap[$normalizedName] = $productColor->id;
                    $processedIds[] = $productColor->id;

                    continue;
                }
            }

            $productColor = ProductColor::whereRaw('LOWER(name) = ?', [$normalizedName])
                ->where('product_id', $product->id)
                ->first();

            if ($productColor) {
                $productColor->update([
                    'name' => $originalName,
                    'position' => $position++,
                ]);
            } else {
                $productColor = ProductColor::create([
                    'product_id' => $product->id,
                    'name' => $originalName,
                    'position' => $position++,
                ]);
            }

            $colorIdMap[$normalizedName] = $productColor->id;
            $processedIds[] = $productColor->id;
        }

        // Delete removed colors
        $colorsToDelete = $product->colors()->whereNotIn('id', $processedIds)->get();
        foreach ($colorsToDelete as $c) {
            $c->delete(); // This cascades to variations and images
        }

        // 3. Sync Variations
        $keepIds = collect($variationsData)->pluck('id')->filter()->toArray();
        $product->variations()
            ->whereNotIn('product_variations.id', $keepIds)
            ->delete();

        foreach ($variationsData as $v) {
            $normColor = strtolower(trim($v['color']));
            $colorId = $colorIdMap[$normColor] ?? null;

            $data = [
                'product_color_id' => $colorId,
                'size' => Product::normalizeSize($v['size'] ?? null),
                'price' => $v['price'],
                'stock' => $v['stock'],
                'sku' => $v['sku'] ?? null,
            ];

            if (! empty($v['id'])) {
                ProductVariation::where('id', $v['id'])
                    ->where('product_id', $product->id)
                    ->update($data);
            } else {
                ProductVariation::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'product_color_id' => $colorId,
                        'size' => Product::normalizeSize($v['size'] ?? null),
                    ],
                    $data
                );
            }
        }
    }

    /**
     * Resolves the product_color_id for a given color string name.
     */
    public function getColorId(Product $product, string $colorName): ?int
    {
        $normalized = strtolower(trim($colorName));
        $color = $product->colors()->whereRaw('LOWER(TRIM(name)) = ?', [$normalized])->first();

        return $color ? $color->id : null;
    }
}
