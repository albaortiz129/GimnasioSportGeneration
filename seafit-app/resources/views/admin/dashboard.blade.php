{{-- Vista principal del panel admin: gestion de clientes, cobros y planes. --}}
@extends('layouts.app')

@section('titulo', 'Panel de Administracion - SeaFit')

@section('contenido')
{{-- Si faltan columnas de cobros en la BD, algunas acciones se ocultan. --}}
@php
    $cobrosDisponibles = $billingColumnsReady ?? false;
    $descuentosDisponibles = $discountsTablesReady ?? false;
    $preciosPlan = ['mensual' => 29.99, 'trimestral' => 75.00, 'anual' => 250.00];
@endphp

<div class="max-w-7xl mx-auto px-4 py-8">
    {{-- Mensajes rápidos de resultado (éxito o error) --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-black text-gray-800">Panel de Gestión</h1>
            <p class="text-sm text-gray-500">Administra usuarios, planes, cobros y clases.</p>
        </div>
        {{-- Accesos rápidos a pantallas principales del panel admin. --}}
        <div class="flex gap-2">
            <a href="{{ route('admin.user.create') }}"
                class="bg-[#0A1931] text-white px-4 py-2 rounded-xl font-bold text-sm">
                Nuevo cliente
            </a>
            <a href="{{ route('admin.classes.index') }}"
                class="bg-[#1A3878] text-white px-4 py-2 rounded-xl font-bold text-sm">
                Gestionar clases
            </a>
            <a href="{{ route('admin.discounts.index') }}"
                class="bg-[#0A1931] text-white px-4 py-2 rounded-xl font-bold text-sm">
                Descuentos
            </a>
        </div>
    </div>

    {{-- Buscador global de clientes --}}
    <form method="GET" action="{{ route('admin.dashboard') }}" class="bg-white border rounded-2xl p-4 mb-6">
        <label class="text-sm font-bold text-gray-700">Buscar cliente</label>
        <div class="flex gap-2 mt-2">
            <input type="text" name="q" value="{{ $buscar ?? '' }}" placeholder="Nombre, apellidos, email o DNI"
                class="w-full border rounded-xl p-2">
            <button class="bg-[#0A1931] text-white px-4 rounded-xl font-bold">Buscar</button>
        </div>
    </form>

    {{-- Resumen de impagos --}}
    <section class="bg-white border rounded-2xl p-4 mb-6">
        <h2 class="text-lg font-bold mb-3 text-red-700">Clientes con impago o pago vencido</h2>
        @if(!$cobrosDisponibles)
            <p class="text-sm text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-xl p-3">
                El módulo de cobros aún no está disponible en esta base de datos.
                Ejecuta las migraciones pendientes para activar esta sección.
            </p>
        @else
            @if(isset($impagados) && $impagados->isNotEmpty())
                @foreach($impagados as $u)
                @php
                    $precioBase = $preciosPlan[$u->tarifa] ?? 0.0;
                    $ultimoDescuento = $descuentosDisponibles ? $u->latestDiscountRedemption : null;
                    $codigoDescuento = optional(optional($ultimoDescuento)->discountCode)->code;
                    $descuentoAplicado = (float) ($ultimoDescuento->discount_applied ?? 0);
                    $totalCobrar = max($precioBase - $descuentoAplicado, 0);
                    $estadoPago = match ($u->payment_status) {
                        'al_dia' => 'al día',
                        'pendiente' => 'pendiente',
                        'impagado' => 'impagado',
                        default => 'sin estado',
                    };
                @endphp
                <div class="flex flex-col md:flex-row md:items-center md:justify-between border rounded-xl p-3 mb-2">
                    <div>
                        <p class="font-bold">{{ $u->nombre }} {{ $u->apellidos }} ({{ $u->dni }})</p>
                        <p class="text-sm text-gray-600">
                            {{ $u->email }} | Estado: <span class="font-bold">{{ $estadoPago }}</span>
                        </p>
                        @if($codigoDescuento)
                            <p class="text-sm text-indigo-700 mt-1">
                                Cupón: <span class="font-bold">{{ $codigoDescuento }}</span>
                                | Descuento: -{{ number_format($descuentoAplicado, 2, ',', '.') }} EUR
                                | Cobro estimado: {{ number_format($totalCobrar, 2, ',', '.') }} EUR
                            </p>
                        @endif

                        @if($u->payment_status === 'pendiente')
                            @php
                                // Bizum/PayPal se mantienen en el mapeo solo para mostrar registros antiguos.
                                $metodoPendiente = match (strtolower((string) $u->metodo_pago)) {
                                    'bizum' => 'Bizum',
                                    'paypal' => 'PayPal',
                                    'transferencia' => 'Transferencia',
                                    'tarjeta', 'stripe', 'visa' => 'Tarjeta',
                                    'efectivo' => 'Efectivo',
                                    default => 'pago manual',
                                };
                            @endphp
                            <p class="text-xs text-amber-700 mt-1">
                                Pendiente de validar cobro por {{ $metodoPendiente }}.
                            </p>
                            <form action="{{ route('admin.user.aprobar_manual', $u) }}" method="POST" class="mt-2">
                                @csrf
                                <button class="bg-green-600 text-white px-3 py-1 rounded text-xs font-bold">
                                    Confirmar pago por {{ $metodoPendiente }}
                                </button>
                            </form>
                        @endif
                    </div>
                    <div class="text-sm text-gray-600">
                        Próximo cobro: {{ optional($u->next_payment_at)->format('d/m/Y') ?? 'Sin fecha' }}
                    </div>
                </div>
                @endforeach
            @else
                <p class="text-sm text-gray-500">No hay clientes en impago.</p>
            @endif
        @endif

    </section>

    {{-- Fichas de clientes --}}
    <div class="space-y-4">
        @foreach($usuarios as $user)
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
            @endphp
            <article class="bg-white border rounded-2xl p-4">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-4">
                    <div>
                        <p class="font-black text-lg">{{ $user->nombre }} {{ $user->apellidos }}</p>
                        <p class="text-sm text-gray-600">{{ $user->email }} | DNI: {{ $user->dni }}</p>
                        @if($codigoDescuento)
                            <p class="text-sm text-indigo-700 mt-1">
                                Cupón usado: <span class="font-bold">{{ $codigoDescuento }}</span>
                                | Descuento aplicado: -{{ number_format($descuentoAplicado, 2, ',', '.') }} EUR
                            </p>
                        @endif
                    </div>
                    <div class="text-sm">
                        {{-- Resumen rápido de estado de plan/pago del cliente. --}}
                        <span class="font-bold">Plan:</span> {{ ucfirst($user->tarifa) }}
                        @if($user->tarifa !== 'cancelada')
                            | <span class="font-bold">Cobro estimado:</span>
                            {{ number_format($totalCobrar, 2, ',', '.') }} EUR
                        @endif
                        @if($cobrosDisponibles)
                            |
                            <span class="font-bold">Pago:</span> {{ $estadoPagoUser }}
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-4 gap-3">
                    @if($cobrosDisponibles)
                        {{-- Formulario rápido para cambiar solo la tarifa. --}}
                        <form action="{{ route('admin.user.plan', $user) }}" method="POST" class="border rounded-xl p-3">
                            @csrf
                            @method('PUT')
                            <p class="font-bold text-sm mb-2">Cambiar plan</p>
                            <select name="tarifa" class="w-full border rounded p-2 mb-2">
                                @foreach(['mensual', 'trimestral', 'anual', 'cancelada'] as $tarifa)
                                    <option value="{{ $tarifa }}" @selected($user->tarifa === $tarifa)>{{ ucfirst($tarifa) }}</option>
                                @endforeach
                            </select>
                            <button class="w-full bg-[#1A3878] text-white py-2 rounded font-bold text-sm">Guardar plan</button>
                        </form>

                        {{-- Registro de cobro manual con método y nota interna. --}}
                        <form action="{{ route('admin.user.manual_charge', $user) }}" method="POST"
                            class="border rounded-xl p-3">
                            @csrf
                            <p class="font-bold text-sm mb-2">Cobro manual</p>
                            <select name="tarifa" class="w-full border rounded p-2 mb-2" required>
                                <option value="mensual">Mensual</option>
                                <option value="trimestral">Trimestral</option>
                                <option value="anual">Anual</option>
                            </select>
                            <select name="metodo_manual" class="w-full border rounded p-2 mb-2" required>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="tarjeta">Tarjeta</option>
                            </select>
                            <input type="text" name="nota" class="w-full border rounded p-2 mb-2" placeholder="Nota (opcional)">
                            <button class="w-full bg-[#0A1931] text-white py-2 rounded font-bold text-sm">Registrar
                                cobro</button>
                        </form>

                        {{-- Acciones de estado de cobro sin editar toda la ficha. --}}
                        <div class="border rounded-xl p-3 flex flex-col gap-2">
                            <p class="font-bold text-sm mb-1">Acciones de pago</p>
                            <form action="{{ route('admin.user.renew', $user) }}" method="POST">
                                @csrf
                                <button class="w-full bg-green-600 text-white py-2 rounded font-bold text-sm">Renovar
                                    suscripción</button>
                            </form>
                            <form action="{{ route('admin.user.mark_unpaid', $user) }}" method="POST">
                                @csrf
                                <button class="w-full bg-yellow-600 text-white py-2 rounded font-bold text-sm">Marcar
                                    impagado</button>
                            </form>
                        </div>
                    @else
                        <div class="border rounded-xl p-3 xl:col-span-3">
                            <p class="text-sm text-yellow-700">
                                Las acciones de cobro están desactivadas hasta ejecutar las migraciones pendientes.
                            </p>
                        </div>
                    @endif

                    <div class="border rounded-xl p-3 flex flex-col gap-2">
                        <p class="font-bold text-sm mb-1">Gestión de usuario</p>
                        <a href="{{ route('admin.user.edit', $user) }}"
                            class="text-center bg-blue-600 text-white py-2 rounded font-bold text-sm">Editar ficha</a>

                        <form action="{{ route('admin.user.delete', $user) }}" method="POST"
                            onsubmit="return confirm('¿Seguro que quieres eliminar este usuario?')">
                            @csrf
                            @method('DELETE')
                            <button class="w-full bg-red-600 text-white py-2 rounded font-bold text-sm">Eliminar
                                usuario</button>
                        </form>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</div>
@endsection
