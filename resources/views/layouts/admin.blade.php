<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Ivonne Showroom</title>
    <link rel="icon" href="{{ asset('img/Logo.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 bg-gray-100 antialiased">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-md min-h-screen flex flex-col">
            <div class="p-6 flex items-center justify-center border-b border-gray-100">
                <img src="{{ asset('img/Logo.png') }}" alt="Ivonne Showroom" class="h-12 w-auto">
            </div>
            <nav class="flex-grow p-4 space-y-2">
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded-md text-gray-600 hover:bg-brand-blush hover:text-brand-pink transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-brand-blush text-brand-pink font-medium' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.categories.index') }}" class="block px-4 py-2 rounded-md text-gray-600 hover:bg-brand-blush hover:text-brand-pink transition-colors {{ request()->routeIs('admin.categories.*') ? 'bg-brand-blush text-brand-pink font-medium' : '' }}">
                    Categorías
                </a>
                <a href="{{ route('admin.products.index') }}" class="block px-4 py-2 rounded-md text-gray-600 hover:bg-brand-blush hover:text-brand-pink transition-colors {{ request()->routeIs('admin.products.*') ? 'bg-brand-blush text-brand-pink font-medium' : '' }}">
                    Productos
                </a>
                <a href="{{ route('home') }}" target="_blank" class="block px-4 py-2 rounded-md text-gray-600 hover:bg-brand-blush hover:text-brand-pink transition-colors">
                    Ver Sitio Web
                </a>
            </nav>
            <div class="p-4 border-t border-gray-100">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-red-500 hover:bg-red-50 rounded-md transition-colors">
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow p-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</body>
</html>
