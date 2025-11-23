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
        $query = Product::with('category', 'images');

        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->has('sort')) {
            if ($request->sort == 'price_asc') {
                $query->orderBy('price', 'asc');
            } elseif ($request->sort == 'price_desc') {
                $query->orderBy('price', 'desc');
            } else {
                $query->latest();
            }
        } else {
            $query->latest();
        }

        $products = $query->paginate(12);
        $categories = Category::all();

        return view('catalog', compact('products', 'categories'));
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
        $cartService->removeFromCart($request->cart_key);
        return back()->with('success', 'Producto eliminado del carrito.');
    }

    public function updateCart(Request $request, CartService $cartService)
    {
        $cartService->updateQuantity($request->cart_key, $request->quantity);
        return back()->with('success', 'Carrito actualizado.');
    }

    public function contact()
    {
        return view('contact');
    }

    public function about()
    {
        return view('about');
    }
}
