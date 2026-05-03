{{-- Agenda semanal de clases con reservas y cancelaciones. --}}
@extends('layouts.app')

@section('titulo', 'Agenda')

@section('contenido')
    <div class="bg-[#F8F8F8] min-h-screen py-10 font-sans">
        <div class="container mx-auto max-w-6xl px-4">

            <header class="text-center mb-10">
                <h1 class="text-[#0A1931] text-4xl font-black tracking-tighter mb-2 italic">CALENDARIO DE CLASES</h1>
                <p class="text-gray-500 font-medium">Gestiona tus entrenamientos de forma visual</p>
            </header>

            {{-- Filtrar agenda. --}}
            <div
                class="flex justify-center gap-2 mb-8 bg-white p-2 rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
                @php
                    $dias = [
                        ['value' => 'Lunes', 'label' => 'Lunes'],
                        ['value' => 'Martes', 'label' => 'Martes'],
                        ['value' => 'Miercoles', 'label' => 'Miércoles'],
                        ['value' => 'Jueves', 'label' => 'Jueves'],
                        ['value' => 'Viernes', 'label' => 'Viernes'],
                        ['value' => 'Sabado', 'label' => 'Sábado'],
                        ['value' => 'Domingo', 'label' => 'Domingo'],
                    ];
                    $diaActual = request('dia', 'Lunes');
                @endphp
                @foreach($dias as $diaItem)
                    <a href="{{ route('agenda', ['dia' => $diaItem['value']]) }}"
                        class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all whitespace-nowrap
                                                            {{ $diaActual == $diaItem['value'] ? 'bg-[#1A3878] text-white shadow-md' : 'text-gray-400 hover:bg-gray-50' }}">
                        {{ $diaItem['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-2xl border border-gray-100 overflow-hidden">
                <div class="grid grid-cols-[100px_1fr] min-h-[800px] relative">

                    {{-- Columna de horas. --}}
                    <div class="bg-gray-50/50 border-r border-gray-100 flex flex-col">
                        @for($h = 8; $h <= 21; $h++)
                            <div
                                class="h-[100px] border-b border-gray-100/50 text-[12px] font-black text-gray-400 flex items-start justify-center pt-4">
                                {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00
                            </div>
                        @endfor
                    </div>

                    {{-- Tarjetas de clase. --}}
                    <div class="relative p-0" id="calendar-grid">
                        @for($i = 0; $i <= 13; $i++)
                            <div class="absolute w-full border-b border-gray-100" style="top: {{ $i * 100 }}px; height: 100px;">
                            </div>
                        @endfor

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
                                            $alto = (int) ($clase->layout_height ?? 90);
                                            $modoCompacto = $columnasTotales > 1;
                                            $claseBoton = $modoCompacto ? 'px-4 py-2' : 'px-6 py-2.5';
                                            // Plazas disponibles (capacidad - inscritos), nunca menor que 0.
                                            $plazasLibres = max((int) $clase->capacidad_max - $clase->users->count(), 0);
                                        @endphp

                                        <div class="absolute rounded-[1.5rem] p-5 border-l-[6px] shadow-sm transition-all duration-300 group
                                                                                {{ $yaReservado
                            ? 'bg-[#e6f3ff] border-[#1A3878] ring-1 ring-[#1A3878]/10'
                            : ($estaCompleto ? 'bg-gray-100 border-gray-300 grayscale' : 'bg-white border-[#a3e635] hover:shadow-xl hover:-translate-y-1 border shadow-sm') }}"
                                            style="top: {{ $top }}px; height: {{ $alto }}px; left: {{ $izquierda }}%; width: {{ $ancho }}%;">

                                            <div class="flex h-full min-w-0 items-center justify-between gap-3">
                                                <div class="flex flex-col min-w-0 flex-1 pr-2">
                                                    <span
                                                        class="text-[11px] font-black uppercase tracking-widest {{ $yaReservado ? 'text-[#1A3878]' : 'text-gray-400' }}">
                                                        {{ substr($clase->hora_inicio, 0, 5) }} -
                                                        {{ \Carbon\Carbon::parse($clase->hora_inicio)->addHour()->format('H:i') }}
                                                    </span>
                                                    <h4
                                                        class="font-black {{ $yaReservado ? 'text-[#0A1931]' : 'text-gray-800' }} tracking-tight truncate {{ $modoCompacto ? 'text-base' : 'text-xl' }}">
                                                        {{ $clase->nombre }}
                                                    </h4>
                                                    <p class="text-xs font-bold text-gray-500 truncate">{{ $clase->sala }} - con
                                                        {{ $clase->instructor }}
                                                    </p>
                                                    <p class="text-xs font-bold text-gray-500 mt-1">
                                                        Plazas libres: {{ $plazasLibres }}
                                                    </p>
                                                </div>

                                                {{-- Acciones según estado de reserva. --}}
                                                <div class="relative flex items-center justify-end shrink-0 min-w-[120px]">
                                                    @if($yaReservado)
                                                        <div class="flex items-center">
                                                            <div
                                                                class="group-hover:hidden flex items-center gap-1 bg-[#1A3878] text-white px-4 py-2 rounded-full text-xs font-black">
                                                                <span class="material-symbols-outlined text-sm">check_circle</span> RESERVADO
                                                            </div>

                                                            <form action="{{ route('clase.cancelar', $clase->id) }}" method="POST"
                                                                class="hidden group-hover:block transition-all animate-in fade-in zoom-in duration-200 self-center"
                                                                onsubmit="return confirm('Seguro que quieres CANCELAR tu reserva en {{ $clase->nombre }}?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="bg-red-500 text-white {{ $claseBoton }} rounded-2xl hover:bg-red-600 shadow-lg shadow-red-200 flex items-center gap-1 text-xs font-black">
                                                                    <span class="material-symbols-outlined text-sm">delete</span> CANCELAR PLAZA
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @elseif($estaCompleto)
                                                        <span
                                                            class="bg-gray-200 text-gray-500 px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-tighter">Lleno</span>
                                                    @else
                                                        <form action="{{ route('clase.reservar', $clase->id) }}" method="POST"
                                                            class="self-center"
                                                            onsubmit="return confirm('Confirmas tu reserva para {{ $clase->nombre }} a las {{ substr($clase->hora_inicio, 0, 5) }}h?')">
                                                            @csrf
                                                            <button type="submit"
                                                                class="bg-[#0A1931] text-white {{ $claseBoton }} rounded-2xl hover:bg-[#1A3878] hover:scale-105 transition-all text-xs font-black shadow-md uppercase">
                                                                Reservar
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                        @empty
                            <div class="h-full flex flex-col items-center justify-center text-gray-300">
                                <span class="material-symbols-outlined text-6xl mb-2">event_busy</span>
                                <p class="italic font-bold">Sin entrenamientos para este día</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        #calendar-grid {
            background-image: radial-gradient(#e5e7eb 1px, transparent 1px);
            background-size: 30px 30px;
        }
    </style>
@endsection