<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin - Ivonne Showroom</title>
    <link rel="icon" href="{{ asset('img/showroom-logo.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body
    class="font-sans antialiased min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 via-white to-pink-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl ring-1 ring-gray-900/5">
        <div class="flex flex-col items-center">
            <img class="h-24 w-auto hover:scale-105 transition-transform duration-300 ease-out"
                src="{{ asset('img/showroom-logo.png') }}" alt="Ivonne Showroom">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 tracking-tight font-sans">Acceso
                Administrativo</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Ingresa tus credenciales para continuar
            </p>
        </div>

        <form class="mt-8 space-y-6" action="{{ route('admin.login.submit') }}" method="POST">
            @csrf
            <div class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required
                            class="appearance-none block w-full px-4 py-3 border border-gray-200 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-pink focus:border-transparent transition-all duration-200 sm:text-sm bg-gray-50 focus:bg-white"
                            placeholder="admin@ivonneshowroom.com">
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="appearance-none block w-full px-4 py-3 border border-gray-200 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-pink focus:border-transparent transition-all duration-200 sm:text-sm bg-gray-50 focus:bg-white"
                            placeholder="••••••••">
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-lg text-white bg-brand-pink hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-pink transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-pink-300 group-hover:text-pink-200 transition-colors"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                            aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                    Ingresar
                </button>
            </div>
        </form>
    </div>
</body>

</html>
