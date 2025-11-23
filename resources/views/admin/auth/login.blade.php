<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin - Ivonne Showroom</title>
    <link rel="icon" href="{{ asset('img/Logo.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 bg-brand-blush antialiased flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
        <div class="flex justify-center mb-6">
            <img src="{{ asset('img/Logo.png') }}" alt="Ivonne Showroom" class="h-24 w-auto">
        </div>
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Acceso Administrativo</h2>
        
        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                <input type="email" name="email" id="email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required autofocus>
                @error('email')
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <input type="password" name="password" id="password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required>
                @error('password')
                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>
            <button type="submit" class="w-full bg-brand-pink text-white font-bold py-2 px-4 rounded-md hover:bg-brand-heart transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-pink">
                Ingresar
            </button>
        </form>
    </div>
</body>
</html>
