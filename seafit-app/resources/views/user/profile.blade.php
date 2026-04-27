{{-- Panel "Mi Perfil": solo lectura de cuenta, membresía y resumen de reservas. --}}
@extends('layouts.app')

@section('titulo', 'Mi Perfil - SeaFit')

@section('contenido')
    <div class="flex flex-col md:flex-row min-h-screen bg-[#f8fafc] font-sans">
        {{-- Barra lateral del panel de socio. --}}
        <aside
            class="w-full md:w-[280px] md:min-w-[280px] bg-white p-6 md:p-8 border-b md:border-b-0 md:border-r border-gray-200">
            <h2 class="text-xl font-extrabold text-[#0A1931] mb-8">Panel de Socio</h2>
            <nav class="flex flex-col gap-2">
                <a href="{{ route('perfil') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors bg-[#e6f3ff] text-[#1A3878]">
                    <span class="material-symbols-outlined">person</span> Mi Perfil
                </a>
                <a href="{{ route('mis.reservas') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#0A1931]">
                    <span class="material-symbols-outlined">calendar_month</span> Mis Reservas
                </a>
                <a href="{{ route('pago.gestion') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#0A1931]">
                    <span class="material-symbols-outlined">payments</span> Gestión de Pago
                </a>
                <a href="{{ route('configuracion') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#0A1931]">
                    <span class="material-symbols-outlined">settings</span> Configuración
                </a>
            </nav>
        </aside>

        {{-- Contenido principal del perfil. --}}
        <main class="flex-1 p-6 md:p-10 lg:p-12 max-w-[1000px]">
            @if(session('success'))
                <div class="bg-green-100 text-green-800 p-4 rounded-xl mb-6 border border-green-200 font-medium">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 text-red-800 p-4 rounded-xl mb-6 border border-red-200 font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <header class="mb-8">
                <h1 class="text-3xl md:text-4xl font-black text-[#0A1931] mb-2">Mi Perfil</h1>
                <p class="text-gray-500 text-[15px]">
                    Aquí puedes consultar tus datos de cuenta, tus reservas y el estado de tu membresía actual.
                </p>
            </header>

            @php
                // Estado de membresía para mostrar información clara en modo lectura.
                $planActivo = $user->isPlanActive();
                $fechaHasta = optional($user->next_payment_at)->format('d/m/Y') ?? 'Sin fecha';
                $suscripcion = $user->subscription('default');
                $enPeriodo = $suscripcion ? $suscripcion->onGracePeriod() : false;
                $cancelada = $user->tarifa === 'cancelada' || ($suscripcion ? $suscripcion->canceled() : false);
            @endphp

            {{-- Tarjeta de membresía en modo solo lectura. --}}
            <section
                class="bg-[#0A1931] text-white p-6 md:p-8 rounded-2xl flex flex-col gap-2 mb-8 shadow-lg border border-white/10">
                <p class="text-xs uppercase tracking-widest text-gray-400 font-bold">Membresía actual</p>
                <h2 class="text-2xl md:text-3xl font-bold">
                    @if($cancelada && !$enPeriodo && !$planActivo)
                        Sin suscripción activa
                    @else
                        {{ $user->tarifa === 'cancelada' ? 'Cancelación programada' : ucfirst($user->tarifa) }}
                    @endif
                </h2>
                <p class="text-sm text-gray-300">
                    @if($planActivo)
                        Estado: Activa (hasta {{ $fechaHasta }})
                    @elseif($user->payment_status === 'pendiente')
                        Estado: Pendiente de validación
                    @elseif($user->payment_status === 'impagado')
                        Estado: Impagada
                    @else
                        Estado: Inactiva
                    @endif
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    Para cambiar datos, contraseña o cancelar la suscripción, usa el apartado Configuración.
                </p>
            </section>

            {{-- Datos de cuenta en modo solo lectura. --}}
            <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
                <h3 class="text-xl font-bold text-[#0A1931] mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-400">badge</span> Datos de Cuenta
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500">Nombre:</p>
                        <p class="font-bold text-[#0A1931]">{{ $user->nombre }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Apellidos:</p>
                        <p class="font-bold text-[#0A1931]">{{ $user->apellidos }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email:</p>
                        <p class="font-bold text-[#0A1931]">{{ $user->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">DNI:</p>
                        <p class="font-bold text-[#0A1931]">{{ $user->dni }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Fecha de nacimiento:</p>
                        <p class="font-bold text-[#0A1931]">
                            {{ $user->fecha_nacimiento ? \Illuminate\Support\Carbon::parse($user->fecha_nacimiento)->format('d/m/Y') : 'No indicada' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Teléfono:</p>
                        <p class="font-bold text-[#0A1931]">{{ $user->telefono }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-sm text-gray-500">Domicilio:</p>
                        <p class="font-bold text-[#0A1931]">{{ $user->domicilio }}</p>
                    </div>
                </div>
            </section>

            {{-- Resumen de reservas sin acciones de edición/cancelación en esta vista. --}}
            <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
                <h3 class="text-xl font-bold text-[#0A1931] mb-6">Mis Reservas ({{ $user->classes->count() }})</h3>
                <div class="flex flex-col gap-3">
                    @forelse($user->classes as $clase)
                        <div class="p-4 bg-[#f8fafc] border border-gray-200 rounded-xl">
                            <h4 class="m-0 font-bold text-[#0A1931] text-lg">{{ $clase->nombre }} ({{ $clase->sala }})</h4>
                            <p class="m-0 mt-1 text-sm text-gray-500 font-medium">
                                {{ $clase->dia_semana }} | {{ substr($clase->hora_inicio, 0, 5) }} h
                            </p>
                        </div>
                    @empty
                        <div class="text-center py-10 px-5 border-2 border-dashed border-gray-200 rounded-xl">
                            <p class="text-gray-500 mb-2">No tienes reservas para esta semana.</p>
                        </div>
                    @endforelse
                </div>

                <div class="flex flex-col sm:flex-row gap-3 mt-6">
                    <a href="{{ route('mis.reservas') }}"
                        class="inline-flex items-center justify-center gap-2 bg-[#1A3878] text-white px-5 py-3 rounded-xl font-bold hover:bg-[#0A1931] transition-colors">
                        <span class="material-symbols-outlined">calendar_month</span> Gestionar mis reservas
                    </a>
                    <a href="{{ route('configuracion') }}"
                        class="inline-flex items-center justify-center gap-2 bg-white text-[#1A3878] border border-[#1A3878] px-5 py-3 rounded-xl font-bold hover:bg-blue-50 transition-colors">
                        <span class="material-symbols-outlined">settings</span> Ir a configuración
                    </a>
                </div>
            </section>
        </main>
    </div>
@endsection
