{{-- Layout base compartido por las páginas. --}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Sport Generation')</title>

    {{-- Token de seguridad para formularios y peticiones --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Librerías visuales --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;700;900&display=swap" rel="stylesheet" />

    {{-- Archivo JS principal --}}
    @viteReactRefresh
    @vite(['resources/js/app.jsx'])

    {{-- Estilos adicionales --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ time() }}">
</head>

<body class="flex flex-col min-h-screen overflow-x-hidden">
    {{-- Cabecera --}}
    @include('components.header')

    {{-- Contenido --}}
    <main class="flex-grow">
        @yield('contenido')
    </main>

    {{-- Pie de página --}}
    @include('components.footer')

    {{-- Chat IA --}}
    @include('components.ai-chat')
</body>

</html>

