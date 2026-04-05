{{-- Vista de gestion de pago: suscripcion, tarjetas y facturas. --}}
@extends('moldes.inicio')

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
                        ❌ {{ session('error') }}
                    </div>
                @endif

                <p class="text-gray-500 text-[15px]">Administra tu suscripción, métodos de pago y revisa tu historial de
                    facturas.</p>
            </header>

            {{-- RESUMEN DE FACTURACIÓN --}}
            @php
                $suscripcion = $user->subscription('default');
                $fechaCobro = 'N/A';

                if ($suscripcion) {
                    if ($suscripcion->active() && !$suscripcion->onGracePeriod()) {
                        $stripeSub = $suscripcion->asStripeSubscription();
                        $timestamp = $stripeSub->current_period_end;

                        // Si el periodo termina hoy mismo (recién pagado), 
                        // mostramos la fecha del mes/año que viene según el plan
                        if (date('d/m/Y', $timestamp) == date('d/m/Y')) {
                            $fecha = now();
                            $proxima = match ($user->tarifa) {
                                'mensual' => $fecha->addMonth(),
                                'trimestral' => $fecha->addMonths(3),
                                'anual' => $fecha->addYear(),
                                default => $fecha->addMonth()
                            };
                            $fechaCobro = $proxima->format('d/m/Y');
                        } else {
                            $fechaCobro = date('d/m/Y', $timestamp);
                        }
                    } elseif ($suscripcion->onGracePeriod()) {
                        $fechaCobro = $suscripcion->ends_at->format('d/m/Y');
                    }
                }
            @endphp

            <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-[#0A1931]">Resumen de Facturación</h3>
                </div>

                @if($user->tarifa != 'cancelada' && $suscripcion)
                    <div class="bg-[#f8fafc] border border-gray-200 rounded-xl p-6 relative">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <div>
                                <h4 class="m-0 text-[#0A1931] font-extrabold text-lg">Próximo cobro (Membresía Total)</h4>
                                <p class="m-0 mt-1 text-gray-500 text-sm">Se cargará automáticamente a tu método principal.</p>
                            </div>
                            <div class="text-left sm:text-right">
                                <span class="block text-2xl sm:text-3xl font-black text-[#1A3878]">
                                    {{ $user->tarifa == 'anual' ? '250,00€' : ($user->tarifa == 'trimestral' ? '79,99€' : '29,99€') }}
                                </span>
                                <span class="text-xs text-gray-400 font-medium uppercase tracking-wider">Fecha:
                                    {{ $fechaCobro }}</span>
                            </div>
                        </div>

                        <div
                            class="mt-6 pt-5 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <span class="text-sm text-gray-500">Plan actual: <strong class="text-[#0A1931]">Acceso Total
                                    {{ ucfirst($user->tarifa) }}</strong></span>

                            <form action="{{ route('plan.cancelar') }}" method="POST"
                                onsubmit="return confirm('¿Estás seguro de que deseas cancelar tu suscripción?')" class="m-0">
                                @csrf
                                <button type="submit"
                                    class="bg-transparent border-none text-[#1A3878] font-bold cursor-pointer flex items-center gap-1.5 p-0 text-sm hover:text-red-500 transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">cancel</span> Cancelar suscripción
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
                        <span class="material-symbols-outlined text-[40px] text-red-500 mb-2">cancel</span>
                        <h4 class="m-0 text-red-800 font-bold text-lg">Suscripción Inactiva</h4>
                        <p class="m-0 mt-1 text-red-600">No tienes una suscripción activa. Puedes reactivarla desde tu Perfil.
                        </p>
                    </div>
                @endif
            </section>

            {{-- MÉTODOS DE PAGO GUARDADOS --}}
            <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-[#0A1931]">Métodos de Pago Guardados</h3>
                </div>

                @foreach($metodosPago as $metodo)
                    @php $esPrincipal = ($metodoPrincipal && $metodo->id === $metodoPrincipal->id); @endphp

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
                                        class="text-[#1A3878] bg-transparent border-none font-bold text-sm cursor-pointer p-0 hover:underline">Establecer
                                        Principal</button>
                                </form>

                                <form action="{{ route('pago.eliminar') }}" method="POST"
                                    onsubmit="return confirm('¿Seguro que quieres borrar este método de pago?')" class="m-0">
                                    @csrf @method('DELETE')
                                    <input type="hidden" name="payment_method" value="{{ $metodo->id }}">
                                    <button type="submit"
                                        class="text-red-500 bg-transparent border-none font-bold text-sm cursor-pointer p-0 hover:text-red-700 hover:underline">Eliminar</button>
                                </form>
                            @else
                                <span class="bg-green-200 text-green-800 px-3 py-1 rounded-full text-xs font-bold">Método
                                    Principal</span>
                            @endif
                        </div>
                    </div>
                @endforeach

                <a href="{{ route('pago.nuevo') }}"
                    class="inline-flex items-center gap-2 text-[#1A3878] font-bold text-sm transition-colors hover:text-[#0A1931] mt-4">
                    <span class="material-symbols-outlined">add_circle</span> Añadir Nuevo Método de Pago
                </a>
            </section>

            {{-- HISTORIAL DE FACTURAS --}}
            <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
                <div class="border-b border-gray-100 pb-4 mb-5">
                    <h3 class="text-xl font-bold text-[#0A1931]">Historial de Facturas</h3>
                </div>

                <div class="flex flex-col gap-5">
                    @forelse($user->invoices() as $factura)
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
            </section>
        </main>
    </div>
@endsection