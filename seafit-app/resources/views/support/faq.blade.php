{{-- Vista de preguntas frecuentes con acordeon de ayuda. --}}
@extends('layouts.app')

@section('titulo', 'Centro de Ayuda - SeaFit')

@section('contenido')
    {{-- Fondo gris muy suave para que resalten las tarjetas blancas --}}
    <div class="bg-[#F4F6F8] min-h-screen py-20 font-sans">
        <div class="container mx-auto max-w-4xl px-6">

            {{-- CABECERA: Texto centrado y azul marino --}}
            <header class="text-center mb-12">
                <h1 class="text-[#102A53] text-5xl font-extrabold mb-6 tracking-tight leading-tight">
                    Centro de Ayuda y Preguntas Frecuentes
                </h1>
                <p class="text-gray-600 text-[17px] font-medium max-w-2xl mx-auto leading-relaxed">
                    Encuentra respuestas rÃ¡pidas a tus dudas sobre membresÃ­as, reservas y pagos.
                </p>
            </header>

            {{-- BARRA DE BÃšSQUEDA: Centrada, fondo blanco, bordes finos --}}
            <div class="relative max-w-3xl mx-auto mb-16">
                <input type="text" placeholder="Buscar preguntas..."
                    class="w-full px-6 py-4.5 rounded-xl border border-gray-200 bg-white shadow-sm outline-none focus:ring-2 focus:ring-[#1A3878]/10 transition-all text-gray-700 placeholder:text-gray-400">
            </div>

            {{-- CONTENEDOR --}}
            <div class="space-y-4 text-left max-w-3xl mx-auto">

                <div class="faq-item bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden group active">
                    <button class="faq-trigger flex items-center justify-between w-full p-8 text-left outline-none">
                        <h3
                            class="text-[#102A53] font-bold text-[19px] tracking-tight transition-colors group-hover:text-blue-700">
                            Â¿CÃ³mo puedo cancelar o cambiar mi clase reservada?
                        </h3>
                    </button>
                    <div class="faq-content transition-all duration-300 ease-in-out group-[.active]:max-h-[500px] max-h-0">
                        <div class="px-8 pb-8 pt-0 text-gray-600 leading-relaxed text-[15px] font-medium">
                            Puedes gestionar todas tus reservas desde el "Panel de Socio" > "Mis Reservas". Tienes hasta 2
                            horas antes del inicio de la clase para cancelar sin penalizaciÃ³n.
                        </div>
                    </div>
                </div>

                <div class="faq-item bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden group">
                    <button class="faq-trigger flex items-center justify-between w-full p-8 text-left outline-none">
                        <h3
                            class="text-[#102A53] font-bold text-[19px] tracking-tight transition-colors group-hover:text-blue-700">
                            Â¿QuÃ© mÃ©todos de pago aceptan para la membresÃ­a?
                        </h3>
                    </button>
                    <div class="faq-content transition-all duration-300 ease-in-out group-[.active]:max-h-[500px] max-h-0">
                        <div
                            class="px-8 pb-8 pt-0 text-gray-600 leading-relaxed text-[15px] font-medium border-t border-gray-100/50 pt-6">
                            Aceptamos tarjeta (Visa/Mastercard) por Stripe, y tambien pago manual por Bizum, PayPal o efectivo.
                        </div>
                    </div>
                </div>

                <div class="faq-item bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden group">
                    <button class="faq-trigger flex items-center justify-between w-full p-8 text-left outline-none">
                        <h3
                            class="text-[#102A53] font-bold text-[19px] tracking-tight transition-colors group-hover:text-blue-700">
                            Â¿Puedo congelar mi membresÃ­a si me voy de vacaciones?
                        </h3>
                    </button>
                    <div class="faq-content transition-all duration-300 ease-in-out group-[.active]:max-h-[500px] max-h-0">
                        <div
                            class="px-8 pb-8 pt-0 text-gray-600 leading-relaxed text-[15px] font-medium border-t border-gray-100/50 pt-6">
                            SÃ­, los planes Trimestrales y Anuales permiten congelar la cuenta una vez por aÃ±o.
                        </div>
                    </div>
                </div>

                <div class="faq-item bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden group">
                    <button class="faq-trigger flex items-center justify-between w-full p-8 text-left outline-none">
                        <h3
                            class="text-[#102A53] font-bold text-[19px] tracking-tight transition-colors group-hover:text-blue-700">
                            Â¿Necesito registrarme para probar una clase?
                        </h3>
                    </button>
                    <div class="faq-content transition-all duration-300 ease-in-out group-[.active]:max-h-[500px] max-h-0">
                        <div
                            class="px-8 pb-8 pt-0 text-gray-600 leading-relaxed text-[15px] font-medium border-t border-gray-100/50 pt-6">
                            SÃ­, para probar una clase gratuita es necesario crear una cuenta bÃ¡sica y solicitarla desde la
                            secciÃ³n de 'Servicios'.
                        </div>
                    </div>
                </div>

            </div>

            {{-- BLOQUE INFERIOR --}}
            <div class="mt-20 bg-[#E8EBF0] rounded-2xl py-10 px-8 border border-gray-200 max-w-3xl mx-auto text-center">
                <h4 class="text-[#102A53] font-bold text-[18px] mb-3tracking-tight">Â¿No encontraste la respuesta?</h4>
                <a href="{{ route('contacto') }}"
                    class="text-[#1A3878] font-semibold text-[15px] underline hover:text-[#0A1931]">
                    ContÃ¡ctanos directamente.
                </a>
            </div>
        </div>
    </div>

    {{-- SCRIPT PARA EL MOVIMIENTO--}}
    <script>
        document.querySelectorAll('.faq-trigger').forEach(trigger => {
            trigger.addEventListener('click', () => {
                const parent = trigger.closest('.faq-item');
                const content = parent.querySelector('.faq-content');

                // Si estÃ¡ activo, lo cerramos
                if (parent.classList.contains('active')) {
                    parent.classList.remove('active');
                    content.style.maxHeight = '0px';
                } else {
                    // Abrimos el actual calculando su altura
                    parent.classList.add('active');
                    content.style.maxHeight = content.scrollHeight + 'px';
                }
            });
        });

        // Ajuste para que la primera pregunta (abierta por defecto) se vea bien al cargar
        window.addEventListener('load', () => {
            const activeContent = document.querySelector('.faq-item.active .faq-content');
            if (activeContent) {
                activeContent.style.maxHeight = activeContent.scrollHeight + 'px';
            }
        });
    </script>
@endsection
