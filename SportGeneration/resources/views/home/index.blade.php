{{-- Página principal. --}}
@extends('layouts.app')

@section('titulo', 'Sport Generation - Tu gimnasio de confianza')

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
        {{-- Banner --}}
        <section
            class="min-h-[360px] sm:h-[450px] bg-cover bg-center flex items-center justify-center text-center px-4 sm:px-5 md:px-[15%]"
            style="background-image: linear-gradient(rgba(0,0,0,0.74), rgba(0,0,0,0.68)), url('{{ asset('imagenes/banner.jpg') }}');">
            <div class="max-w-[900px]">
                <h1 class="text-white text-2xl sm:text-3xl md:text-[42px] font-extrabold leading-[1.2] drop-shadow-md m-0">
                    Olvídate de colas y llamadas. Accede a todo nuestro catálogo al instante.
                </h1>

                @if($ctaMode === 'qr')
                    <button type="button" id="abrirQrHome"
                        class="inline-block w-full sm:w-auto mt-8 bg-[#265E1F] text-white py-4 px-10 sm:px-12 rounded-2xl font-extrabold text-[16px] sm:text-[18px] tracking-wide transition-all duration-300 hover:scale-105 hover:bg-[#265E1F] shadow-xl shadow-[#265E1F]/35">
                        {{ $ctaText }}
                    </button>
                @elseif($ctaMode === 'blocked')
                    <div
                        class="inline-block w-full sm:w-auto mt-8 bg-amber-50 text-amber-900 border border-amber-300 py-3 px-6 rounded-xl font-bold text-[14px] sm:text-[15px] shadow-lg">
                        {{ $ctaText }}
                    </div>
                @else
                    <a href="{{ $ctaUrl }}"
                        class="inline-block w-full sm:w-auto mt-8 py-4 px-10 sm:px-12 rounded-2xl font-extrabold text-[16px] sm:text-[18px] tracking-wide transition-all duration-300 hover:scale-105 shadow-xl {{ $ctaText === 'Empieza ahora' ? 'bg-[#ADFE01] text-[#265E1F] hover:bg-[#ADFE01] shadow-[#ADFE01]/40' : 'bg-[#265E1F] text-white hover:bg-[#265E1F] shadow-[#265E1F]/35' }}">
                        {{ $ctaText }}
                    </a>
                @endif

            </div>
        </section>

        {{-- Título --}}
        <section class="text-center pt-8 sm:pt-10 pb-2 px-4 sm:px-5">
            <h2 class="text-[28px] sm:text-[32px] text-[#265E1F] mb-2.5 font-bold leading-tight max-w-[980px] mx-auto m-0">
                Por tan solo 30 euros al mes puedes
                tener acceso a lo siguiente:</h2>
            {{-- <p class="text-gray-600 text-base sm:text-lg m-0">Todo lo que necesitas para empezar</p>--}}
        </section>

        {{-- Tarjetas --}}
        <section
            class="px-4 sm:px-5 pt-2 sm:pt-4 pb-5 sm:pb-6 max-w-[900px] mx-auto grid grid-cols-1 md:grid-cols-2 items-stretch gap-5 sm:gap-6">

            {{-- Tarjeta: Clases Colectivas --}}
            <div
                class="bg-white p-6 sm:p-7 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-start text-left h-full md:min-h-[430px] transition-all duration-300 hover:shadow-xl hover:-translate-y-1 hover:border-[#265E1F]/20">
                <div class="mb-5 flex items-center justify-center w-16 h-16">
                    <img src="{{ asset('imagenes/clases-logo.png') }}" alt="Clases Colectivas"
                        class="w-[58px] h-[58px] object-contain">
                </div>
                <h3 class="text-gray-900 text-2xl sm:text-[28px] font-black leading-tight mb-3 m-0">Clases colectivas</h3>
                <p class="text-gray-600 text-base leading-normal mb-5">
                    Accede al catálogo completo de clases, consulta los horarios disponibles y reserva ahora las clases que
                    más te gusten. Garantiza tu plaza mediante
                    la reserva online antes de cada entrenamiento en el siguiente botón.
                </p>
                <a href="{{ url('/servicios') }}"
                    class="mt-auto pt-1 text-[#265E1F] font-bold flex items-center gap-2 hover:underline self-start">
                    Ver Clases <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            {{-- Tarjeta: Entrenador Personal --}}
            <div
                class="bg-white p-6 sm:p-7 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-start text-left h-full md:min-h-[430px] transition-all duration-300 hover:shadow-xl hover:-translate-y-1 hover:border-[#265E1F]/20">
                <div class="mb-5 flex items-center justify-center w-16 h-16">
                    <img src="{{ asset('imagenes/gimnasio-cardio-logo.png') }}" alt="Entrenador Personal"
                        class="w-[58px] h-[58px] object-contain">
                </div>
                <h3 class="text-gray-900 text-2xl sm:text-[28px] font-black leading-tight mb-3 m-0">Cele Molina</h3>
                <p class="text-gray-600 text-base leading-normal mb-2">
                    Nuestro entrenador profesional diseñará un plan 100% adaptado a tus metas,
                    ajustando la rutina al detalle.
                </p>
                <ul class="text-gray-600 text-sm space-y-1 mb-4 list-none p-0">
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#265E1F] flex-shrink-0"></span> Asesoramiento.
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#265E1F] flex-shrink-0"></span> Rutinas personalizadas.
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#265E1F] flex-shrink-0"></span> Orientación nutricional.
                    </li>
                </ul>
                <a href="{{ route('valoracion') }}"
                    class="mt-auto pt-1 text-[#265E1F] font-bold flex items-center gap-2 hover:underline self-start">
                    Solicitar valoración <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            {{--
            Tarjeta: Membresía
            <div
                class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-start text-left transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <div class="mb-6 bg-[#265E1F] p-4 rounded-xl flex items-center justify-center w-16 h-16">
                    <img src="{{ asset('imagenes/piscina-logo.png') }}" alt="Membresía"
                        class="w-full h-full object-contain">
                </div>
                <h3 class="text-gray-900 text-xl font-bold mb-4 m-0">Membresía</h3>
                <p class="text-gray-600 text-base leading-relaxed mb-6 flex-1">
                    Acceso total y sin restricciones a todas las instalaciones y clases. Suscríbete ahora eligiendo la
                    modalidad de pago que prefieras (mensual, trimestral o anual).
                </p>
                <a href="{{ url('/tarifas') }}" class="text-[#265E1F] font-bold flex items-center gap-2 hover:underline">
                    Ver tarifas <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
            --}}
        </section>
        <section class="text-center pt-5 sm:pt-6 pb-10 sm:pb-12 px-4 sm:px-5">
            <h2 class="text-[28px] sm:text-[32px] text-[#265E1F] mb-2.5 font-bold leading-tight max-w-[980px] mx-auto m-0">
                Además de un acceso al gimnasio
                mediante QR/RFID</h2>
        </section>
    </div>

    @if($ctaMode === 'qr')
        <script>
            (() => {
                // El botón del inicio abre el QR.
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