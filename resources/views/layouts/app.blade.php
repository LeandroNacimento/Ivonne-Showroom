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
</head>

<body class="font-sans text-text-dark bg-brand-blush antialiased">
    <div class="min-h-screen flex flex-col">
        @include('layouts.navigation')

        <main id="spa-root" class="flex-grow">
            <div class="spa-content">
                @yield('content')
            </div>
        </main>

        @include('layouts.footer')
    </div>
</body>

</html>
