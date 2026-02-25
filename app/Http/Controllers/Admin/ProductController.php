<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\ProductService;
use App\Services\ProductImageService;

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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|array',
            'images.*.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'variations' => 'required|array|min:1',
            'variations.*.color' => 'required|string',
            'variations.*.size' => 'required|string',
            'variations.*.price' => 'required|numeric|min:0',
            'variations.*.stock' => 'required|integer|min:0',
            'variations.*.sku' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($request) {
            $product = Product::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'category_id' => $request->category_id,
                'description' => $request->description,
                'is_featured' => $request->has('is_featured'),
            ]);

            $this->productService->syncVariations($product, $request->variations);

            $imagesData = $request->file('images');
            if (!empty($imagesData) && is_array($imagesData)) {
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

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|array',
            'images.*.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'variations' => 'required|array|min:1',
            'variations.*.id' => 'nullable|integer',
            'variations.*.color' => 'required|string',
            'variations.*.size' => 'required|string',
            'variations.*.price' => 'required|numeric|min:0',
            'variations.*.stock' => 'required|integer|min:0',
            'variations.*.sku' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($request, $product) {
            $product->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'category_id' => $request->category_id,
                'description' => $request->description,
                'is_featured' => $request->has('is_featured'),
            ]);

            $this->productService->syncVariations($product, $request->variations);

            if ($request->has('delete_images')) {
                $this->imageService->deleteImages($product, $request->delete_images);
            }

            $imagesData = $request->file('images');
            if (!empty($imagesData) && is_array($imagesData)) {
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
        $query = $request->get('q');

        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('id', 'like', "%{$query}%")
            ->with([
                'variations' => function ($q) {
                    $q->where('stock', '>', 0);
                }
            ])
            ->limit(20)
            ->get();

        return response()->json($products);
    }
}
