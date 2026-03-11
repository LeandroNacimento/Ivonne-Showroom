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
