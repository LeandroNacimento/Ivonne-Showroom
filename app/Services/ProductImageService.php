<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;

class ProductImageService
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Handles associating newly uploaded images to the correct product and product_color.
     * 
     * @param Product $product The base product instance
     * @param array $imagesData Uploaded files grouped by color name: ['Color Name' => [UploadedFile...]]
     */
    public function storeImages(Product $product, array $imagesData): void
    {
        foreach ($imagesData as $colorName => $files) {
            $colorId = $this->productService->getColorId($product, $colorName);

            // Get starting position for this color
            $maxPos = $product->images()
                ->where('product_images.product_color_id', $colorId)
                ->max('product_images.position') ?? -1;

            foreach ($files as $file) {
                $maxPos++;
                $path = $file->store('products', 'public');

                ProductImage::create([
                    'product_color_id' => $colorId,
                    'path' => $path,
                    'position' => $maxPos,
                ]);
            }
        }
    }

    /**
     * Deletes specified images from storage and database.
     */
    public function deleteImages(Product $product, array $imageIds): void
    {
        /** @var \App\Models\ProductImage[] $imagesToDelete */
        $imagesToDelete = ProductImage::whereIn('id', $imageIds)
            ->whereHas('productColor', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })
            ->get();

        foreach ($imagesToDelete as $img) {
            Storage::disk('public')->delete($img->path);
            $img->delete();
        }
    }
}
