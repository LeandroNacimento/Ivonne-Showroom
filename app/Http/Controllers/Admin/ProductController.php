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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        return view('admin.products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request)
    {
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
                'is_featured' => $request->has('is_featured'),
            ]);

            $this->productService->syncVariations($product, $request->variations);

            $imagesData = $request->file('images');
            if (! empty($imagesData) && is_array($imagesData)) {
                $this->imageService->storeImages($product, $imagesData);
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Producto creado con éxito.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $product->load('variations', 'images');

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
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

        return redirect()->route('admin.products.index')->with('success', 'Producto actualizado con éxito.');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Producto eliminado con éxito.');
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
                'price' => $product->price,
                'variations' => $product->variations->map(function ($v) use ($product) {
                    return [
                        'id' => $v->id,
                        'color' => $v->productColor->name ?? 'N/A',
                        'size' => $v->size,
                        'stock' => $v->stock,
                        'price' => $v->price ?? $product->price,
                    ];
                })->values()->toArray(),
            ];
        });

        return response()->json($mappedProducts);
    }
}
