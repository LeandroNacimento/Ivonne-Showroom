<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class ShowroomController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::where('is_featured', true)->with('category', 'images')->take(4)->get();
        $categories = Category::all();
        return view('home', compact('featuredProducts', 'categories'));
    }

    public function catalog()
    {
        return view('catalog');
    }

    public function product($slug)
    {
        $product = Product::where('slug', $slug)->with(['category', 'colors', 'variations.productColor'])->firstOrFail();
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['colors'])
            ->take(4)
            ->get();

        // Prepare sorted variations for Alpine (business logic belongs here, not in Blade)
        $sortedVariations = $product->variations
            ->where('stock', '>', 0)
            ->sortBy(fn($v) => Product::SIZE_ORDER[strtoupper($v->size)] ?? 99)
            ->map(fn($v) => [
                'id' => $v->id,
                'color' => $v->productColor->name ?? 'Único',
                'size' => $v->size,
                'price' => $v->price,
                'stock' => $v->stock,
            ])
            ->values();

        // Group images by color for Alpine.js dynamic gallery
        $imagesByColor = [];
        foreach ($product->colors as $color) {
            if ($color->image) {
                $imagesByColor[$color->name] = [$color->image_url];
            }
        }

        // Initial active color
        $initialColor = $product->variations->where('stock', '>', 0)->first()?->productColor?->name
            ?? ($product->colors->first()?->name ?? 'Único');

        return view('product', compact('product', 'relatedProducts', 'imagesByColor', 'sortedVariations', 'initialColor'));
    }

    public function cart()
    {
        return view('cart');
    }

    public function addToCart(Request $request, CartService $cartService)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variation_id' => 'required|exists:product_variations,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $success = $cartService->addToCart($request->product_id, $request->variation_id, $request->quantity);

        if ($success) {
            return redirect()->route('cart')->with('success', '✔ Producto agregado correctamente');
        }

        return back()->with('error', 'No se pudo agregar el producto.');
    }

    public function contact()
    {
        return view('contact');
    }
}
