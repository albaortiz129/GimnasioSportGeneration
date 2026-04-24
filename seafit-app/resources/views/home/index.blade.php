{{-- Vista pública principal (home) con acceso a servicios y planes. --}}
@extends('layouts.app')

@section('titulo', 'SeaFit - Tu gimnasio online')

@section('contenido')
    @php
        $ctaMode = 'link';

        if (auth()->check()) {
            if (auth()->user()->is_admin) {
                $ctaUrl = route('admin.dashboard');
                $ctaText = 'Ir al panel admin';
            } else {
                // Si el socio está activo, en inicio mostramos acceso directo al QR.
                if (auth()->user()->isPlanActive()) {
                    $ctaUrl = null;
                    $ctaText = 'Entrar al gimnasio';
                    $ctaMode = 'qr';
                } else {
                    $ctaUrl = null;
                    $ctaText = 'Debes ponerte en contacto con el Administrador del gimnasio.';
                    $ctaMode = 'blocked';
                }
            }
        } else {
            $ctaUrl = route('registro');
            $ctaText = 'Empieza ahora';
        }
    @endphp

    <div class="w-full">
        {{-- Banner Principal (Hero) --}}

        <section class="min-h-[360px] sm:h-[450px] bg-cover bg-center flex items-center justify-center text-center px-4 sm:px-5 md:px-[15%]"
            style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('{{ asset('imagenes/banner.jpg') }}');">
            <div class="max-w-[900px]">
                <h1 class="text-white text-2xl sm:text-3xl md:text-[42px] font-extrabold leading-[1.2] drop-shadow-md m-0">
                    Olvídate de colas y llamadas. Accede a todo nuestro catálogo al instante.
                </h1>

                @if($ctaMode === 'qr')
                    <button type="button" id="abrirQrHome"
                        class="inline-block w-full sm:w-auto mt-8 bg-[#1A3878] text-white py-3 px-8 rounded-xl font-bold text-[15px] sm:text-[16px] transition-transform duration-300 hover:scale-105 shadow-lg">
                        {{ $ctaText }}
                    </button>
                @elseif($ctaMode === 'blocked')
                    <div
                        class="inline-block w-full sm:w-auto mt-8 bg-amber-50 text-amber-900 border border-amber-300 py-3 px-6 rounded-xl font-bold text-[14px] sm:text-[15px] shadow-lg">
                        {{ $ctaText }}
                    </div>
                @else
                    <a href="{{ $ctaUrl }}"
                        class="inline-block w-full sm:w-auto mt-8 bg-[#1A3878] text-white py-3 px-8 rounded-xl font-bold text-[15px] sm:text-[16px] transition-transform duration-300 hover:scale-105 shadow-lg">
                        {{ $ctaText }}
                    </a>
                @endif

            </div>
        </section>

        {{-- Título de Introducción --}}
        <section class="text-center pt-12 sm:pt-[60px] pb-5 px-4 sm:px-5">
            <h2 class="text-[28px] sm:text-[32px] text-[#051221] mb-2.5 font-bold m-0">¿Listo?</h2>
            <p class="text-gray-600 text-base sm:text-lg m-0">Todo lo que necesitas para empezar</p>
        </section>

        {{-- 3. Cuadrícula de Servicios --}}
        <section class="px-4 sm:px-5 py-10 sm:py-12 max-w-[1200px] mx-auto grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">

            {{-- Tarjeta: Clases Colectivas --}}
            <div
                class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-start text-left transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <div class="mb-6 bg-[#1A3878] p-4 rounded-xl flex items-center justify-center w-16 h-16">
                    <img src="{{ asset('imagenes/clases-logo.png') }}" alt="Clases Colectivas"
                        class="w-full h-full object-contain">
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
            <div
                class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-start text-left transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <div class="mb-6 bg-[#1A3878] p-4 rounded-xl flex items-center justify-center w-16 h-16">
                    <img src="{{ asset('imagenes/gimnasio-cardio-logo.png') }}" alt="Entrenador Personal"
                        class="w-full h-full object-contain">
                </div>
                <h3 class="text-gray-900 text-xl font-bold mb-4 uppercase m-0">Entrenador Personal</h3>
                <p class="text-gray-600 text-base leading-relaxed mb-4">
                    Nuestro equipo de profesionales diseñará un plan 100% adaptado a tus metas, monitorizando tu progreso y
                    ajustando la rutina al detalle.
                </p>
                <ul class="text-gray-600 text-sm space-y-2 mb-6 list-none p-0 flex-1">
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#1A3878] flex-shrink-0"></span> Planes nutricionales
                        personalizados.
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#1A3878] flex-shrink-0"></span> Seguimiento por nuestra
                        plataforma web.
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#1A3878] flex-shrink-0"></span> Especialización en masa
                        muscular y pérdida.
                    </li>
                </ul>
                <a href="{{ route('valoracion') }}"
                    class="text-[#1A3878] font-bold flex items-center gap-2 hover:underline">
                    Solicitar Valoración <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            {{-- Tarjeta: Membresía --}}
            <div
                class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-start text-left transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <div class="mb-6 bg-[#1A3878] p-4 rounded-xl flex items-center justify-center w-16 h-16">
                    <img src="{{ asset('imagenes/piscina-logo.png') }}" alt="Membresía"
                        class="w-full h-full object-contain">
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

    @if($ctaMode === 'qr')
        <script>
            (() => {
                // El botón del inicio abre el modal QR global.
                const botonInicio = document.getElementById('abrirQrHome');

                botonInicio?.addEventListener('click', () => {
                    if (typeof window.openGymQrModal === 'function') {
                        window.openGymQrModal();
                    }
                });
            })();
        </script>
    @endif
@endsection
