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

class ProductController extends Controller
{
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

            // Images grouped by color: images[ColorName][] = file
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $color => $files) {
                    foreach ($files as $position => $file) {
                        $path = $file->store('products', 'public');
                        ProductImage::create([
                            'product_id' => $product->id,
                            'path' => $path,
                            'color' => $color,
                            'position' => $position,
                        ]);
                    }
                }
            }

            // Variations
            foreach ($request->variations as $variation) {
                ProductVariation::create([
                    'product_id' => $product->id,
                    'color' => $variation['color'],
                    'size' => $variation['size'],
                    'price' => $variation['price'],
                    'stock' => $variation['stock'],
                    'sku' => $variation['sku'] ?? null,
                ]);
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

            // New images grouped by color: images[ColorName][] = file
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $color => $files) {
                    // Position continues from existing images of this color
                    $maxPos = $product->images()
                        ->where('color', $color)
                        ->max('position') ?? -1;

                    foreach ($files as $file) {
                        $maxPos++;
                        $path = $file->store('products', 'public');
                        ProductImage::create([
                            'product_id' => $product->id,
                            'path' => $path,
                            'color' => $color,
                            'position' => $maxPos,
                        ]);
                    }
                }
            }

            // Delete removed images
            if ($request->has('delete_images')) {
                $imagesToDelete = ProductImage::whereIn('id', $request->delete_images)
                    ->where('product_id', $product->id)
                    ->get();

                foreach ($imagesToDelete as $img) {
                    Storage::disk('public')->delete($img->path);
                    $img->delete();
                }
            }

            // Sync variations: delete old, then upsert
            $keepIds = collect($request->variations)
                ->pluck('id')
                ->filter()
                ->toArray();

            // Delete variations not in the submitted list
            $product->variations()->whereNotIn('id', $keepIds)->delete();

            // Upsert each variation (update existing first, then create new)
            foreach ($request->variations as $v) {
                $data = [
                    'color' => $v['color'],
                    'size' => $v['size'],
                    'price' => $v['price'],
                    'stock' => $v['stock'],
                    'sku' => $v['sku'] ?? null,
                ];

                if (!empty($v['id'])) {
                    ProductVariation::where('id', $v['id'])
                        ->where('product_id', $product->id)
                        ->update($data);
                } else {
                    // Use updateOrCreate to handle duplicates gracefully
                    ProductVariation::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'color' => $v['color'],
                            'size' => $v['size'],
                        ],
                        $data
                    );
                }
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
