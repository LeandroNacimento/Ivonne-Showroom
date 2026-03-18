<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin - Ivonne Showroom</title>
    <link rel="icon" href="{{ asset('img/showroom-logo.png') }}" type="image/png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="h-full font-sans text-gray-900 antialiased" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/80 z-40 lg:hidden"
        @click="sidebarOpen = false" x-cloak></div>

    <!-- Mobile Sidebar -->
    <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full" class="fixed inset-0 z-50 flex w-72 lg:hidden" x-cloak>

        <div class="relative flex w-full max-w-xs flex-1 flex-col bg-white pt-5 pb-4 shadow-xl">
            <div class="absolute top-0 right-0 -mr-12 pt-2">
                <button type="button"
                    class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                    @click="sidebarOpen = false">
                    <span class="sr-only">Close sidebar</span>
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex flex-shrink-0 items-center px-4">
                <img class="h-10 w-auto" src="{{ asset('img/showroom-logo.png') }}" alt="Ivonne Showroom">
            </div>
            <nav class="mt-5 h-full overflow-y-auto px-4 space-y-1">
                @include('layouts.partials.admin-nav')
            </nav>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <div
        class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-72 lg:flex-col lg:border-r lg:border-gray-200 lg:bg-white lg:shadow-sm">
        <div class="flex flex-col flex-grow overflow-y-auto pt-5 pb-4">
            <div class="flex flex-shrink-0 items-center px-6 mb-6">
                <img class="h-12 w-auto" src="{{ asset('img/showroom-logo.png') }}" alt="Ivonne Showroom">
            </div>
            <nav class="mt-2 flex-1 space-y-1 px-4">
                @include('layouts.partials.admin-nav')
            </nav>
        </div>
        <div class="flex flex-shrink-0 border-t border-gray-200 p-4">
            <form method="POST" action="{{ route('admin.logout') }}" class="w-full">
                @csrf
                <button type="submit"
                    class="group flex w-full items-center gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors">
                    <svg class="h-6 w-6 shrink-0 text-gray-400 group-hover:text-red-600" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                    </svg>
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex flex-1 flex-col lg:pl-72 h-full">
        <!-- Top Sticky Bar -->
        <div class="sticky top-0 z-10 flex h-16 flex-shrink-0 bg-white shadow-sm border-b border-gray-200">
            <button type="button"
                class="px-4 border-r border-gray-200 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-pink lg:hidden"
                @click="sidebarOpen = true">
                <span class="sr-only">Open sidebar</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <div class="flex flex-1 justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex flex-1 items-center">
                    <h2 class="text-lg font-semibold text-gray-800">Panel de Administración</h2>
                </div>
                <div class="ml-4 flex items-center md:ml-6">
                    <div class="flex items-center gap-3">
                        <span
                            class="hidden md:block text-sm font-medium text-gray-700">{{ Auth::user()->name ?? 'Admin' }}</span>
                        <div
                            class="h-8 w-8 rounded-full bg-brand-blush flex items-center justify-center text-brand-pink font-bold border border-brand-pink/20">
                            {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <main class="flex-1 py-8">
            <div class="px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                        class="mb-6 rounded-md bg-green-50 p-4 border border-green-200">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @yield('content')
                {{ $slot ?? '' }}
            </div>
        </main>
    </div>

    @livewireScripts
</body>

</html>
