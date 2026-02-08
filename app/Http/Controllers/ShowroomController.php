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

    public function catalog(Request $request)
    {
        $query = Product::with('category', 'images', 'variations');

        // Filter by Category
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by Sizes (Combinable)
        if ($request->filled('sizes')) {
            $sizes = is_array($request->sizes) ? $request->sizes : [$request->sizes];
            $query->whereHas('variations', function ($q) use ($sizes) {
                $q->whereIn('size', $sizes)->where('stock', '>', 0);
            });
        }

        // Filter by Colors (Combinable)
        if ($request->filled('colors')) {
            $colors = is_array($request->colors) ? $request->colors : [$request->colors];
            $query->whereHas('variations', function ($q) use ($colors) {
                $q->whereIn('color', $colors)->where('stock', '>', 0);
            });
        }

        // Sorting
        if ($request->has('sort')) {
            if ($request->sort == 'price_asc') {
                $query->orderBy('price', 'asc')->orderBy('id', 'desc');
            } elseif ($request->sort == 'price_desc') {
                $query->orderBy('price', 'desc')->orderBy('id', 'desc');
            } else {
                $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        }

        $products = $query->paginate(12);

        // Data for Sidebar (Context-aware)
        $categories = Category::withCount('products')->get();

        $baseVarQuery = \App\Models\ProductVariation::where('stock', '>', 0);

        if ($request->filled('category')) {
            $baseVarQuery->whereHas('product.category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        $availableSizes = (clone $baseVarQuery)->distinct()->orderBy('size')->pluck('size');
        $availableColors = (clone $baseVarQuery)->distinct()->orderBy('color')->pluck('color');

        return view('catalog', compact('products', 'categories', 'availableSizes', 'availableColors'));
    }

    public function product($slug)
    {
        $product = Product::where('slug', $slug)->with('category', 'images', 'variations')->firstOrFail();
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return view('product', compact('product', 'relatedProducts'));
    }

    public function cart(CartService $cartService)
    {
        $cart = $cartService->getCart();
        $total = $cartService->getTotal();
        $whatsappMessage = $cartService->getWhatsAppMessage();
        return view('cart', compact('cart', 'total', 'whatsappMessage'));
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
            return redirect()->route('cart')->with('success', 'Producto agregado al carrito.');
        }

        return back()->with('error', 'No se pudo agregar el producto.');
    }

    public function removeFromCart(Request $request, CartService $cartService)
    {
        $request->validate([
            'cart_key' => 'required|string',
        ]);

        $cartService->removeFromCart($request->cart_key);
        return back()->with('success', 'Producto eliminado del carrito.');
    }

    public function updateCart(Request $request, CartService $cartService)
    {
        $request->validate([
            'cart_key' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $cartService->updateQuantity($request->cart_key, $request->quantity);
        return back()->with('success', 'Carrito actualizado.');
    }

    public function contact()
    {
        return view('contact');
    }
}
