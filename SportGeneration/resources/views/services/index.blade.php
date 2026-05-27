{{-- Servicios. --}}
@extends('layouts.app')

@section('titulo', 'Servicios')

@section('contenido')
    <div class="bg-white font-display text-[#4B5563]">
        <main class="flex flex-1 flex-col items-center flex-grow">
            {{-- Cabecera --}}
            <div class="w-full text-center py-14 sm:py-16 bg-white border-b border-gray-200">
                <h1
                    class="text-gray-900 text-4xl lg:text-5xl font-black leading-tight tracking-tighter max-w-4xl mx-auto px-4">
                    <span class="block">Descubre lo mejor de</span>
                    <span class="block text-[#265E1F] whitespace-nowrap">Sport Generation</span>
                </h1>
                <p class="text-base sm:text-lg text-[#4A4A4A] font-medium mt-4 max-w-2xl mx-auto px-4 leading-relaxed">
                    Queremos que entrenar sea sencillo, accesible y efectivo.
                </p>
            </div>

            <div
                class="layout-content-container flex flex-col w-full max-w-7xl flex-1 gap-14 lg:gap-16 px-4 sm:px-5 py-12 lg:py-16">
                {{-- Mensaje de éxito tras una reserva/cancelación. --}}
                @if(session('success'))
                    <div
                        class="bg-[#EAF7DB] text-[#265E1F] p-4 rounded-2xl border border-[#ADFE01] font-semibold text-center shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Sección de clases en el calendario. --}}
                <section id="clases"
                    class="grid grid-cols-1 lg:grid-cols-[380px_1fr] gap-8 p-6 lg:p-10 rounded-[2.5rem] bg-white border border-[#EAF7DB] shadow-[0_22px_55px_rgba(38,94,31,0.08)] transition-all">

                    {{-- Bloque izquierdo. --}}
                    <div class="flex flex-col gap-6 justify-center">
                        <div class="mb-5 flex items-center justify-center w-16 h-16">
                            <img src="{{ asset('imagenes/clases-logo.png') }}" alt="Clases Colectivas"
                                class="w-[58px] h-[58px] object-contain">
                        </div>
                        <h2 class="text-gray-900 text-3xl lg:text-4xl font-black tracking-tight">Clases colectivas</h2>
                        <p class="text-gray-600 text-base sm:text-lg leading-relaxed">
                            Reserva tu plaza de forma visual en nuestro calendario.
                        </p>

                        <div class="bg-white p-5 rounded-2xl border border-gray-100 space-y-3">
                            <h4 class="font-black text-[#265E1F] uppercase text-sm tracking-widest">Leyenda</h4>
                            <div class="flex items-center gap-3 text-sm font-bold text-gray-500">
                                <span class="w-4 h-4 rounded-full bg-[#ADFE01] shadow-sm"></span> Disponible
                            </div>
                            <div class="flex items-center gap-3 text-sm font-bold text-gray-500">
                                <span class="w-4 h-4 rounded-full bg-[#265E1F] shadow-sm"></span> Tu reserva
                            </div>
                            <div class="flex items-center gap-3 text-sm font-bold text-gray-500">
                                <span class="w-4 h-4 rounded-full bg-gray-200 shadow-sm"></span> Clase llena
                            </div>
                        </div>

                        <button onclick="window.location.href='{{ route('agenda') }}'"
                            class="flex w-fit items-center justify-center rounded-2xl h-14 px-8 bg-[#265E1F] text-white text-base sm:text-lg font-bold hover:bg-[#265E1F] hover:-translate-y-0.5 transition-all shadow-lg shadow-[#265E1F]/20 mt-2 uppercase tracking-wider">
                            Ver agenda completa
                        </button>
                    </div>

                    {{-- Bloque derecho. --}}
                    <div
                        class="bg-white rounded-[2.25rem] border border-gray-200 shadow-inner overflow-hidden flex flex-col h-[750px]">
                        {{-- Selector horizontal de días --}}
                        <div class="p-3 sm:p-4 bg-white border-b border-gray-200 flex gap-2 overflow-x-auto">
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
                                                                                                                                                                                                                                                                                                                                                                                                            {{ $diaActivo == $diaItem['value'] ? 'bg-[#265E1F] text-white shadow-md scale-[1.02]' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-[#265E1F]' }}">
                                    {{ $diaItem['label'] }}
                                </a>
                            @endforeach
                        </div>

                        {{-- Rejilla.--}}
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

                            {{-- Área de clases. --}}
                            <div
                                class="relative min-h-[1400px] bg-[linear-gradient(to_bottom,#EAF7DB_1px,transparent_1px)] bg-[size:100%_100px]">
                                @forelse($clases as $clase)
                                    @php
                                        $yaReservado = Auth::check() && Auth::user()->classes->contains($clase->id);
                                        $estaCompleto = $clase->capacidad_max <= 0;
                                        $columnasTotales = max((int) ($clase->layout_cols ?? 1), 1);
                                        $columnaActual = max((int) ($clase->layout_col ?? 0), 0);
                                        $separacion = 2.0;
                                        $ancho = (100 - (($columnasTotales - 1) * $separacion)) / $columnasTotales;
                                        $izquierda = $columnaActual * ($ancho + $separacion);
                                        $top = (int) ($clase->layout_top ?? 0);
                                        $alto = (int) ($clase->layout_height ?? 95);
                                        $modoCompacto = $columnasTotales > 1;
                                        $claseBoton = $modoCompacto ? 'px-4 py-2' : 'px-6 py-2.5';
                                    @endphp

                                    {{-- Tarjeta dinamica de clase --}}
                                    <div class="absolute rounded-3xl p-5 border-l-[8px] shadow-sm transition-all group overflow-hidden
                                                                                                                                                                                                                                                        {{ $yaReservado ? 'bg-[#EAF7DB] border-[#265E1F] ring-1 ring-inset ring-[#265E1F]/20' : ($estaCompleto ? 'bg-gray-100 border-gray-400' : 'bg-white border-[#ADFE01] hover:shadow-xl hover:-translate-y-0.5 border shadow-sm') }}"
                                        style="top: {{ $top }}px; height: {{ $alto }}px; left: {{ $izquierda }}%; width: {{ $ancho }}%;">

                                        <div class="flex h-full min-w-0 items-center justify-between gap-3">
                                            <div class="min-w-0 flex-1 pr-2">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span
                                                        class="text-[11px] font-black uppercase {{ $yaReservado ? 'text-[#265E1F]' : 'text-gray-400' }}">
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

                                            {{-- Acciones --}}
                                            <div class="flex items-center justify-end shrink-0 min-w-[120px]">
                                                @if($yaReservado)
                                                    {{-- Si está reservada, al pasar el ratón permite cancelar --}}
                                                    <div class="flex items-center">
                                                        <div
                                                            class="group-hover:hidden bg-[#265E1F] text-white flex items-center gap-1.5 px-4 py-2 rounded-2xl text-[10px] font-black">
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
                                                            class="bg-[#265E1F] text-white {{ $claseBoton }} rounded-2xl text-[10px] font-black hover:bg-[#265E1F] hover:scale-105 transition-all shadow-md">
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

                {{-- Sección de entrenador personal --}}
                <section id="entrenador"
                    class="grid grid-cols-1 lg:grid-cols-2 gap-10 p-8 lg:p-12 rounded-[2.5rem] bg-white border border-gray-200 shadow-[0_16px_40px_rgba(0,0,0,0.08)]">
                    <div class="rounded-[2rem] overflow-hidden shadow-2xl h-full min-h-[350px] bg-gray-300 relative group">
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition-all"></div>
                        <img src="{{ asset('imagenes/imagenEntrenador.jpg') }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700"
                            alt="Entrenador">
                    </div>
                    <div class="flex flex-col gap-6 justify-center text-left">
                        <div class="mb-5 flex items-center justify-center w-16 h-16">
                            <img src="{{ asset('imagenes/gimnasio-cardio-logo.png') }}" alt="Entrenador Personal"
                                class="w-[58px] h-[58px] object-contain">
                        </div>
                        <h2 class="text-gray-900 text-4xl font-black tracking-tighter">Entrenador personal</h2>
                        <p class="text-lg text-gray-600 leading-relaxed">
                            Cele Molina es un gran entrenador y fisioculturista de éxito, con él puedes llevar tu físico al
                            siguiente nivel
                            con planes 100% personalizados. Evaluación mensual de grasa
                            corporal, masa muscular y rendimiento.
                        </p>
                        <button onclick="window.location.href='{{ route('valoracion') }}'"
                            class="flex w-fit min-w-[240px] items-center justify-center rounded-2xl h-14 px-8 bg-[#265E1F] text-white font-black text-lg hover:bg-[#265E1F] shadow-xl hover:shadow-[#265E1F]/40 transition-all uppercase tracking-widest">
                            Solicitar valoración
                        </button>
                    </div>
                </section>

                {{-- Sección de membresía
                <section id="membresia"
                    class="p-10 lg:p-16 rounded-[2.5rem] bg-white text-[#265E1F] shadow-[0_20px_50px_rgba(0,0,0,0.05)] text-center border border-gray-100 relative overflow-hidden">

                    <div class="relative z-10 flex flex-col gap-4 items-center mb-12">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center bg-[#265E1F]/10 mb-2">
                            <span class="material-symbols-outlined text-3xl text-[#265E1F]">pool</span>
                        </div>
                        <h2 class="text-4xl lg:text-5xl font-black tracking-tight text-[#265E1F]">Acceso total
                        </h2>
                        <p class="text-lg text-gray-500 max-w-3xl mx-auto font-medium leading-relaxed">
                            Nuestro gimnasio cuenta con las mejores instalaciones para poder facilitarte el entrenamiento.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 relative z-10">
                        <div
                            class="p-10 rounded-2xl bg-[#EAF7DB] border border-gray-100 hover:border-[#265E1F]/20 hover:bg-white transition-all duration-300 group shadow-sm hover:shadow-md">
                            <span
                                class="material-symbols-outlined text-4xl text-[#265E1F] group-hover:scale-110 transition-transform">fitness_center</span>
                            <h4 class="font-black text-lg mt-5 text-[#265E1F]">Cardio y fuerza</h4>
                            <p class="text-sm text-gray-400 mt-1 font-medium italic">Maquinaria de última generación.</p>
                        </div>

                        <div
                            class="p-10 rounded-2xl bg-[#EAF7DB] border border-gray-100 hover:border-[#265E1F]/20 hover:bg-white transition-all duration-300 group shadow-sm hover:shadow-md">
                            <span
                                class="material-symbols-outlined text-4xl text-[#265E1F] group-hover:scale-110 transition-transform">pool</span>
                            <h4 class="font-black text-lg mt-5 text-[#265E1F]">Crossfit</h4>
                            <p class="text-sm text-gray-400 mt-1 font-medium italic">Acceso libre y clases.</p>
                        </div>

                        <div
                            class="p-10 rounded-2xl bg-[#EAF7DB] border border-gray-100 hover:border-[#265E1F]/20 hover:bg-white transition-all duration-300 group shadow-sm hover:shadow-md">
                            <span
                                class="material-symbols-outlined text-4xl text-[#265E1F] group-hover:scale-110 transition-transform">hot_tub</span>
                            <h4 class="font-black text-lg mt-5 text-[#265E1F]">Zonas wellness</h4>
                            <p class="text-sm text-gray-400 mt-1 font-medium italic">Sauna, baño turco y vestuarios.</p>
                        </div>
                    </div>

                    @auth
                    @if(!auth()->user()->is_admin && auth()->user()->isPlanActive())
                    <button type="button" onclick="window.location.href='{{ url('/tarifas') }}'"
                        class="mt-10 inline-flex items-center justify-center rounded-2xl h-14 px-8 bg-[#265E1F] text-white font-black text-lg hover:bg-[#265E1F] shadow-xl transition-all uppercase tracking-widest">
                        Ir a la página de tarifas
                    </button>
                    @elseif(!auth()->user()->is_admin)
                    <button type="button" onclick="window.location.href='{{ route('pago.gestion') }}'"
                        class="mt-10 inline-flex items-center justify-center rounded-2xl h-14 px-8 bg-[#265E1F] text-white font-black text-lg hover:bg-[#265E1F] shadow-xl transition-all uppercase tracking-widest">
                        Activar o completar mi plan
                    </button>
                    @endif
                    @else
                    <button type="button" onclick="window.location.href='{{ url('/tarifas') }}'"
                        class="mt-10 inline-flex items-center justify-center rounded-2xl h-14 px-8 bg-[#265E1F] text-white font-black text-lg hover:bg-[#265E1F] shadow-xl transition-all uppercase tracking-widest">
                        Ver planes y precios de membresía
                    </button>
                    @endauth
                </section>
                --}}
            </div>
        </main>
    </div>

@endsection