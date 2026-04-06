{{-- Vista principal del panel de socio con estado de cuenta y seguridad. --}}
@extends('moldes.inicio')

@section('titulo', 'Panel de Socio - SeaFit')

@section('contenido')
    {{-- Contenedor Principal --}}
    <div class="flex flex-col md:flex-row min-h-screen bg-[#f8fafc] font-sans">

        {{-- BARRA LATERAL (SIDEBAR) --}}
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

        {{-- CONTENIDO PRINCIPAL --}}
        <main class="flex-1 p-6 md:p-10 lg:p-12 max-w-[1000px]">

            {{-- Mensajes de Notificación --}}
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
                <h1 class="text-3xl md:text-4xl font-black text-[#0A1931] mb-2">¡Hola, {{ $user->nombre }}! 👋</h1>
                <p class="text-gray-500 text-[15px]">Bienvenido a tu panel personal. Aquí puedes gestionar tu cuenta y
                    revisar tu progreso.</p>
            </header>

            {{-- TARJETA DE MEMBRESÍA --}}
            @php
                $planActivo = $user->planActivo();
                $fechaHasta = optional($user->next_payment_at)->format('d/m/Y') ?? 'Pendiente';
                $suscripcion = $user->subscription('default');
                $enPeriodo = $suscripcion ? $suscripcion->onGracePeriod() : false;
                $cancelada = $user->tarifa === 'cancelada' || ($suscripcion ? $suscripcion->canceled() : false);
            @endphp


            <section
                class="bg-[#0A1931] text-white p-6 md:p-8 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 mb-10 shadow-lg relative overflow-hidden border border-white/10">
                <div
                    class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full -mr-10 -mt-10 pointer-events-none">
                </div>

                <div class="relative z-10">
                    <p class="text-xs uppercase tracking-widest text-gray-400 font-bold mb-1">Membresía Actual</p>
                    <h2 class="text-2xl md:text-3xl font-bold mb-1">
                        @if($cancelada && !$enPeriodo)
                            Sin suscripción activa
                        @else
                            Acceso Total {{ ucfirst($user->tarifa) }}
                        @endif
                    </h2>
                    <p class="text-sm text-gray-400">
                        @if($planActivo)
                            Estado: Activa (hasta {{ $fechaHasta }})
                        @elseif($user->payment_status === 'pendiente')
                            Estado: Pendiente de validación de pago
                        @elseif($user->payment_status === 'impagado')
                            Estado: Impagada
                        @else
                            Estado: Inactiva
                        @endif

                    </p>
                </div>

                <div class="relative z-10 flex gap-3">
                    {{-- Ambos casos abren el modal para preguntar el plan --}}
                    @if($enPeriodo || $cancelada)
                        <button onclick="document.getElementById('modalPlanes').classList.remove('hidden')"
                            class="bg-[#a3e635] text-[#0A1931] px-6 py-3 rounded-xl font-bold hover:scale-105 transition-transform shadow-md">
                            {{ $enPeriodo ? 'Reanudar / Cambiar Plan' : 'Elegir Nuevo Plan' }}
                        </button>
                    @else
                        <a href="{{ route('pago.gestion') }}"
                            class="bg-white/10 hover:bg-white/20 text-white px-6 py-3 rounded-xl font-bold transition-all border border-white/20 no-underline">
                            Gestionar Pago
                        </a>
                    @endif
                </div>
            </section>

            {{-- DATOS DE CUENTA --}}
            <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
                <h3 class="text-xl font-bold text-[#0A1931] mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-400">badge</span> Datos de Cuenta
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500">Nombre:</p>
                        <p class="font-bold text-[#0A1931]">{{ $user->nombre }} {{ $user->apellidos }}</p>
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
                        <p class="text-sm text-gray-500">Teléfono:</p>
                        <p class="font-bold text-[#0A1931]">{{ $user->telefono }}</p>
                    </div>
                </div>
                <a href="{{ route('configuracion') }}"
                    class="inline-block mt-6 text-[#1A3878] font-bold underline hover:text-[#0A1931] transition-colors">Editar
                    información</a>
            </section>

            {{-- CAMBIAR CONTRASEÑA --}}
            <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
                <h3 class="text-xl font-bold text-[#0A1931] mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-gray-400">lock</span> Seguridad
                </h3>

                @if($errors->any())
                    <div class="bg-red-50 text-red-600 p-3 rounded-xl text-sm mb-4 font-bold border border-red-100">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('perfil.password') }}" method="POST" class="grid grid-cols-1 gap-4 max-w-md">
                    @csrf
                    <input type="password" name="password_actual" placeholder="Contraseña actual"
                        class="w-full p-3 border rounded-xl bg-gray-50 outline-none focus:ring-1 focus:ring-[#1A3878]"
                        required>
                    <input type="password" name="password" placeholder="Nueva contraseña"
                        class="w-full p-3 border rounded-xl bg-gray-50 outline-none focus:ring-1 focus:ring-[#1A3878]"
                        required>
                    <input type="password" name="password_confirmation" placeholder="Confirmar nueva contraseña"
                        class="w-full p-3 border rounded-xl bg-gray-50 outline-none focus:ring-1 focus:ring-[#1A3878]"
                        required>
                    <button type="submit"
                        class="bg-[#0A1931] text-white py-3 rounded-xl font-bold hover:bg-[#1A3878] transition-colors shadow-sm">
                        Actualizar Contraseña
                    </button>
                </form>
            </section>

            {{-- MIS RESERVAS --}}
            <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
                <h3 class="text-xl font-bold text-[#0A1931] mb-6">Mis Reservas ({{ $user->clases->count() }})</h3>
                <div class="flex flex-col gap-3">
                    @forelse($user->clases as $clase)
                        <div
                            class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-4 bg-[#f8fafc] border border-gray-200 rounded-xl gap-4 hover:border-gray-300 transition-colors">
                            <div>
                                <h4 class="m-0 font-bold text-[#0A1931] text-lg">{{ $clase->nombre }} ({{ $clase->sala }})</h4>
                                <p class="m-0 mt-1 text-sm text-gray-500 font-medium">
                                    {{ $clase->dia_semana }} | {{ substr($clase->hora_inicio, 0, 5) }} h
                                </p>
                            </div>
                            <div
                                class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end border-t sm:border-t-0 border-gray-200 pt-3 sm:pt-0">
                                <span
                                    class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1">✓
                                    Confirmada</span>
                                <form action="{{ route('clase.cancelar', $clase->id) }}" method="POST"
                                    onsubmit="return confirm('¿Quieres cancelar esta reserva?')" class="m-0">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="text-red-500 bg-transparent border-none font-bold text-sm underline cursor-pointer hover:text-red-700 transition-colors">Cancelar</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 px-5 border-2 border-dashed border-gray-200 rounded-xl">
                            <p class="text-gray-500 mb-2">No tienes reservas para esta semana.</p>
                            <a href="{{ route('agenda') }}" class="text-[#1A3878] font-bold hover:underline">Ver horario de
                                clases</a>
                        </div>
                    @endforelse
                </div>
                @if($user->clases->count() > 0)
                    <a href="{{ route('mis.reservas') }}"
                        class="inline-block mt-6 text-[#1A3878] font-bold underline hover:text-[#0A1931] transition-colors">Ver
                        todas mis reservas</a>
                @endif
            </section>
        </main>
    </div>

    {{-- MODAL DE SELECCIÓN DE PLAN (Fuera de Main para evitar cortes visuales) --}}
    <div id="modalPlanes"
        class="fixed inset-0 bg-black/70 backdrop-blur-md z-[100] hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] p-8 md:p-10 max-w-md w-full shadow-2xl relative">
            <h3 class="text-3xl font-black text-[#0A1931] mb-3">¡Vuelve a SeaFit! 🏋️‍♂️</h3>
            <p class="text-gray-500 mb-8 font-medium">Selecciona tu tarifa para reactivar o cambiar tu suscripción
                inmediata.</p>

            <form action="{{ route('plan.reanudar') }}" method="POST" class="flex flex-col gap-4">
                @csrf
                <label
                    class="flex items-center p-5 border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-[#1A3878] transition-all group has-[:checked]:border-[#1A3878] has-[:checked]:bg-blue-50/50">
                    <input type="radio" name="tarifa" value="mensual" class="hidden" checked>
                    <div class="flex-1">
                        <p class="font-bold text-xl text-[#0A1931] group-has-[:checked]:text-[#1A3878]">Mensual</p>
                        <p class="text-sm text-gray-500">29.99€ / mes · Sin compromiso</p>
                    </div>
                    <span
                        class="material-symbols-outlined text-gray-200 group-has-[:checked]:text-[#1A3878] text-3xl">check_circle</span>
                </label>

                <label
                    class="flex items-center p-5 border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-[#1A3878] transition-all group has-[:checked]:border-[#1A3878] has-[:checked]:bg-blue-50/50">
                    <input type="radio" name="tarifa" value="trimestral" class="hidden">
                    <div class="flex-1">
                        <p class="font-bold text-xl text-[#0A1931] group-has-[:checked]:text-[#1A3878]">Trimestral</p>
                        <p class="text-sm text-gray-500">79.99€ / trimestre · <span class="text-green-600 font-bold">Ahorro
                                extra</span></p>
                    </div>
                    <span
                        class="material-symbols-outlined text-gray-200 group-has-[:checked]:text-[#1A3878] text-3xl">check_circle</span>
                </label>

                <label
                    class="flex items-center p-5 border-2 border-gray-100 rounded-2xl cursor-pointer hover:border-[#1A3878] transition-all group has-[:checked]:border-[#1A3878] has-[:checked]:bg-blue-50/50">
                    <input type="radio" name="tarifa" value="anual" class="hidden">
                    <div class="flex-1">
                        <p class="font-bold text-xl text-[#0A1931] group-has-[:checked]:text-[#1A3878]">Anual</p>
                        <p class="text-sm text-gray-500">250.00€ / año · <span class="text-green-600 font-bold">Ahorra
                                100€</span></p>
                    </div>
                    <span
                        class="material-symbols-outlined text-gray-200 group-has-[:checked]:text-[#1A3878] text-3xl">check_circle</span>
                </label>

                {{-- Código de Descuento --}}
                <div class="mt-2">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1">¿Tienes un código?</label>
                    <div class="relative mt-1">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">sell</span>
                        <input type="text" name="coupon" placeholder="Ej: SEAFIT20"
                            class="w-full pl-10 pr-4 py-3 border-2 border-gray-100 rounded-2xl outline-none focus:border-[#1A3878] transition-all text-sm font-bold uppercase">
                    </div>
                </div>

                <button type="submit"
                    class="mt-6 bg-[#0A1931] text-white py-4 rounded-2xl font-bold text-xl hover:bg-[#1A3878] shadow-lg transition-all">
                    Confirmar Activación
                </button>

                <button type="button" onclick="document.getElementById('modalPlanes').classList.add('hidden')"
                    class="text-gray-400 font-bold text-sm hover:text-red-500 transition-colors uppercase tracking-widest mt-2">
                    Cerrar
                </button>
            </form>
        </div>
    </div>
@endsection
