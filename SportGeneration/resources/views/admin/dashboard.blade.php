{{-- Panel admin simplificado: clientes, avisos de pago y accesos rápidos. --}}
@extends('layouts.app')

@section('titulo', 'Panel de administración')

@section('contenido')
    @php
        $cobrosDisponibles = $billingColumnsReady ?? false;
        $descuentosDisponibles = $discountsTablesReady ?? false;
        $preciosPlan = ['mensual' => 29.99, 'trimestral' => 75.00, 'anual' => 250.00];
        $avisosPago = $cobrosDisponibles ? ($impagados ?? collect()) : collect();
        $totalAvisos = $avisosPago->count();
    @endphp

    <div class="min-h-screen bg-[#F9FAFB]">
        <div class="max-w-6xl mx-auto px-4 py-8">
            @if(session('success'))
                <div class="mb-4 rounded-xl border border-[#ADFE01] bg-[#ADFE01] px-4 py-3 font-bold text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 font-bold text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <header class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-black text-[#265E1F]">Administración</h1>
                    <p class="mt-1 text-sm text-gray-500">Gestión rápida de socios, pagos y clases.</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.user.create') }}"
                        class="rounded-xl border border-[#265E1F] bg-white px-4 py-2 text-sm font-bold text-[#265E1F]">
                        Nuevo cliente
                    </a>
                    <a href="{{ route('admin.classes.index') }}"
                        class="rounded-xl border border-[#265E1F] bg-white px-4 py-2 text-sm font-bold text-[#265E1F]">
                        Clases
                    </a>
                    <a href="{{ route('admin.discounts.index') }}"
                        class="rounded-xl border border-[#265E1F] bg-white px-4 py-2 text-sm font-bold text-[#265E1F]">
                        Descuentos
                    </a>
                </div>
            </header>

            <section class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-gray-500">Clientes</p>
                    <p class="mt-2 text-3xl font-black text-[#265E1F]">{{ $usuarios->count() }}</p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-gray-500">Avisos de pago</p>
                    <p class="mt-2 text-3xl font-black {{ $totalAvisos > 0 ? 'text-red-600' : 'text-[#265E1F]' }}">
                        {{ $totalAvisos }}
                    </p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-bold text-gray-500">Búsqueda actual</p>
                    <p class="mt-2 truncate text-lg font-black text-gray-900">
                        {{ ($buscar ?? '') !== '' ? $buscar : 'Todos los clientes' }}
                    </p>
                </div>
            </section>

            <section
                class="mb-6 rounded-2xl border {{ $totalAvisos > 0 ? 'border-amber-200 bg-amber-50' : 'border-green-200 bg-green-50' }} p-5">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined {{ $totalAvisos > 0 ? 'text-amber-700' : 'text-green-700' }}">
                            {{ $totalAvisos > 0 ? 'notifications_active' : 'check_circle' }}
                        </span>
                        <div>
                            <h2 class="font-black {{ $totalAvisos > 0 ? 'text-amber-900' : 'text-green-900' }}">
                                {{ $totalAvisos > 0 ? 'Hay pagos que revisar' : 'Todo al día' }}
                            </h2>
                            <p class="text-sm {{ $totalAvisos > 0 ? 'text-amber-800' : 'text-green-800' }}">
                                @if(!$cobrosDisponibles)
                                    El módulo de cobros aún no está disponible en esta base de datos.
                                @elseif($totalAvisos > 0)
                                    {{ $totalAvisos }} cliente{{ $totalAvisos === 1 ? '' : 's' }} necesita{{ $totalAvisos === 1 ? '' : 'n' }} revisión.
                                @else
                                    No hay pagos pendientes ni impagados.
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($totalAvisos > 0)
                        <button type="button" id="toggle-payment-alerts"
                            class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-amber-900 shadow-sm">
                            Ver avisos
                        </button>
                    @endif
                </div>

                @if($totalAvisos > 0)
                    <div id="payment-alerts-list" class="mt-4 hidden space-y-3">
                        @foreach($avisosPago as $u)
                            @php
                                $estadoPago = match ($u->payment_status) {
                                    'al_dia' => 'al día',
                                    'pendiente' => 'pendiente',
                                    'impagado' => 'impagado',
                                    default => 'sin estado',
                                };
                                $metodoPendiente = match (strtolower((string) $u->metodo_pago)) {
                                    'visa' => 'Tarjeta',
                                    'efectivo' => 'Efectivo',
                                    default => 'pago manual',
                                };
                            @endphp

                            <div class="rounded-xl border border-amber-200 bg-white p-4">
                                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <p class="font-black text-gray-900">{{ $u->nombre }} {{ $u->apellidos }}</p>
                                        <p class="text-sm text-gray-500">
                                            {{ $estadoPago }} | Próximo cobro:
                                            {{ optional($u->next_payment_at)->format('d/m/Y') ?? 'Sin fecha' }}
                                        </p>
                                    </div>

                                    @if($u->payment_status === 'pendiente')
                                        <form action="{{ route('admin.user.aprobar_manual', $u) }}" method="POST">
                                            @csrf
                                            <button class="rounded-lg bg-green-600 px-3 py-2 text-xs font-bold text-white">
                                                Confirmar {{ $metodoPendiente }}
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.user.edit', $u) }}"
                                            class="rounded-lg bg-[#265E1F] px-3 py-2 text-xs font-bold text-white">
                                            Revisar ficha
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <form method="GET" action="{{ route('admin.dashboard') }}"
                class="mb-6 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <label class="text-sm font-bold text-gray-700">Buscar cliente</label>
                <div class="mt-2 flex flex-col gap-2 sm:flex-row">
                    <input type="text" name="q" value="{{ $buscar ?? '' }}" placeholder="Nombre, email o DNI"
                        class="w-full rounded-xl border border-gray-200 p-3 outline-none focus:border-[#265E1F]">
                    <button class="rounded-xl bg-[#265E1F] px-5 py-3 font-bold text-white">Buscar</button>
                </div>
            </form>

            <section class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="border-b border-gray-100 p-5">
                    <h2 class="text-xl font-black text-gray-900">Clientes</h2>
                    <p class="text-sm text-gray-500">Acciones básicas sin saturar el panel.</p>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($usuarios as $user)
                        @php
                            $precioBase = $preciosPlan[$user->tarifa] ?? 0.0;
                            $ultimoDescuento = $descuentosDisponibles ? $user->latestDiscountRedemption : null;
                            $codigoDescuento = optional(optional($ultimoDescuento)->discountCode)->code;
                            $descuentoAplicado = (float) ($ultimoDescuento->discount_applied ?? 0);
                            $totalCobrar = max($precioBase - $descuentoAplicado, 0);
                            $estadoPagoUser = match ($user->payment_status) {
                                'al_dia' => 'al día',
                                'pendiente' => 'pendiente',
                                'impagado' => 'impagado',
                                default => 'sin estado',
                            };
                            $badgePago = match ($user->payment_status) {
                                'al_dia' => 'bg-green-50 text-green-700 border-green-200',
                                'pendiente' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'impagado' => 'bg-red-50 text-red-700 border-red-200',
                                default => 'bg-gray-50 text-gray-600 border-gray-200',
                            };
                        @endphp

                        <article class="p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="truncate text-lg font-black text-gray-900">
                                            {{ $user->nombre }} {{ $user->apellidos }}
                                        </h3>
                                        @if($cobrosDisponibles)
                                            <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $badgePago }}">
                                                {{ $estadoPagoUser }}
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mt-1 text-sm text-gray-500">{{ $user->email }} | DNI: {{ $user->dni }}</p>
                                    <p class="mt-1 text-sm text-gray-600">
                                        Plan: <strong>{{ ucfirst($user->tarifa) }}</strong>
                                        @if($user->tarifa !== 'cancelada')
                                            | Cobro estimado: <strong>{{ number_format($totalCobrar, 2, ',', '.') }} EUR</strong>
                                        @endif
                                        @if($codigoDescuento)
                                            | Cupón: <strong>{{ $codigoDescuento }}</strong>
                                        @endif
                                    </p>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('admin.user.edit', $user) }}"
                                        class="rounded-lg bg-[#265E1F] px-3 py-2 text-sm font-bold text-white">
                                        Editar
                                    </a>

                                    @if($cobrosDisponibles)
                                        <form action="{{ route('admin.user.renew', $user) }}" method="POST">
                                            @csrf
                                            <button class="rounded-lg bg-green-600 px-3 py-2 text-sm font-bold text-white">
                                                Renovar
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.user.mark_unpaid', $user) }}" method="POST">
                                            @csrf
                                            <button class="rounded-lg bg-amber-600 px-3 py-2 text-sm font-bold text-white">
                                                Marcar impago
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.user.delete', $user) }}" method="POST"
                                        onsubmit="return confirm('¿Seguro que quieres eliminar este usuario?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg bg-red-600 px-3 py-2 text-sm font-bold text-white">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="p-8 text-center text-sm font-bold text-gray-500">
                            No hay clientes que mostrar.
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="mt-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h2 class="text-lg font-black text-gray-900">Notificaciones de impago</h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Este correo recibirá avisos internos cuando marques a un cliente como impagado.
                        </p>
                    </div>

                    <form action="{{ route('admin.notifications.unpaid') }}" method="POST"
                        class="flex w-full flex-col gap-2 sm:flex-row lg:max-w-xl">
                        @csrf
                        <input type="email" name="unpaid_notification_email"
                            value="{{ old('unpaid_notification_email', $unpaidNotificationEmail ?? '') }}"
                            placeholder="correo@ejemplo.com"
                            class="w-full rounded-xl border border-gray-200 p-3 outline-none focus:border-[#265E1F]">
                        <button class="rounded-xl bg-[#265E1F] px-5 py-3 font-bold text-white">
                            Guardar
                        </button>
                    </form>
                </div>

                @error('unpaid_notification_email')
                    <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                @enderror
            </section>
        </div>
    </div>

    <script>
        (() => {
            const toggle = document.getElementById('toggle-payment-alerts');
            const list = document.getElementById('payment-alerts-list');

            toggle?.addEventListener('click', () => {
                list?.classList.toggle('hidden');
                toggle.textContent = list?.classList.contains('hidden') ? 'Ver avisos' : 'Ocultar avisos';
            });
        })();
    </script>
@endsection
