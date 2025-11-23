<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(10);
        return view('admin.products.index', compact('products'));
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
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'variations.*.color' => 'required|string',
            'variations.*.size' => 'required|string',
            'variations.*.stock' => 'required|integer|min:0',
        ]);

        $product = Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'category_id' => $request->category_id,
            'price' => $request->price,
            'description' => $request->description,
            'is_featured' => $request->has('is_featured'),
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                ]);
            }
        }

        if ($request->has('variations')) {
            foreach ($request->variations as $variation) {
                ProductVariation::create([
                    'product_id' => $product->id,
                    'color' => $variation['color'],
                    'size' => $variation['size'],
                    'stock' => $variation['stock'],
                ]);
            }
        }

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
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'category_id' => $request->category_id,
            'price' => $request->price,
            'description' => $request->description,
            'is_featured' => $request->has('is_featured'),
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                ]);
            }
        }

        // Handle variations update/create/delete logic is complex, for simplicity we might just re-create or handle separately.
        // For this MVP, let's assume we just add new ones or update existing if ID provided.
        // A better approach for variations in edit is often a separate component or Vue/Livewire.
        // I will stick to basic adding for now to keep it simple as requested.
        
        if ($request->has('new_variations')) {
             foreach ($request->new_variations as $variation) {
                if ($variation['color'] && $variation['size']) {
                    ProductVariation::create([
                        'product_id' => $product->id,
                        'color' => $variation['color'],
                        'size' => $variation['size'],
                        'stock' => $variation['stock'],
                    ]);
                }
            }
        }

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
}
