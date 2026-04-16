{{-- Vista de servicios con calendario interactivo y resumen de ofertas. --}}
@extends('layouts.app')

@section('titulo', 'Servicios Detallados - SeaFit')

@section('contenido')
    <div class="bg-white font-display text-[#4B5563]">
        <main class="flex flex-1 flex-col items-center flex-grow">


            {{-- Cabecera principal de la página --}}
            <div class="w-full text-center py-16 bg-[#F8F8F8] border-b border-gray-200">
                <h1 class="text-gray-900 text-4xl lg:text-5xl font-black leading-tight tracking-tighter">
                    Descubre la Oferta Completa de SeaFit
                </h1>
                <p class="text-lg mt-3 max-w-3xl mx-auto px-4">
                    Selecciona el camino que mejor se adapta a tus objetivos: entrenamientos grupales, soporte personal o
                    acceso ilimitado.
                </p>
            </div>

            <div class="layout-content-container flex flex-col w-full max-w-7xl flex-1 gap-16 px-4 py-16 lg:py-24">
                {{-- Mensaje de éxito tras una reserva/cancelación --}}
                @if(session('success'))
                    <div
                        class="bg-green-100 text-green-800 p-4 rounded-2xl border border-green-200 font-bold text-center animate-bounce">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Seccion de clases colectivas con calendario visual --}}
                <section id="clases"
                    class="grid grid-cols-1 lg:grid-cols-[380px_1fr] gap-8 p-6 lg:p-10 rounded-[3rem] bg-white border border-gray-100 shadow-2xl transition-all">

                    {{-- Bloque izquierdo: descripcion + leyenda --}}
                    <div class="flex flex-col gap-6 justify-center">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center bg-[#1A3878]/10">
                            <span class="material-symbols-outlined text-4xl text-[#1A3878]">groups</span>
                        </div>
                        <h2 class="text-gray-900 text-4xl font-black tracking-tight italic">Clases Colectivas</h2>
                        <p class="text-gray-600 text-lg leading-relaxed">
                            Reserva tu plaza de forma visual en nuestro calendario interactivo. Cambia de día para ver toda
                            la oferta semanal.
                        </p>

                        <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 space-y-4">
                            <h4 class="font-black text-[#0A1931] uppercase text-sm tracking-widest">Leyenda</h4>
                            <div class="flex items-center gap-3 text-sm font-bold text-gray-500">
                                <span class="w-4 h-4 rounded-full bg-[#a3e635] shadow-sm"></span> Disponible
                            </div>
                            <div class="flex items-center gap-3 text-sm font-bold text-gray-500">
                                <span class="w-4 h-4 rounded-full bg-[#1A3878] shadow-sm"></span> Tu Reserva
                            </div>
                            <div class="flex items-center gap-3 text-sm font-bold text-gray-500">
                                <span class="w-4 h-4 rounded-full bg-gray-200 shadow-sm"></span> Clase Llena
                            </div>
                        </div>

                        <button onclick="window.location.href='{{ route('agenda') }}'"
                            class="flex w-fit items-center justify-center rounded-2xl h-14 px-8 bg-[#1A3878] text-white text-lg font-bold hover:bg-[#0A1931] transition-all shadow-lg mt-4 uppercase tracking-tighter">
                            Ver Agenda Completa
                        </button>
                    </div>

                    {{-- Bloque derecho: calendario de clases por horas --}}
                    <div
                        class="bg-[#F8F9FA] rounded-[2.5rem] border border-gray-200 shadow-inner overflow-hidden flex flex-col h-[750px]">
                        {{-- Selector horizontal de días --}}
                        <div class="p-4 bg-white border-b border-gray-200 flex gap-2 overflow-x-auto no-scrollbar">
                            @php
                                $diasSemana = [
                                    ['value' => 'Lunes', 'label' => 'Lunes'],
                                    ['value' => 'Martes', 'label' => 'Martes'],
                                    ['value' => 'Miercoles', 'label' => 'Miércoles'],
                                    ['value' => 'Jueves', 'label' => 'Jueves'],
                                    ['value' => 'Viernes', 'label' => 'Viernes'],
                                    ['value' => 'Sabado', 'label' => 'Sábado'],
                                    ['value' => 'Domingo', 'label' => 'Domingo'],
                                ];
                                $diaActivo = request('dia', 'Lunes');
                            @endphp
                            @foreach($diasSemana as $diaItem)
                                <a href="{{ route('servicios', ['dia' => $diaItem['value']]) }}#clases"
                                    class="flex-shrink-0 text-xs font-black px-6 py-2.5 rounded-full transition-all uppercase tracking-widest
                                                                                                                                    {{ $diaActivo == $diaItem['value'] ? 'bg-[#1A3878] text-white shadow-lg scale-105' : 'bg-gray-100 text-gray-400 hover:bg-gray-200' }}">
                                    {{ $diaItem['label'] }}
                                </a>
                            @endforeach
                        </div>

                        {{-- Rejilla: columna de horas + area de tarjetas --}}
                        <div class="flex-1 overflow-y-auto relative grid grid-cols-[80px_1fr] bg-white scroll-smooth"
                            id="calendar-body">
                            {{-- Columna de horas --}}
                            <div class="bg-gray-50/50 border-r border-gray-100">
                                @for($h = 8; $h <= 21; $h++)
                                    <div
                                        class="h-[100px] border-b border-gray-100/50 text-[11px] font-black text-gray-400 flex items-start justify-center pt-3">
                                        {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00
                                    </div>
                                @endfor
                            </div>

                            {{-- area de clases con lineas de fondo cada hora --}}
                            <div
                                class="relative min-h-[1400px] bg-[linear-gradient(to_bottom,#f8fafc_1px,transparent_1px)] bg-[size:100%_100px]">
                                @forelse($clases as $clase)
                                    @php
                                        $yaReservado = Auth::check() && Auth::user()->classes->contains($clase->id);
                                        $estaCompleto = $clase->capacidad_max <= 0;
                                        $columnasTotales = max((int) ($clase->layout_cols ?? 1), 1);
                                        $columnaActual = max((int) ($clase->layout_col ?? 0), 0);
                                        $separacion = 2.0; // porcentaje entre columnas
                                        $ancho = (100 - (($columnasTotales - 1) * $separacion)) / $columnasTotales;
                                        $izquierda = $columnaActual * ($ancho + $separacion);
                                        $top = (int) ($clase->layout_top ?? 0);
                                        $alto = (int) ($clase->layout_height ?? 95);
                                        $modoCompacto = $columnasTotales > 1;
                                        $claseBoton = $modoCompacto ? 'px-4 py-2' : 'px-6 py-2.5';
                                    @endphp

                                    {{-- Tarjeta dinamica de clase --}}
                                    <div class="absolute rounded-3xl p-5 border-l-[8px] shadow-sm transition-all group overflow-hidden
                                                                                                                                        {{ $yaReservado ? 'bg-[#e6f3ff] border-[#1A3878] ring-1 ring-inset ring-blue-200' : ($estaCompleto ? 'bg-gray-100 border-gray-400' : 'bg-white border-[#a3e635] hover:shadow-xl hover:-translate-y-0.5 border shadow-sm') }}"
                                        style="top: {{ $top }}px; height: {{ $alto }}px; left: {{ $izquierda }}%; width: {{ $ancho }}%;">

                                        <div class="flex h-full min-w-0 items-center justify-between gap-3">
                                            <div class="min-w-0 flex-1 pr-2">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span
                                                        class="text-[11px] font-black uppercase {{ $yaReservado ? 'text-[#1A3878]' : 'text-gray-400' }}">
                                                        {{ substr($clase->hora_inicio, 0, 5) }}h -
                                                        {{ \Carbon\Carbon::parse($clase->hora_inicio)->addHour()->format('H:i') }}h
                                                    </span>
                                                </div>
                                                <h4
                                                    class="font-black text-gray-900 leading-tight uppercase tracking-tighter truncate {{ $modoCompacto ? 'text-base' : 'text-lg' }}">
                                                    {{ $clase->nombre }}
                                                </h4>
                                                <p class="text-[11px] font-bold text-gray-500 uppercase italic truncate">
                                                    {{ $clase->sala }} - con {{ $clase->instructor }}
                                                </p>
                                                <p class="text-[11px] font-bold text-gray-500 mt-1">
                                                    Plazas libres: {{ max((int) $clase->capacidad_max, 0) }}
                                                </p>
                                            </div>

                                            {{-- Acciones: reservar / cancelar / lleno --}}
                                            <div class="flex items-center justify-end shrink-0 min-w-[120px]">
                                                @if($yaReservado)
                                                    {{-- Si esta reservada, al pasar el raton permite cancelar --}}
                                                    <div class="flex items-center">
                                                        <div
                                                            class="group-hover:hidden bg-[#1A3878] text-white flex items-center gap-1.5 px-4 py-2 rounded-2xl text-[10px] font-black">
                                                            <span class="material-symbols-outlined text-sm">check_circle</span>
                                                            RESERVADO
                                                        </div>
                                                        <form action="{{ route('clase.cancelar', $clase->id) }}" method="POST"
                                                            class="hidden group-hover:block transition-all self-center"
                                                            onsubmit="return confirm('Seguro que quieres CANCELAR tu reserva en {{ $clase->nombre }}?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="bg-red-500 text-white {{ $claseBoton }} rounded-2xl text-[10px] font-black shadow-lg shadow-red-200 hover:bg-red-600">
                                                                CANCELAR PLAZA
                                                            </button>
                                                        </form>
                                                    </div>
                                                @elseif($estaCompleto)
                                                    <span
                                                        class="bg-gray-200 text-gray-400 px-4 py-2 rounded-2xl text-[10px] font-black uppercase">Lleno</span>
                                                @else
                                                    <form action="{{ route('clase.reservar', $clase->id) }}" method="POST"
                                                        class="self-center"
                                                        onsubmit="return confirm('Confirmas tu reserva para {{ $clase->nombre }} a las {{ substr($clase->hora_inicio, 0, 5) }}h?')">
                                                        @csrf
                                                        <button type="submit"
                                                            class="bg-[#0A1931] text-white {{ $claseBoton }} rounded-2xl text-[10px] font-black hover:bg-[#1A3878] hover:scale-105 transition-all shadow-md">
                                                            RESERVAR
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="flex flex-col items-center justify-center h-[300px] text-gray-300">
                                        <span class="material-symbols-outlined text-5xl mb-2">event_busy</span>
                                        <p class="italic font-bold">No hay clases programadas</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Seccion de entrenador personal --}}
                <section id="entrenador"
                    class="grid grid-cols-1 lg:grid-cols-2 gap-12 p-8 lg:p-12 rounded-[2.5rem] bg-[#F4F4F4] border border-gray-200 shadow-lg">
                    <div class="rounded-[2rem] overflow-hidden shadow-2xl h-full min-h-[350px] bg-gray-300 relative group">
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition-all"></div>
                        <img src="{{ asset('imagenes/imagenEntrenador.jpg') }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                            alt="Entrenador">
                    </div>
                    <div class="flex flex-col gap-6 justify-center text-left">
                        <div
                            class="w-16 h-16 rounded-3xl flex items-center justify-center bg-[#1A3878] text-white rotate-3">
                            <span class="material-symbols-outlined text-4xl">fitness_center</span>
                        </div>
                        <h2 class="text-gray-900 text-4xl font-black italic tracking-tighter">Entrenador Personal</h2>
                        <p class="text-lg text-gray-600 leading-relaxed">
                            Lleva tu fisico al siguiente nivel con planes 100% personalizados. Evaluacion mensual de grasa
                            corporal,
                            masa muscular y rendimiento.
                        </p>
                        <button onclick="window.location.href='{{ route('valoracion') }}'"
                            class="flex w-fit min-w-[240px] items-center justify-center rounded-2xl h-14 px-8 bg-[#1A3878] text-white font-black text-lg hover:bg-[#0A1931] shadow-xl hover:shadow-[#1A3878]/40 transition-all uppercase tracking-widest">
                            Solicitar Valoracion
                        </button>
                    </div>
                </section>

                {{-- Seccion de membresia y beneficios --}}
                <section id="membresia"
                    class="p-10 lg:p-16 rounded-[2.5rem] bg-white text-[#0A1931] shadow-[0_20px_50px_rgba(0,0,0,0.05)] text-center border border-gray-100 relative overflow-hidden">

                    <div class="relative z-10 flex flex-col gap-4 items-center mb-12">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center bg-[#1A3878]/10 mb-2">
                            <span class="material-symbols-outlined text-3xl text-[#1A3878]">pool</span>
                        </div>
                        <h2 class="text-4xl lg:text-5xl font-black tracking-tight text-[#0A1931]">Acceso Total (Membresía)
                        </h2>
                        <p class="text-lg text-gray-500 max-w-3xl mx-auto font-medium leading-relaxed">
                            Tu llave a todas nuestras instalaciones. Elige la duracion de tu plan y disfruta sin
                            restricciones del
                            gimnasio, la piscina y las zonas de relax.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 relative z-10">
                        <div
                            class="p-10 rounded-2xl bg-[#F8FAFC] border border-gray-100 hover:border-blue-200 hover:bg-white transition-all duration-300 group shadow-sm hover:shadow-md">
                            <span
                                class="material-symbols-outlined text-4xl text-[#1A3878] group-hover:scale-110 transition-transform">fitness_center</span>
                            <h4 class="font-black text-lg mt-5 text-[#0A1931]">Gimnasio y Cardio</h4>
                            <p class="text-sm text-gray-400 mt-1 font-medium italic">Maquinaria de última generación.</p>
                        </div>

                        <div
                            class="p-10 rounded-2xl bg-[#F8FAFC] border border-gray-100 hover:border-blue-200 hover:bg-white transition-all duration-300 group shadow-sm hover:shadow-md">
                            <span
                                class="material-symbols-outlined text-4xl text-[#1A3878] group-hover:scale-110 transition-transform">pool</span>
                            <h4 class="font-black text-lg mt-5 text-[#0A1931]">Piscina Climatizada</h4>
                            <p class="text-sm text-gray-400 mt-1 font-medium italic">Acceso a nado libre y clases.</p>
                        </div>

                        <div
                            class="p-10 rounded-2xl bg-[#F8FAFC] border border-gray-100 hover:border-blue-200 hover:bg-white transition-all duration-300 group shadow-sm hover:shadow-md">
                            <span
                                class="material-symbols-outlined text-4xl text-[#1A3878] group-hover:scale-110 transition-transform">hot_tub</span>
                            <h4 class="font-black text-lg mt-5 text-[#0A1931]">Zonas Wellness</h4>
                            <p class="text-sm text-gray-400 mt-1 font-medium italic">Sauna, baño turco y vestuarios.</p>
                        </div>
                    </div>

                    @auth
                        @if(!auth()->user()->is_admin && auth()->user()->isPlanActive())
                            <button type="button" onclick="window.location.href='{{ route('valoracion') }}'"
                                class="mt-10 inline-flex items-center justify-center rounded-2xl h-14 px-8 bg-[#1A3878] text-white font-black text-lg hover:bg-[#0A1931] shadow-xl transition-all uppercase tracking-widest">
                                Contratar Entrenador Personal
                            </button>
                        @elseif(!auth()->user()->is_admin)
                            <button type="button" onclick="window.location.href='{{ route('pago.gestion') }}'"
                                class="mt-10 inline-flex items-center justify-center rounded-2xl h-14 px-8 bg-[#0A1931] text-white font-black text-lg hover:bg-[#1A3878] shadow-xl transition-all uppercase tracking-widest">
                                Activar o completar mi plan
                            </button>
                        @endif
                    @else
                        <button type="button" onclick="window.location.href='{{ url('/tarifas') }}'"
                            class="mt-10 inline-flex items-center justify-center rounded-2xl h-14 px-8 bg-[#1A3878] text-white font-black text-lg hover:bg-[#0A1931] shadow-xl transition-all uppercase tracking-widest">
                            Ver Planes y Precios de Membresía
                        </button>
                    @endauth


                </section>
            </div>
        </main>
    </div>
@endsection
