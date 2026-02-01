@extends('layouts.app')

@section('content')
    <div class="bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="lg:grid lg:grid-cols-2 lg:gap-x-8 lg:items-start">
                <!-- Image gallery -->
                <div class="flex flex-col-reverse">
                    <div
                        class="w-full aspect-w-1 aspect-h-1 bg-gray-200 rounded-lg overflow-hidden sm:aspect-w-2 sm:aspect-h-3 flex items-center justify-center">
                        <img src="{{ $product->cover_url }}" alt="{{ $product->name }}"
                            class="w-full h-full object-center object-cover">
                    </div>
                    <!-- Thumbnails could go here -->
                </div>

                <!-- Product info -->
                <div class="mt-10 px-4 sm:px-0 sm:mt-16 lg:mt-0">
                    <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">{{ $product->name }}</h1>

                    <div class="mt-3">
                        <h2 class="sr-only">Información del producto</h2>
                        <p class="text-3xl text-gray-900">${{ number_format($product->price, 0, ',', '.') }}</p>
                    </div>

                    <div class="mt-6">
                        <h3 class="sr-only">Descripción</h3>
                        <div class="text-base text-gray-700 space-y-6">
                            <p>{{ $product->description }}</p>
                        </div>
                    </div>

                    <div class="mt-8">
                        @if ($product->variations->count() > 0)
                            <form action="{{ route('cart.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">

                                <div class="mb-6">
                                    <h3 class="text-sm text-gray-900 font-medium mb-2">Selecciona una variante:</h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        @foreach ($product->variations as $variation)
                                            <label
                                                class="relative border rounded-lg p-4 flex cursor-pointer focus:outline-none">
                                                <input type="radio" name="variation_id" value="{{ $variation->id }}"
                                                    class="sr-only" aria-labelledby="variation-label-{{ $variation->id }}"
                                                    required>
                                                <div class="flex items-center justify-between w-full">
                                                    <div class="flex items-center">
                                                        <div class="text-sm">
                                                            <p id="variation-label-{{ $variation->id }}"
                                                                class="font-medium text-gray-900">
                                                                {{ $variation->color }} - {{ $variation->size }}
                                                            </p>
                                                            <p class="text-gray-500">
                                                                Stock: {{ $variation->stock }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4 flex-shrink-0 text-brand-pink"
                                                        id="check-icon-{{ $variation->id }}" style="display: none;">
                                                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="flex items-center mb-6">
                                    <label for="quantity" class="mr-4 text-sm font-medium text-gray-700">Cantidad:</label>
                                    <input type="number" name="quantity" id="quantity" value="1" min="1"
                                        class="w-20 rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50 text-center">
                                </div>

                                <button type="submit"
                                    class="w-full bg-brand-pink border border-transparent rounded-md py-3 px-8 flex items-center justify-center text-base font-medium text-white hover:bg-brand-heart focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-50 focus:ring-brand-pink transition-colors">
                                    Agregar al Carrito
                                </button>
                            </form>
                        @else
                            <p class="text-red-500 font-medium">No hay stock disponible por el momento.</p>
                        @endif

                        <div class="mt-4 text-sm text-gray-500 text-center">
                            <p>Coordinamos el pago y envío directamente por chat.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            @if ($relatedProducts->count() > 0)
                <div class="mt-16 border-t border-gray-200 pt-10">
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 font-script text-brand-pink mb-6">También te
                        puede interesar</h2>
                    <div class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-4 xl:gap-x-8">
                        @foreach ($relatedProducts as $related)
                            <div class="group relative">
                                <div
                                    class="w-full min-h-80 bg-gray-200 aspect-w-1 aspect-h-1 rounded-md overflow-hidden group-hover:opacity-75 lg:h-80 lg:aspect-none flex items-center justify-center">
                                    @if ($related->images->first())
                                        <img src="{{ asset('storage/' . $related->images->first()->path) }}"
                                            alt="{{ $related->name }}" class="w-full h-full object-center object-cover">
                                    @else
                                        <span class="text-gray-400">Imagen</span>
                                    @endif
                                </div>
                                <div class="mt-4 flex justify-between">
                                    <div>
                                        <h3 class="text-sm text-gray-700">
                                            <a href="{{ route('product.show', $related->slug) }}">
                                                <span aria-hidden="true" class="absolute inset-0"></span>
                                                {{ $related->name }}
                                            </a>
                                        </h3>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">
                                        ${{ number_format($related->price, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        // Simple script to handle radio button styling
        const radios = document.querySelectorAll('input[type="radio"][name="variation_id"]');
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                // Reset all styles
                document.querySelectorAll('label').forEach(label => {
                    label.classList.remove('ring-2', 'ring-brand-pink', 'border-brand-pink');
                    const icon = label.querySelector('[id^="check-icon-"]');
                    if (icon) icon.style.display = 'none';
                });

                // Add style to selected
                if (this.checked) {
                    const label = this.closest('label');
                    label.classList.add('ring-2', 'ring-brand-pink', 'border-brand-pink');
                    const icon = label.querySelector('[id^="check-icon-"]');
                    if (icon) icon.style.display = 'block';
                }
            });
        });
    </script>
@endsection
