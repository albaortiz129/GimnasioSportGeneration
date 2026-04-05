{{-- Layout base compartido por las paginas de SeaFit. --}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'SeaFit')</title>

    {{-- Token de seguridad para formularios y peticiones JS --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Librerias visuales globales --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;700;900&display=swap" rel="stylesheet" />

    {{-- Archivo JS principal del proyecto (incluye el registro en React) --}}
    @viteReactRefresh
    @vite(['resources/js/app.jsx'])

    {{-- Estilos globales adicionales del proyecto --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ time() }}">
</head>

<body class="flex flex-col min-h-screen">
    {{-- Header comun para todo el sitio --}}
    @include('componentes.header')

    {{-- Contenido de cada vista hija --}}
    <main class="flex-grow">
        @yield('contenido')
    </main>

    {{-- Footer comun para todo el sitio --}}
    @include('componentes.footer')
</body>

</html>