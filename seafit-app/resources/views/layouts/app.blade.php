{{-- Layout base compartido por las páginas de SeaFit. --}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'SeaFit')</title>

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

    <script>
        (() => {
            /**
             * Corrige el hueco superior que algunas extensiones/traductores
             * pueden inyectar. No modifica tamaños de logo ni botones.
             */
            function fixTopGap() {
                const header = document.querySelector('body > header');
                if (!header) return;

                header.style.removeProperty('margin-top');
                const topGap = Math.round(header.getBoundingClientRect().top);

                if (topGap > 0) {
                    header.style.setProperty('margin-top', `-${topGap}px`, 'important');
                } else {
                    header.style.setProperty('margin-top', '0px', 'important');
                }
            }

            document.addEventListener('DOMContentLoaded', fixTopGap);
            window.addEventListener('load', fixTopGap);
            window.addEventListener('resize', fixTopGap);

            // Revisiones tardías por si el navegador inyecta cambios después.
            setTimeout(fixTopGap, 250);
            setTimeout(fixTopGap, 1000);
        })();
    </script>
</head>

<body class="flex flex-col min-h-screen">
    {{-- Header --}}
    @include('components.header')

    {{-- Contenido --}}
    <main class="flex-grow">
        @yield('contenido')
    </main>

    {{-- Footer --}}
    @include('components.footer')

    {{-- Chat IA --}}
    @include('components.ai-chat')
</body>

</html>
