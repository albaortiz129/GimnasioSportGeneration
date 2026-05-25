{{-- Mis reservas: aquÃ­ el socio puede cancelar y aÃ±adir nuevas clases. --}}
@extends('layouts.app')

@section('titulo', 'Mis reservas')

@section('contenido')
    <div class="flex flex-col md:flex-row min-h-screen bg-[#EAF7DB] font-sans">
        {{-- Barra lateral del panel de socio. --}}
        <aside
            class="w-full md:w-[280px] md:min-w-[280px] bg-white p-6 md:p-8 border-b md:border-b-0 md:border-r border-gray-200">
            <h2 class="text-xl font-extrabold text-[#265E1F] mb-8">Panel de socio</h2>
            <nav class="flex flex-col gap-2">
                <a href="{{ route('perfil') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#265E1F]">
                    <span class="material-symbols-outlined">person</span> Mi perfil
                </a>
                <a href="{{ route('mis.reservas') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors bg-[#EAF7DB] text-[#265E1F]">
                    <span class="material-symbols-outlined">calendar_month</span> Mis reservas
                </a>
                <a href="{{ route('pago.gestion') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#265E1F]">
                    <span class="material-symbols-outlined">payments</span> GestiÃ³n de pago
                </a>
                <a href="{{ route('configuracion') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#265E1F]">
                    <span class="material-symbols-outlined">settings</span> ConfiguraciÃ³n
                </a>
            </nav>
        </aside>

        {{-- Contenido principal de reservas. --}}
        <main class="flex-1 p-6 md:p-10 lg:p-12 max-w-[1000px]">
            @if(session('success'))
                <div
                    class="bg-[#ADFE01] text-green-800 p-4 rounded-xl mb-6 border border-[#ADFE01] font-medium flex items-center gap-3">
                    <span class="material-symbols-outlined">check_circle</span>
                    <strong>{{ session('success') }}</strong>
                </div>
            @endif

            <header class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-black text-[#265E1F] mb-2">Mis Reservas</h1>
                        <p class="text-gray-500 text-[15px]">
                            AquÃ­ puedes gestionar tus clases reservadas y cancelar plaza cuando lo necesites.
                        </p>
                    </div>
                </div>
            </header>

            {{-- Listado de reservas actuales. --}}
            <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-[#265E1F]">PrÃ³ximas clases ({{ $user->classes->count() }})</h3>
                </div>

                <div class="flex flex-col gap-4">
                    @forelse($user->classes as $clase)
                        <div
                            class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-5 bg-white border border-gray-200 rounded-xl gap-4 shadow-sm hover:shadow-md transition-shadow">
                            <div>
                                <h4 class="m-0 font-bold text-[#265E1F] text-lg">{{ $clase->nombre }} ({{ $clase->sala }})</h4>
                                <p class="m-0 mt-1 text-sm text-gray-500 font-medium">
                                    {{ $clase->dia_semana }} | {{ substr($clase->hora_inicio, 0, 5) }} h
                                </p>
                            </div>

                            <div
                                class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end border-t sm:border-t-0 border-gray-100 pt-4 sm:pt-0 mt-2 sm:mt-0">
                                <span
                                    class="bg-[#ADFE01] text-green-800 px-4 py-1.5 rounded-full text-xs font-bold flex items-center gap-1">
                                    Confirmada
                                </span>

                                <form action="{{ route('clase.cancelar', $clase->id) }}" method="POST"
                                    onsubmit="return confirm('Â¿Seguro que quieres cancelar tu plaza en {{ $clase->nombre }}?')"
                                    class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-500 bg-transparent border-none font-bold text-sm underline cursor-pointer hover:text-red-700 transition-colors">
                                        Cancelar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 px-5 border-2 border-dashed border-gray-200 rounded-xl">
                            <p class="text-gray-500 mb-4">No tienes clases reservadas actualmente.</p>
                            <a href="{{ route('agenda') }}"
                                class="inline-block bg-[#265E1F] text-white py-2.5 px-6 rounded-lg font-bold transition-transform hover:scale-105 shadow-md">
                                Ir a la agenda.
                            </a>
                        </div>
                    @endforelse
                </div>
            </section>
        </main>
    </div>
@endsection
