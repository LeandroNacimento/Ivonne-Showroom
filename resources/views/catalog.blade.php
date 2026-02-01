@extends('layouts.app')

@section('content')
    <div class="bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 font-script text-brand-pink">Catálogo</h1>

                <!-- Filters / Sort -->
                <div class="flex gap-4 mt-4 md:mt-0">
                    <form action="{{ route('catalog') }}" method="GET" class="flex gap-2">
                        <select name="category"
                            class="catalog-filter rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                            <option value="">Todas las categorías</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->slug }}"
                                    {{ request('category') == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>

                        <select name="sort"
                            class="catalog-filter rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Más nuevos</option>
                            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Menor precio
                            </option>
                            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Mayor precio
                            </option>
                        </select>
                    </form>
                </div>
            </div>

            <div id="catalog-products"
                class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8">
                @forelse($products as $product)
                    <div
                        class="group relative bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100 hover:shadow-md transition-shadow">
                        <div
                            class="w-full min-h-80 bg-gray-200 aspect-w-1 aspect-h-1 rounded-t-lg overflow-hidden group-hover:opacity-75 lg:h-80 lg:aspect-none flex items-center justify-center">
                            <img src="{{ $product->cover_url }}" alt="{{ $product->name }}"
                                class="w-full h-full object-center object-cover">
                        </div>
                        <div class="mt-4 flex justify-between px-4 pb-4">
                            <div>
                                <h3 class="text-sm text-gray-700">
                                    <a href="{{ route('product.show', $product->slug) }}">
                                        <span aria-hidden="true" class="absolute inset-0"></span>
                                        {{ $product->name }}
                                    </a>
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">{{ $product->category->name }}</p>
                            </div>
                            <p class="text-sm font-medium text-gray-900">${{ number_format($product->price, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <p class="text-gray-500 text-lg">No se encontraron productos con estos filtros.</p>
                        <a href="{{ route('catalog') }}" class="text-brand-pink hover:underline mt-2 inline-block">Limpiar
                            filtros</a>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $products->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
