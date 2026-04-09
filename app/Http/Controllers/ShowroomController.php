<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use App\Services\HomeHeroService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ShowroomController extends Controller
{
    public function index(HomeHeroService $homeHeroService)
    {
        $featuredProducts = Product::where('is_featured', true)
            ->with([
                'category',
                'colors' => function ($query) {
                    $query->select('product_colors.id', 'product_colors.product_id', 'product_colors.name', 'product_colors.image', 'product_colors.position')
                        ->with('images:id,product_color_id,path,position');
                },
            ])
            ->take(4)
            ->get();
        $categories = Category::all();
        $homeHero = $homeHeroService->getRenderableHero();
        $homeHeroSlides = $homeHero?->activeSlides ?? collect();
        $homeHeroMode = $this->resolveHomeHeroMode($homeHeroSlides);

        return view('home', compact('featuredProducts', 'categories', 'homeHero', 'homeHeroSlides', 'homeHeroMode'));
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

    private function resolveHomeHeroMode(Collection $slides): string
    {
        return match (true) {
            $slides->count() >= 2 => 'carousel',
            $slides->count() === 1 => 'static',
            default => 'fallback',
        };
    }
}
