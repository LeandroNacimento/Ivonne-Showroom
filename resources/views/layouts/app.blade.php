<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ivonne Showroom - Formosa</title>
    <link rel="icon" href="{{ asset('img/showroom-logo.png') }}" type="image/png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Montserrat:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans text-text-dark bg-brand-blush antialiased">
    <div class="min-h-screen flex flex-col">
        @if (!request()->routeIs('cart'))
            <livewire:mini-cart />
        @endif
        @include('layouts.navigation')

        <main class="flex-grow">
            @yield('content', $slot ?? '')
        </main>

        @include('layouts.footer')
    </div>
    <div x-data="{ show: false, count: 0 }"
        @product-added.window="
            show = true; 
            count = $event.detail.count || count; 
            setTimeout(() => show = false, 2500)
        "
        x-show="show" x-transition
        class="fixed bottom-6 right-6 bg-white border border-brand-pink/20 text-gray-800 px-5 py-4 rounded-xl shadow-luxury z-50 min-w-[300px]"
        style="display: none;">
        <div class="flex items-center mb-3">
            <span class="mr-2 text-xl">✨</span>
            <span class="font-semibold text-brand-pink">¡Producto agregado!</span>
        </div>
        <div class="text-sm text-gray-600 mb-4 flex items-center font-medium">
            <x-icon name="bag" class="w-4 h-4 mr-2 text-brand-pink" />
            <span x-text="count + (count === 1 ? ' producto en tu pedido' : ' productos en tu pedido')"></span>
        </div>
        <a href="{{ route('cart') }}"
            class="block w-full text-center bg-brand-pink hover:bg-brand-heart text-white border-none rounded-lg py-2 text-sm font-semibold transition-colors shadow-sm">
            Ver mi pedido
        </a>
    </div>

    @livewireScripts
</body>

</html>
