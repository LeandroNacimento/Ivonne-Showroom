<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Layout</title>
    @livewireStyles
</head>
<body>
    <h1>Modo de Prueba Aislado</h1>
    <p>Si este buscador funciona, el problema está en tu diseño 'admin'.</p>

    {{ $slot ?? '' }}
    @yield('content')

    @livewireScripts
</body>
</html>
