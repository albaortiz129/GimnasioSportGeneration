@extends('moldes.inicio')

@section('titulo', 'Servicios Detallados - SeaFit')

@section('contenido')
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

    <div class="bg-white font-display text-[#4B5563]">
        <main class="flex flex-1 flex-col items-center flex-grow">
            {{-- CABECERA --}}
            <div class="w-full text-center py-16 bg-[#F8F8F8] border-b border-gray-200">
                <h1 class="text-gray-900 text-4xl lg:text-5xl font-black leading-tight tracking-tighter">
                    Descubre la Oferta Completa de SeaFit
                </h1>
                <p class="text-lg mt-3 max-w-3xl mx-auto px-4">
                    Selecciona el camino que mejor se adapta a tus objetivos: entrenamientos grupales, soporte personal o acceso ilimitado.
                </p>
            </div>

            <div class="layout-content-container flex flex-col w-full max-w-7xl flex-1 gap-16 px-4 py-16 lg:py-24">

                {{-- MENSAJES DE ÉXITO O ERROR --}}
                @if(session('success'))
                    <div class="bg-green-100 text-green-800 p-4 rounded-2xl border border-green-200 font-bold text-center animate-bounce">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- SECCIÓN CLASES COLECTIVAS (CALENDARIO VISUAL) --}}
                <section id="clases" class="grid grid-cols-1 lg:grid-cols-[380px_1fr] gap-8 p-6 lg:p-10 rounded-[3rem] bg-white border border-gray-100 shadow-2xl transition-all">
                    
                    {{-- Texto descriptivo (Izquierda) --}}
                    <div class="flex flex-col gap-6 justify-center">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center bg-[#1A3878]/10">
                            <span class="material-symbols-outlined text-4xl text-[#1A3878]">groups</span>
                        </div>
                        <h2 class="text-gray-900 text-4xl font-black tracking-tight italic">Clases Colectivas</h2>
                        <p class="text-gray-600 text-lg leading-relaxed">
                            Reserva tu plaza de forma visual en nuestro calendario interactivo. Cambia de día para ver toda la oferta semanal.
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

                    {{-- CALENDARIO TIPO GOOGLE CALENDAR (Derecha) --}}
                    <div class="bg-[#F8F9FA] rounded-[2.5rem] border border-gray-200 shadow-inner overflow-hidden flex flex-col h-[750px]">
                        {{-- Selector de días mejorado --}}
                        <div class="p-4 bg-white border-b border-gray-200 flex gap-2 overflow-x-auto no-scrollbar">
                            @php 
                                $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']; 
                                $diaActivo = request('dia', 'Lunes');
                            @endphp
                            @foreach($diasSemana as $dia)
                                <a href="{{ route('servicios', ['dia' => $dia]) }}#clases"
                                    class="flex-shrink-0 text-xs font-black px-6 py-2.5 rounded-full transition-all uppercase tracking-widest
                                    {{ $diaActivo == $dia ? 'bg-[#1A3878] text-white shadow-lg scale-105' : 'bg-gray-100 text-gray-400 hover:bg-gray-200' }}">
                                    {{ $dia }}
                                </a>
                            @endforeach
                        </div>

                        {{-- Rejilla de Tiempo --}}
                        <div class="flex-1 overflow-y-auto relative grid grid-cols-[80px_1fr] bg-white scroll-smooth" id="calendar-body">
                            {{-- Columna de Horas --}}
                            <div class="bg-gray-50/50 border-r border-gray-100">
                                @for($h = 8; $h <= 21; $h++)
                                    <div class="h-[100px] border-b border-gray-100/50 text-[11px] font-black text-gray-400 flex items-start justify-center pt-3">
                                        {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00
                                    </div>
                                @endfor
                            </div>

                            {{-- Área de Clases con líneas de fondo --}}
                            <div class="relative min-h-[1400px] bg-[linear-gradient(to_bottom,#f8fafc_1px,transparent_1px)] bg-[size:100%_100px]">
                                @forelse($clases as $clase)
                                    @php 
                                        $hora = (int)substr($clase->hora_inicio, 0, 2);
                                        $minutos = (int)substr($clase->hora_inicio, 3, 2);
                                        $top = ($hora - 8) * 100 + ($minutos * 100 / 60);
                                        
                                        $yaReservado = Auth::check() && Auth::user()->clases->contains($clase->id);
                                        $estaCompleto = $clase->capacidad_max <= 0;
                                    @endphp

                                    {{-- CARD DE CLASE DINÁMICA --}}
                                    <div class="absolute left-4 right-4 rounded-3xl p-5 border-l-[8px] shadow-sm transition-all group overflow-hidden
                                        {{ $yaReservado ? 'bg-[#e6f3ff] border-[#1A3878] ring-1 ring-inset ring-blue-200' : ($estaCompleto ? 'bg-gray-100 border-gray-400' : 'bg-white border-[#a3e635] hover:shadow-xl hover:-translate-y-0.5 border shadow-sm') }}"
                                        style="top: {{ $top }}px; height: 95px;">
                                        
                                        <div class="flex justify-between items-center h-full">
                                            <div>
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="text-[11px] font-black uppercase {{ $yaReservado ? 'text-[#1A3878]' : 'text-gray-400' }}">
                                                        {{ substr($clase->hora_inicio, 0, 5) }}h - {{ \Carbon\Carbon::parse($clase->hora_inicio)->addHour()->format('H:i') }}h
                                                    </span>
                                                </div>
                                                <h4 class="font-black text-lg text-gray-900 leading-tight uppercase tracking-tighter">{{ $clase->nombre }}</h4>
                                                <p class="text-[11px] font-bold text-gray-500 uppercase italic">{{ $clase->sala }} · con {{ $clase->instructor }}</p>
                                            </div>

                                            {{-- Acciones con Confirmación --}}
                                            <div class="flex items-center">
                                                @if($yaReservado)
                                                    {{-- Estado Reservado: Muestra check, en Hover muestra Cancelar --}}
                                                    <div class="flex items-center">
                                                        <div class="group-hover:hidden bg-[#1A3878] text-white flex items-center gap-1.5 px-4 py-2 rounded-2xl text-[10px] font-black">
                                                            <span class="material-symbols-outlined text-sm">check_circle</span> RESERVADO
                                                        </div>
                                                        <form action="{{ route('clase.cancelar', $clase->id) }}" method="POST" class="hidden group-hover:block transition-all"
                                                              onsubmit="return confirm('¿Seguro que quieres CANCELAR tu reserva en {{ $clase->nombre }}?')">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-2xl text-[10px] font-black shadow-lg shadow-red-200 hover:bg-red-600">
                                                                CANCELAR PLAZA
                                                            </button>
                                                        </form>
                                                    </div>
                                                @elseif($estaCompleto)
                                                    <span class="bg-gray-200 text-gray-400 px-4 py-2 rounded-2xl text-[10px] font-black uppercase">Lleno</span>
                                                @else
                                                    {{-- Botón Reservar con Confirmación --}}
                                                    <form action="{{ route('clase.reservar', $clase->id) }}" method="POST"
                                                          onsubmit="return confirm('¿Confirmas tu reserva para {{ $clase->nombre }} a las {{ substr($clase->hora_inicio, 0, 5) }}h?')">
                                                        @csrf
                                                        <button type="submit" class="bg-[#0A1931] text-white px-6 py-2.5 rounded-2xl text-[10px] font-black hover:bg-[#1A3878] hover:scale-105 transition-all shadow-md">
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

                {{-- SECCIÓN ENTRENADOR PERSONAL --}}
                <section id="entrenador" class="grid grid-cols-1 lg:grid-cols-2 gap-12 p-8 lg:p-12 rounded-[2.5rem] bg-[#F4F4F4] border border-gray-200 shadow-lg">
                    <div class="rounded-[2rem] overflow-hidden shadow-2xl h-full min-h-[350px] bg-gray-300 relative group">
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition-all"></div>
                        <img src="{{ asset('imagenes/imagenEntrenador.jpg') }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" alt="Entrenador">
                    </div>
                    <div class="flex flex-col gap-6 justify-center text-left">
                        <div class="w-16 h-16 rounded-3xl flex items-center justify-center bg-[#1A3878] text-white rotate-3">
                            <span class="material-symbols-outlined text-4xl">fitness_center</span>
                        </div>
                        <h2 class="text-gray-900 text-4xl font-black italic tracking-tighter">Entrenador Personal</h2>
                        <p class="text-lg text-gray-600 leading-relaxed">
                            Lleva tu físico al siguiente nivel con planes 100% personalizados. Evaluación mensual de grasa corporal, masa muscular y rendimiento.
                        </p>
                        <button onclick="window.location.href='{{ route('valoracion') }}'" 
                            class="flex w-fit min-w-[240px] items-center justify-center rounded-2xl h-14 px-8 bg-[#1A3878] text-white font-black text-lg hover:bg-[#0A1931] shadow-xl hover:shadow-[#1A3878]/40 transition-all uppercase tracking-widest">
                            Solicitar Valoración
                        </button>
                    </div>
                </section>

                {{-- SECCIÓN MEMBRESÍA --}}
                <section id="membresia" class="p-10 lg:p-16 rounded-[3rem] bg-[#0A1931] text-white shadow-2xl text-center relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-32 -mt-32"></div>
                    <div class="relative z-10 flex flex-col gap-6 items-center">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center bg-white/10">
                            <span class="material-symbols-outlined text-4xl text-[#a3e635]">workspace_premium</span>
                        </div>
                        <h2 class="text-5xl font-black italic tracking-tighter">Acceso Total SeaFit</h2>
                        <p class="text-xl text-gray-300 max-w-3xl mx-auto font-medium">
                            Olvídate de las restricciones. Tu membresía te da acceso ilimitado a la sala de máquinas, clases de natación y spa.
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-12 relative z-10">
                        <div class="p-8 rounded-[2rem] bg-white/5 border border-white/10 backdrop-blur-sm hover:bg-white/10 transition-all">
                            <span class="material-symbols-outlined text-5xl text-[#a3e635]">bolt</span>
                            <h4 class="font-black text-xl mt-4 uppercase tracking-tighter">Gimnasio 24/7</h4>
                        </div>
                        <div class="p-8 rounded-[2rem] bg-white/5 border border-white/10 backdrop-blur-sm hover:bg-white/10 transition-all">
                            <span class="material-symbols-outlined text-5xl text-[#a3e635]">waves</span>
                            <h4 class="font-black text-xl mt-4 uppercase tracking-tighter">Piscina y Spa</h4>
                        </div>
                        <div class="p-8 rounded-[2rem] bg-white/5 border border-white/10 backdrop-blur-sm hover:bg-white/10 transition-all">
                            <span class="material-symbols-outlined text-5xl text-[#a3e635]">self_improvement</span>
                            <h4 class="font-black text-xl mt-4 uppercase tracking-tighter">Zonas Relax</h4>
                        </div>
                    </div>

                    <button class="mt-12 min-w-[300px] h-16 px-10 bg-[#a3e635] text-[#0A1931] font-black rounded-2xl text-xl hover:scale-105 transition-all shadow-2xl uppercase tracking-tighter" 
                            onclick="window.location.href='{{ url('/tarifas') }}'">
                        Ver Planes de Precios
                    </button>
                </section>
            </div>
        </main>
    </div>

    <style>
        .font-display { font-family: 'Lexend', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Estilo sutil de puntos para el fondo del calendario */
        #calendar-body {
            background-image: radial-gradient(#cbd5e1 0.5px, transparent 0.5px);
            background-size: 20px 20px;
        }
    </style>
@endsection