{{-- Mi perfil. --}}
@extends('layouts.app')

@section('titulo', 'Mi perfil')

@section('contenido')
    <div class="flex flex-col md:flex-row min-h-screen bg-[#EAF7DB] font-sans">
        {{-- Barra lateral del panel de usuario. --}}
        <aside
            class="w-full md:w-[280px] md:min-w-[280px] bg-white p-6 md:p-8 border-b md:border-b-0 md:border-r border-gray-200">
            <h2 class="text-xl font-extrabold text-[#265E1F] mb-8">Panel de usuario</h2>
            <nav class="flex flex-col gap-2">
                <a href="{{ route('perfil') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors bg-[#EAF7DB] text-[#265E1F]">
                    <span class="material-symbols-outlined">person</span> Mi perfil
                </a>
                <a href="{{ route('mis.reservas') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#265E1F]">
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

        {{-- Contenido. --}}
        <main class="flex-1 p-6 md:p-10 lg:p-12 max-w-[1000px]">
            @if(session('success'))
                <div class="bg-[#ADFE01] text-green-800 p-4 rounded-xl mb-6 border border-[#ADFE01] font-medium">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 text-red-800 p-4 rounded-xl mb-6 border border-red-200 font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <header class="mb-8">
                <h1 class="text-3xl md:text-4xl font-black text-[#265E1F] mb-2">Mi perfil</h1>
                <p class="text-gray-500 text-[15px]">
                    AquÃ­ puedes consultar tus datos de cuenta, tus reservas y el estado de tu membresÃ­a actual.
                </p>
            </header>

            @php
                // Estado de membresÃ­a para mostrar informaciÃ³n clara en modo lectura.
                $planActivo = $user->isPlanActive();
                $fechaHasta = optional($user->next_payment_at)->format('d/m/Y') ?? 'Sin fecha';
                $suscripcion = $user->subscription('default');
                $enPeriodo = $suscripcion ? $suscripcion->onGracePeriod() : false;
                $cancelada = $user->tarifa === 'cancelada' || ($suscripcion ? $suscripcion->canceled() : false);
            @endphp

            {{-- Tarjeta de membresÃ­a. --}}
            <section
                class="bg-[#265E1F] text-white p-6 md:p-8 rounded-2xl flex flex-col gap-2 mb-8 shadow-lg border border-white/10">
                <p class="text-xs uppercase tracking-widest text-gray-400 font-bold">MembresÃ­a actual</p>
                <h2 class="text-2xl md:text-3xl font-bold">
                    @if($cancelada && !$enPeriodo && !$planActivo)
                        Sin suscripciÃ³n activa
                    @else
                        {{ $user->tarifa === 'cancelada' ? 'CancelaciÃ³n programada' : ucfirst($user->tarifa) }}
                    @endif
                </h2>
                <p class="text-sm text-gray-300">
                    @if($planActivo)
                        Estado: Activa (hasta {{ $fechaHasta }})
                    @elseif($user->payment_status === 'pendiente')
                        Estado: Pendiente de validaciÃ³n
                    @elseif($user->payment_status === 'impagado')
                        Estado: Impagada
                    @else
                        Estado: Inactiva
                    @endif
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    Para cambiar datos, contraseÃ±a o cancelar la suscripciÃ³n, usa el apartado ConfiguraciÃ³n.
                </p>
            </section>

            {{-- Datos de cuenta. --}}
            <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
                <h3 class="text-xl font-bold text-[#265E1F] mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-400">badge</span> Datos de cuenta
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500">Nombre:</p>
                        <p class="font-bold text-[#265E1F]">{{ $user->nombre }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Apellidos:</p>
                        <p class="font-bold text-[#265E1F]">{{ $user->apellidos }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email:</p>
                        <p class="font-bold text-[#265E1F]">{{ $user->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">DNI:</p>
                        <p class="font-bold text-[#265E1F]">{{ $user->dni }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Fecha de nacimiento:</p>
                        <p class="font-bold text-[#265E1F]">
                            {{ $user->fecha_nacimiento ? \Illuminate\Support\Carbon::parse($user->fecha_nacimiento)->format('d/m/Y') : 'No indicada' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">TelÃ©fono:</p>
                        <p class="font-bold text-[#265E1F]">{{ $user->telefono }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-sm text-gray-500">Domicilio:</p>
                        <p class="font-bold text-[#265E1F]">{{ $user->domicilio }}</p>
                    </div>
                </div>
            </section>

            {{-- Resumen de reservas. --}}
            <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
                <h3 class="text-xl font-bold text-[#265E1F] mb-6">Mis reservas ({{ $user->classes->count() }})</h3>
                <div class="flex flex-col gap-3">
                    @forelse($user->classes as $clase)
                        <div class="p-4 bg-[#EAF7DB] border border-gray-200 rounded-xl">
                            <h4 class="m-0 font-bold text-[#265E1F] text-lg">{{ $clase->nombre }} ({{ $clase->sala }})</h4>
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
                        class="inline-flex items-center justify-center gap-2 bg-[#265E1F] text-white px-5 py-3 rounded-xl font-bold hover:bg-[#265E1F] transition-colors">
                        <span class="material-symbols-outlined">calendar_month</span> Gestionar mis reservas
                    </a>
                    <a href="{{ route('configuracion') }}"
                        class="inline-flex items-center justify-center gap-2 bg-white text-[#265E1F] border border-[#265E1F] px-5 py-3 rounded-xl font-bold hover:bg-[#EAF7DB] transition-colors">
                        <span class="material-symbols-outlined">settings</span> Ir a configuraciÃ³n
                    </a>
                </div>
            </section>
        </main>
    </div>
@endsection

