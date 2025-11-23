@extends('layouts.app')

@section('content')
<div class="bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-3xl font-bold text-gray-900 font-script text-brand-pink mb-8">Tu Carrito</h1>
        
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(empty($cart))
            <div class="bg-brand-blush rounded-lg p-8 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-brand-pink mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <p class="text-lg text-gray-700 mb-4">
                    Tu carrito está vacío.
                </p>
                <a href="{{ route('catalog') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-brand-pink hover:bg-brand-heart transition-colors">
                    Ver Catálogo
                </a>
            </div>
        @else
            <div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-200">
                <ul class="divide-y divide-gray-200">
                    @foreach($cart as $key => $item)
                    <li class="p-4 sm:p-6 flex items-center">
                        <div class="flex-shrink-0 h-16 w-16 rounded-md overflow-hidden bg-gray-100">
                            @if($item['image'])
                                <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover">
                            @else
                                <span class="flex items-center justify-center h-full w-full text-gray-400 text-xs">Sin imagen</span>
                            @endif
                        </div>
                        <div class="ml-4 flex-1 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900"><a href="{{ route('product.show', \Illuminate\Support\Str::slug($item['name'])) }}">{{ $item['name'] }}</a></h3>
                                <p class="text-sm text-gray-500">{{ $item['color'] }} - {{ $item['size'] }}</p>
                                <p class="text-sm font-medium text-gray-900 mt-1">${{ number_format($item['price'], 0, ',', '.') }}</p>
                            </div>
                            <div class="mt-4 sm:mt-0 flex items-center gap-4">
                                <form action="{{ route('cart.update') }}" method="POST" class="flex items-center">
                                    @csrf
                                    <input type="hidden" name="cart_key" value="{{ $key }}">
                                    <label for="quantity-{{ $key }}" class="sr-only">Cantidad</label>
                                    <input type="number" id="quantity-{{ $key }}" name="quantity" value="{{ $item['quantity'] }}" min="1" class="w-16 rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50 text-center" onchange="this.form.submit()">
                                </form>
                                <form action="{{ route('cart.remove') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="cart_key" value="{{ $key }}">
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                <div class="bg-gray-50 px-4 py-6 sm:px-6 flex justify-between items-center">
                    <div class="text-base font-medium text-gray-900">Total</div>
                    <div class="text-xl font-bold text-brand-pink">${{ number_format($total, 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <a href="https://wa.me/5493704550445?text={{ $whatsappMessage }}" target="_blank" class="inline-flex items-center px-8 py-4 border border-transparent text-lg font-medium rounded-md text-white bg-green-500 hover:bg-green-600 shadow-md transition-colors">
                    <svg class="h-6 w-6 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.463 1.065 2.876 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                    Enviar Pedido por WhatsApp
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
