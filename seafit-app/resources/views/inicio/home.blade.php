@extends('moldes.inicio')

@section('titulo', 'SeaFit - Tu gimnasio online')

@section('contenido')

    <div class="w-full">
        {{-- 1. Banner Principal (Hero) --}}
        {{-- Se adapta el padding lateral: 5 en móvil, 15% en pantallas grandes --}}
        <section class="h-[450px] bg-cover bg-center flex items-center justify-center text-center px-5 md:px-[15%]"
            style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('{{ asset('imagenes/banner.jpg') }}');">
            <div class="max-w-[900px]">
                <h1 class="text-white text-3xl md:text-[42px] font-extrabold leading-[1.2] drop-shadow-md m-0">
                    Olvídate de colas y llamadas. Accede a todo nuestro catálogo al instante.
                </h1>
                
                {{-- Botón sustituido 100% a Tailwind --}}
                <a href="{{ url('/registro') }}" class="inline-block mt-8 bg-[#1A3878] text-white py-3 px-8 rounded-xl font-bold text-[16px] transition-transform duration-300 hover:scale-105 shadow-lg">
                    Empezar ahora
                </a>
            </div>
        </section>

        {{-- 2. Título de Introducción --}}
        <section class="text-center pt-[60px] pb-5 px-5">
            <h2 class="text-[32px] text-[#051221] mb-2.5 font-bold m-0">¿Listo?</h2>
            <p class="text-gray-600 text-lg m-0">Todo lo que necesitas para empezar</p>
        </section>

        {{-- 3. Cuadrícula de Servicios --}}
        <section class="px-5 py-12 max-w-[1200px] mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">

            {{-- Tarjeta: Clases Colectivas --}}
            <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-start text-left transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <div class="mb-6 bg-[#1A3878] p-4 rounded-xl flex items-center justify-center w-16 h-16">
                    <img src="{{ asset('imagenes/clases-logo.png') }}" alt="Clases Colectivas" class="w-full h-full object-contain">
                </div>
                <h3 class="text-gray-900 text-xl font-bold mb-4 m-0">Clases colectivas</h3>
                <p class="text-gray-600 text-base leading-relaxed mb-6 flex-1">
                    Accede al catálogo completo de clases. Consulta los horarios disponibles. Garantiza tu plaza con nuestra
                    herramienta de reserva online antes de cada entrenamiento.
                </p>
                <a href="{{ url('/servicios') }}" class="text-[#1A3878] font-bold flex items-center gap-2 hover:underline">
                    Ver Clases <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            {{-- Tarjeta: Entrenador Personal --}}
            <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-start text-left transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <div class="mb-6 bg-[#1A3878] p-4 rounded-xl flex items-center justify-center w-16 h-16">
                    <img src="{{ asset('imagenes/gimnasio-cardio-logo.png') }}" alt="Entrenador Personal" class="w-full h-full object-contain">
                </div>
                <h3 class="text-gray-900 text-xl font-bold mb-4 uppercase m-0">Entrenador Personal</h3>
                <p class="text-gray-600 text-base leading-relaxed mb-4">
                    Nuestro equipo de profesionales diseñará un plan 100% adaptado a tus metas, monitorizando tu progreso y ajustando la rutina al detalle.
                </p>
                <ul class="text-gray-600 text-sm space-y-2 mb-6 list-none p-0 flex-1">
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#1A3878] flex-shrink-0"></span> Planes nutricionales personalizados.
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#1A3878] flex-shrink-0"></span> Seguimiento por nuestra plataforma web.
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#1A3878] flex-shrink-0"></span> Especialización en masa muscular y pérdida.
                    </li>
                </ul>
                <a href="{{ route('valoracion') }}" class="text-[#1A3878] font-bold flex items-center gap-2 hover:underline">
                    Solicitar Valoración <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            {{-- Tarjeta: Membresía --}}
            <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-start text-left transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <div class="mb-6 bg-[#1A3878] p-4 rounded-xl flex items-center justify-center w-16 h-16">
                    <img src="{{ asset('imagenes/piscina-logo.png') }}" alt="Membresía" class="w-full h-full object-contain">
                </div>
                <h3 class="text-gray-900 text-xl font-bold mb-4 m-0">Membresía</h3>
                <p class="text-gray-600 text-base leading-relaxed mb-6 flex-1">
                    Acceso total y sin restricciones a todas las instalaciones y clases. Suscríbete ahora eligiendo la
                    modalidad de pago que prefieras (mensual, trimestral o anual).
                </p>
                <a href="{{ url('/tarifas') }}" class="text-[#1A3878] font-bold flex items-center gap-2 hover:underline">
                    Ver Tarifas <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

        </section>
    </div>
@endsection