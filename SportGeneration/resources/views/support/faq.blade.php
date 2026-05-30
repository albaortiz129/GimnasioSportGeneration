{{-- Preguntas frecuentes. --}}
@extends('layouts.app')

@section('titulo', 'Preguntas frecuentes')

@section('contenido')
    {{-- Fondo gris. --}}
    <div class="bg-white min-h-screen py-20 font-sans">
        <div class="container mx-auto max-w-4xl px-6">

            {{-- Cabecera. --}}
            <header class="text-center mb-12">
                <h1 class="text-[#265E1F] text-5xl font-extrabold mb-6 tracking-tight leading-tight">
                    Preguntas frecuentes
                </h1>
                <p class="text-gray-600 text-[17px] font-medium max-w-2xl mx-auto leading-relaxed">
                    Encuentra respuestas rápidas a tus dudas sobre Sport Generation.
                </p>
            </header>

            {{-- Barra de búsqueda. --}}
            <div class="relative max-w-3xl mx-auto mb-16">
                <input type="text" placeholder="Buscar preguntas..."
                    class="w-full px-6 py-4.5 rounded-xl border border-gray-200 bg-white shadow-sm outline-none focus:ring-2 focus:ring-[#265E1F]/10 transition-all text-gray-700 placeholder:text-gray-400">
            </div>

            {{-- Bloque de preguntas. --}}
            <div class="space-y-4 text-left max-w-3xl mx-auto">

                <div class="faq-item bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden group active">
                    <button class="faq-trigger flex items-center justify-between w-full p-8 text-left outline-none">
                        <h3
                            class="text-[#265E1F] font-bold text-[19px] tracking-tight transition-colors group-hover:text-[#265E1F]">
                            ¿Si me inscribo, cuando puedo comenzar a entrenar?
                        </h3>
                    </button>
                    <div class="faq-content transition-all duration-300 ease-in-out group-[.active]:max-h-[500px] max-h-0">
                        <div class="px-8 pb-8 pt-0 text-gray-600 leading-relaxed text-[15px] font-medium">
                            Puedes comenzar a entrenar desde el momento en el que realices el pago de la primera cuota.
                        </div>
                    </div>
                </div>

                <div class="faq-item bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden group active">
                    <button class="faq-trigger flex items-center justify-between w-full p-8 text-left outline-none">
                        <h3
                            class="text-[#265E1F] font-bold text-[19px] tracking-tight transition-colors group-hover:text-[#265E1F]">
                            ¿Hay permanencia?
                        </h3>
                    </button>
                    <div class="faq-content transition-all duration-300 ease-in-out group-[.active]:max-h-[500px] max-h-0">
                        <div class="px-8 pb-8 pt-0 text-gray-600 leading-relaxed text-[15px] font-medium">
                            No, solamente debes abonar el mes correspondiente durante los primeros días del mes en curso.
                        </div>
                    </div>
                </div>

                <div class="faq-item bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden group active">
                    <button class="faq-trigger flex items-center justify-between w-full p-8 text-left outline-none">
                        <h3
                            class="text-[#265E1F] font-bold text-[19px] tracking-tight transition-colors group-hover:text-[#265E1F]">
                            ¿Qué pasa si no tengo experiencia?
                        </h3>
                    </button>
                    <div class="faq-content transition-all duration-300 ease-in-out group-[.active]:max-h-[500px] max-h-0">
                        <div class="px-8 pb-8 pt-0 text-gray-600 leading-relaxed text-[15px] font-medium">
                            Disponemos de un entrenador en el gimnasio al que le puedes preguntar acerca de rutinas,
                            ejercicios o técnica para ayudarte a mejorar según tu objetivo.
                        </div>
                    </div>
                </div>

                <div class="faq-item bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden group active">
                    <button class="faq-trigger flex items-center justify-between w-full p-8 text-left outline-none">
                        <h3
                            class="text-[#265E1F] font-bold text-[19px] tracking-tight transition-colors group-hover:text-[#265E1F]">
                            ¿Puedo llevar un acompañante?
                        </h3>
                    </button>
                    <div class="faq-content transition-all duration-300 ease-in-out group-[.active]:max-h-[500px] max-h-0">
                        <div class="px-8 pb-8 pt-0 text-gray-600 leading-relaxed text-[15px] font-medium">
                            Si, un viernes al mes podrás traer a un acompañante de forma totalmente gratuita para que
                            entrene contigo, pero deberás avisar al llegar.
                        </div>
                    </div>
                </div>

                <div class="faq-item bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden group active">
                    <button class="faq-trigger flex items-center justify-between w-full p-8 text-left outline-none">
                        <h3
                            class="text-[#265E1F] font-bold text-[19px] tracking-tight transition-colors group-hover:text-[#265E1F]">
                            ¿Tenéis tabla de entrenamiento?
                        </h3>
                    </button>
                    <div class="faq-content transition-all duration-300 ease-in-out group-[.active]:max-h-[500px] max-h-0">
                        <div class="px-8 pb-8 pt-0 text-gray-600 leading-relaxed text-[15px] font-medium">
                            Si, en el gimnasio dispones de una tabla de entrenamiento por grupos musculares que puedes usar.
                        </div>
                    </div>
                </div>

                <div class="faq-item bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden group active">
                    <button class="faq-trigger flex items-center justify-between w-full p-8 text-left outline-none">
                        <h3
                            class="text-[#265E1F] font-bold text-[19px] tracking-tight transition-colors group-hover:text-[#265E1F]">
                            ¿Cómo puedo cancelar o cambiar mi clase reservada?
                        </h3>
                    </button>
                    <div class="faq-content transition-all duration-300 ease-in-out group-[.active]:max-h-[500px] max-h-0">
                        <div class="px-8 pb-8 pt-0 text-gray-600 leading-relaxed text-[15px] font-medium">
                            Puedes gestionar todas tus reservas desde el "Panel de Socio" > "Mis Reservas".
                            Tienes hasta 2 horas antes del inicio de la clase para cancelar sin penalización.
                        </div>
                    </div>
                </div>

                <div class="faq-item bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden group">
                    <button class="faq-trigger flex items-center justify-between w-full p-8 text-left outline-none">
                        <h3
                            class="text-[#265E1F] font-bold text-[19px] tracking-tight transition-colors group-hover:text-[#265E1F]">
                            ¿Qué métodos de pago aceptan para la membresía?
                        </h3>
                    </button>
                    <div class="faq-content transition-all duration-300 ease-in-out group-[.active]:max-h-[500px] max-h-0">
                        <div
                            class="px-8 pb-8 pt-0 text-gray-600 leading-relaxed text-[15px] font-medium border-t border-gray-100/50 pt-6">
                            Aceptamos pago con tarjeta y pago manual en efectivo.
                        </div>
                    </div>
                </div>

                <div class="faq-item bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden group">
                    <button class="faq-trigger flex items-center justify-between w-full p-8 text-left outline-none">
                        <h3
                            class="text-[#265E1F] font-bold text-[19px] tracking-tight transition-colors group-hover:text-[#265E1F]">
                            ¿Necesito registrarme para probar una clase?
                        </h3>
                    </button>
                    <div class="faq-content transition-all duration-300 ease-in-out group-[.active]:max-h-[500px] max-h-0">
                        <div
                            class="px-8 pb-8 pt-0 text-gray-600 leading-relaxed text-[15px] font-medium border-t border-gray-100/50 pt-6">
                            Sí, para probar una clase gratuita necesitas crear una cuenta básica y solicitarla desde la
                            sección "Servicios".
                        </div>
                    </div>
                </div>

            </div>

            {{-- Contacto rápido. --}}
            <div class="mt-20 bg-[#EAF7DB] rounded-2xl py-10 px-8 border border-gray-200 max-w-3xl mx-auto text-center">
                <h4 class="text-[#265E1F] font-bold text-[18px] mb-3 tracking-tight">¿No encontraste la respuesta?</h4>
                <a href="{{ route('contacto') }}"
                    class="text-[#265E1F] font-semibold text-[15px] underline hover:text-[#265E1F]">
                    Contáctanos directamente o llámanos al 635 461 119.
                </a>
            </div>
        </div>
    </div>

    {{-- Desplegar pregunta. --}}
    <script>
        document.querySelectorAll('.faq-trigger').forEach(trigger => {
            trigger.addEventListener('click', () => {
                const parent = trigger.closest('.faq-item');
                const content = parent.querySelector('.faq-content');

                if (parent.classList.contains('active')) {
                    parent.classList.remove('active');
                    content.style.maxHeight = '0px';
                } else {
                    parent.classList.add('active');
                    content.style.maxHeight = content.scrollHeight + 'px';
                }
            });
        });

        // Primera pregunta abierta al acceder a la página.
        window.addEventListener('load', () => {
            const activeContent = document.querySelector('.faq-item.active .faq-content');
            if (activeContent) {
                activeContent.style.maxHeight = activeContent.scrollHeight + 'px';
            }
        });
    </script>
@endsection
