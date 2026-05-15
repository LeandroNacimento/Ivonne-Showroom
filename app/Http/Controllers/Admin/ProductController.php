<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductImageService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    protected ProductService $productService;

    protected ProductImageService $imageService;

    public function __construct(ProductService $productService, ProductImageService $imageService)
    {
        $this->productService = $productService;
        $this->imageService = $imageService;
    }

    public function index()
    {
        return view('admin.products.index');
    }

    public function create()
    {
        $categories = Category::all();
        $sizeTypeOptions = Product::sizeTypeOptions();

        return view('admin.products.create', compact('categories', 'sizeTypeOptions'));
    }

    public function store(StoreProductRequest $request)
    {
        try {
            $validated = $request->validated();

            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $i = 1;
            while (Product::withTrashed()->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$i++;
            }

            DB::transaction(function () use ($request, $validated, $slug) {
                $product = Product::create([
                    'name' => $validated['name'],
                    'slug' => $slug,
                    'category_id' => $validated['category_id'],
                    'description' => $validated['description'] ?? null,
                    'size_type' => $validated['size_type'],
                    'is_featured' => $request->has('is_featured'),
                ]);

                $this->productService->syncVariations($product, $request->variations);

                $imagesData = $request->file('images');
                if (! empty($imagesData) && is_array($imagesData)) {
                    $this->imageService->storeImages($product, $imagesData);
                }
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Product create failed.', [
                'product_name' => $request->input('name'),
                'admin_user_id' => $request->user()?->id,
                'exception' => $exception,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo guardar el producto. Revisa las variaciones y las imagenes asociadas antes de volver a intentar.');
        }

        return redirect()->route('admin.products.index')->with('success', 'Producto creado con exito.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $product->load('variations', 'images');
        $sizeTypeOptions = Product::sizeTypeOptions();

        return view('admin.products.edit', compact('product', 'categories', 'sizeTypeOptions'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $validated = $request->validated();

            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $i = 1;
            while (Product::withTrashed()->where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $baseSlug.'-'.$i++;
            }

            DB::transaction(function () use ($request, $product, $validated, $slug) {
                $product->update([
                    'name' => $validated['name'],
                    'slug' => $slug,
                    'category_id' => $validated['category_id'],
                    'description' => $validated['description'] ?? null,
                    'size_type' => $validated['size_type'],
                    'is_featured' => $request->has('is_featured'),
                ]);

                $this->productService->syncVariations($product, $request->variations);

                if ($request->has('delete_images')) {
                    $this->imageService->deleteImages($product, $request->delete_images);
                }

                $imagesData = $request->file('images');
                if (! empty($imagesData) && is_array($imagesData)) {
                    $this->imageService->storeImages($product, $imagesData);
                }
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Product update failed.', [
                'product_id' => $product->id,
                'admin_user_id' => $request->user()?->id,
                'exception' => $exception,
            ]);

            return back()
                ->withInput()
                ->with('error', 'No se pudo actualizar el producto. Revisa las variaciones y las imagenes asociadas antes de volver a intentar.');
        }

        return redirect()->route('admin.products.index')->with('success', 'Producto actualizado con exito.');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Producto eliminado con exito.');
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:1', 'max:50'],
        ]);

        $query = $validated['q'];

        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('id', 'like', "%{$query}%")
            ->with([
                'variations' => function ($q) {
                    $q->where('stock', '>', 0);
                },
                'variations.productColor',
            ])
            ->limit(20)
            ->get();

        $mappedProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'variations' => $product->variations->map(function ($v) {
                    return [
                        'id' => $v->id,
                        'color' => $v->productColor->name ?? 'N/A',
                        'size' => $v->size,
                        'stock' => $v->stock,
                        'effective_price' => (float) $v->effective_price,
                        'original_price' => (float) $v->original_price,
                        'has_active_offer' => $v->has_active_offer,
                    ];
                })->values()->toArray(),
            ];
        });

        return response()->json($mappedProducts);
    }
}
