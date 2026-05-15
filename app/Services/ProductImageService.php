<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
     * @param  Product  $product  The base product instance
     * @param  array  $imagesData  Uploaded files grouped by color name: ['Color Name' => [UploadedFile...]]
     */
    public function storeImages(Product $product, array $imagesData): void
    {
        foreach ($imagesData as $colorName => $files) {
            $normalizedColorName = trim((string) $colorName);

            if ($normalizedColorName === '') {
                throw ValidationException::withMessages([
                    'images' => 'Las imagenes deben estar asociadas a un color valido.',
                ]);
            }

            $colorId = $this->productService->getColorId($product, $colorName);

            if ($colorId === null) {
                throw ValidationException::withMessages([
                    'images' => "No se pudo asociar las imagenes al color '{$normalizedColorName}'. Guarda el color visible antes de subir imagenes para ese grupo.",
                ]);
            }

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
