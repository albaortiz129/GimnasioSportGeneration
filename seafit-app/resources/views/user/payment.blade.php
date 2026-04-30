{{-- Vista de gestión de pago: suscripción, tarjetas y facturas. --}}
@extends('layouts.app')

@section('titulo', 'Gestión de Pago - SeaFit')

@section('contenido')
    <div class="flex flex-col md:flex-row min-h-screen bg-[#f8fafc] font-sans">

        {{-- BARRA LATERAL --}}
        <aside
            class="w-full md:w-[280px] md:min-w-[280px] bg-white p-6 md:p-8 border-b md:border-b-0 md:border-r border-gray-200">
            <h2 class="text-xl font-extrabold text-[#0A1931] mb-8">Panel de Socio</h2>
            <nav class="flex flex-col gap-2">
                <a href="{{ route('perfil') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#0A1931]">
                    <span class="material-symbols-outlined">person</span> Mi Perfil
                </a>
                <a href="{{ route('mis.reservas') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#0A1931]">
                    <span class="material-symbols-outlined">calendar_month</span> Mis Reservas
                </a>
                <a href="{{ route('pago.gestion') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors bg-[#e6f3ff] text-[#1A3878]">
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
            <header class="mb-8">
                <h1 class="text-3xl md:text-4xl font-black text-[#0A1931] mb-4">Gestión de Pago</h1>

                @if(session('success'))
                    <div
                        class="bg-green-100 text-green-800 p-4 rounded-xl mb-4 border border-green-200 font-medium flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">check_circle</span> {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-100 text-red-800 p-4 rounded-xl mb-4 border border-red-200 font-medium">
                        <span class="material-symbols-outlined text-[20px]">error</span> {{ session('error') }}
                    </div>
                @endif

                <p class="text-gray-500 text-[15px]">Administra tu suscripción, métodos de pago y revisa tu historial de
                    facturas.</p>
            </header>

            {{-- RESUMEN DE FACTURACIÓN --}}
            @php
                // Variables auxiliares para estado de cuenta.
                $planActivo = $user->isPlanActive();
                $fechaCobro = optional($user->next_payment_at)->format('d/m/Y') ?? 'Pendiente';
            @endphp


            <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-[#0A1931]">Resumen de Facturación</h3>
                </div>

                @if($planActivo)
                    <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                        <p class="font-bold text-green-800">Suscripción activa</p>
                        <p class="text-sm text-green-700 mt-1">
                            Plan: {{ $user->tarifa === 'cancelada' ? 'Cancelación programada' : ucfirst($user->tarifa) }} |
                            Método: {{ ucfirst($user->metodo_pago ?? 'sin definir') }}
                        </p>
                        <p class="text-sm text-green-700 mt-1">
                            Próximo cobro: {{ $fechaCobro }}
                        </p>
                    </div>
                @elseif($user->payment_status === 'pendiente')
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <p class="font-bold text-amber-800">Pago pendiente de validación</p>
                        <p class="text-sm text-amber-700 mt-1">
                            Tu plan se activará cuando el administrador confirme el pago manual.
                        </p>
                        <p class="text-sm text-amber-700 mt-1">
                            Método seleccionado: {{ ucfirst($user->metodo_pago ?? 'sin definir') }}
                        </p>
                    </div>
                @elseif($user->payment_status === 'impagado')
                    <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                        <p class="font-bold text-red-800">Suscripción impagada</p>
                        <p class="text-sm text-red-700 mt-1">
                            Hay un pago pendiente. Revisa tu método de pago o solicita renovación.
                        </p>
                    </div>
                @else
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="font-bold text-gray-800">Suscripción inactiva</p>
                        <p class="text-sm text-gray-600 mt-1">
                            No tienes una suscripción activa en este momento.
                        </p>
                    </div>
                @endif

            </section>

            {{-- MÉTODOS DE PAGO GUARDADOS --}}
            <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-[#0A1931]">Métodos de Pago Guardados</h3>
                </div>
                @php
                    // Si el principal es manual, no se marca ninguna tarjeta como principal.
                    $principalEsManual = in_array($user->metodo_pago, ['efectivo'], true);
                @endphp

                {{-- Tarjetas guardadas en Stripe. --}}
                @foreach($metodosPago as $metodo)
                    @php $esPrincipal = !$principalEsManual && ($metodoPrincipal && $metodo->id === $metodoPrincipal->id); @endphp

                    <div
                        class="border {{ $esPrincipal ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-[#f8fafc]' }} rounded-xl p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                        <div class="flex items-center gap-4">
                            <span class="material-symbols-outlined text-[32px] text-[#0A1931]">credit_card</span>
                            <div>
                                <h4 class="m-0 text-[#0A1931] font-bold text-lg">{{ ucfirst($metodo->card->brand) }} ****
                                    {{ $metodo->card->last4 }}
                                </h4>
                                <p
                                    class="m-0 text-xs font-bold {{ $esPrincipal ? 'text-green-700' : 'text-gray-500' }} mt-1 uppercase tracking-wider">
                                    {{ $esPrincipal ? 'Principal' : 'Secundario' }} | Exp.
                                    {{ $metodo->card->exp_month }}/{{ $metodo->card->exp_year }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-5 border-t sm:border-t-0 border-gray-200 w-full sm:w-auto pt-4 sm:pt-0 mt-2 sm:mt-0">
                            @if(!$esPrincipal)
                                <form action="{{ route('pago.principal') }}" method="POST" class="m-0">
                                    @csrf
                                    <input type="hidden" name="payment_method" value="{{ $metodo->id }}">
                                    <button type="submit"
                                        class="text-[#1A3878] bg-transparent border-none font-bold text-sm cursor-pointer p-0 hover:underline">
                                        Establecer Principal
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('pago.eliminar') }}" method="POST" class="m-0">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="payment_method" value="{{ $metodo->id }}">
                                <button type="submit"
                                    class="text-red-500 bg-transparent border-none font-bold text-sm cursor-pointer p-0 hover:text-red-700 hover:underline">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach

                {{-- Métodos manuales guardados en la base de datos. --}}
                @foreach($metodosManuales as $manual)
                    @php $esPrincipalManual = $principalEsManual && ($user->metodo_pago === $manual['code']); @endphp

                    <div
                        class="border {{ $esPrincipalManual ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-[#f8fafc]' }} rounded-xl p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                        <div class="flex items-center gap-4">
                            <span class="material-symbols-outlined text-[32px] text-[#0A1931]">payments</span>
                            <div>
                                <h4 class="m-0 text-[#0A1931] font-bold text-lg">{{ $manual['label'] }}</h4>
                                <p
                                    class="m-0 text-xs font-bold {{ $esPrincipalManual ? 'text-green-700' : 'text-gray-500' }} mt-1 uppercase tracking-wider">
                                    {{ $esPrincipalManual ? 'Principal' : 'Manual guardado' }}
                                </p>
                                @if(!empty($manual['value_masked']))
                                    <p class="m-0 text-sm text-gray-600 mt-1">
                                        Dato: {{ $manual['value_masked'] }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-5 border-t sm:border-t-0 border-gray-200 w-full sm:w-auto pt-4 sm:pt-0 mt-2 sm:mt-0">
                            @if(!$esPrincipalManual)
                                <form action="{{ route('pago.principal_manual') }}" method="POST" class="m-0">
                                    @csrf
                                    <input type="hidden" name="metodo_manual" value="{{ $manual['code'] }}">
                                    <button type="submit"
                                        class="text-[#1A3878] bg-transparent border-none font-bold text-sm cursor-pointer p-0 hover:underline">
                                        Establecer Principal
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('pago.eliminar_manual') }}" method="POST" class="m-0">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="metodo_manual" value="{{ $manual['code'] }}">
                                <button type="submit"
                                    class="text-red-500 bg-transparent border-none font-bold text-sm cursor-pointer p-0 hover:text-red-700 hover:underline">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach

                @if($metodosPago->isEmpty() && $metodosManuales->isEmpty())
                    <p class="text-gray-500 text-sm">No tienes métodos guardados todavía.</p>
                @endif

                {{-- Alta sencilla del método manual (solo Efectivo). --}}
                <form action="{{ route('pago.guardar_manual') }}" method="POST"
                    class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3" id="form-metodo-manual">
                    @csrf
                    <div>
                        <select name="metodo_manual" id="metodo_manual" class="border rounded p-3 w-full" required>
                            <option value="">Guardar método manual...</option>
                            <option value="efectivo" {{ old('metodo_manual') === 'efectivo' ? 'selected' : '' }}>Efectivo
                            </option>
                        </select>
                        @error('metodo_manual')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="bg-[#0A1931] text-white rounded p-3 font-bold h-fit">
                        Guardar método
                    </button>
                </form>

                <a href="{{ route('pago.nuevo') }}"
                    class="inline-flex items-center gap-2 text-[#1A3878] font-bold text-sm transition-colors hover:text-[#0A1931] mt-4">
                    <span class="material-symbols-outlined">add_circle</span> Añadir tarjeta
                </a>
            </section>

            {{-- HISTORIAL DE FACTURAS --}}
            <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
                <div class="border-b border-gray-100 pb-4 mb-5">
                    <h3 class="text-xl font-bold text-[#0A1931]">Historial de Facturas</h3>
                </div>

                <div class="flex flex-col gap-5">
                    @forelse($user->invoices() as $factura)
                        {{-- Fila de factura generada por Stripe. --}}
                        <div
                            class="flex justify-between items-center bg-gray-50 p-4 rounded-xl border border-gray-100 hover:border-gray-300 transition-colors">
                            <div>
                                <h4 class="m-0 text-[#0A1931] font-bold">Factura {{ $factura->date()->format('M Y') }}</h4>
                                <p class="m-0 mt-1 text-sm text-gray-500">{{ $factura->date()->format('d/m/Y') }} | Pago
                                    Completado</p>
                            </div>
                            <div class="flex items-center gap-4 sm:gap-6">
                                <span class="font-black text-[#0A1931] text-lg">{{ $factura->total() }}</span>

                                <a href="{{ route('factura.descargar', $factura->id) }}"
                                    class="text-[#1A3878] font-bold text-sm flex items-center gap-1.5 hover:text-[#0A1931] transition-colors bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm">
                                    PDF <span class="material-symbols-outlined text-[16px]">download</span>
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">Aún no tienes facturas disponibles.</p>
                    @endforelse
                </div>
                {{-- Cambio conjunto de tarifa + método desde perfil socio. --}}
                <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
                    <h3 class="text-xl font-bold text-[#0A1931] mb-4">Cambiar plan y método de pago</h3>

                    <form action="{{ route('pago.cambiar_plan_metodo') }}" method="POST"
                        class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @csrf
                        <select name="tarifa" class="border rounded p-3" required>
                            <option value="mensual">Mensual</option>
                            <option value="trimestral">Trimestral</option>
                            <option value="anual">Anual</option>
                        </select>

                        {{-- En este selector solo se muestran los métodos activos. --}}
                        <select name="metodo_pago" class="border rounded p-3" required>
                            <option value="visa">Visa</option>
                            <option value="efectivo">Efectivo</option>
                        </select>

                        <button class="bg-[#0A1931] text-white rounded p-3 font-bold">Actualizar</button>
                    </form>
                </section>

            </section>
        </main>
    </div>
@endsection
