{{-- Configuración del usuario. --}}
@extends('layouts.app')

@section('titulo', 'Configuración')

@section('contenido')
    <div class="flex flex-col md:flex-row min-h-screen bg-[#f8fafc] font-sans">
        {{-- Barra lateral del panel de socio. --}}
        <aside
            class="w-full md:w-[280px] md:min-w-[280px] bg-white p-6 md:p-8 border-b md:border-b-0 md:border-r border-gray-200">
            <h2 class="text-xl font-extrabold text-[#265e1f] mb-8">Panel de socio</h2>
            <nav class="flex flex-col gap-2">
                <a href="{{ route('perfil') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#265e1f]">
                    <span class="material-symbols-outlined">person</span> Mi perfil
                </a>
                <a href="{{ route('mis.reservas') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#265e1f]">
                    <span class="material-symbols-outlined">calendar_month</span> Mis reservas
                </a>
                <a href="{{ route('pago.gestion') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#265e1f]">
                    <span class="material-symbols-outlined">payments</span> Gestión de pago
                </a>
                <a href="{{ route('configuracion') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors bg-[#e6f3ff] text-[#265e1f]">
                    <span class="material-symbols-outlined">settings</span> Configuración
                </a>
            </nav>
        </aside>

        {{-- Contenido. --}}
        <main class="flex-1 p-6 md:p-10 lg:p-12 max-w-[1000px]">
            @if(session('success'))
                <div
                    class="bg-[#c6ff7c] text-green-800 p-4 rounded-xl mb-6 border border-[#c6ff7c] font-medium flex items-center gap-3">
                    <span class="material-symbols-outlined">check_circle</span>
                    <strong>{{ session('success') }}</strong>
                </div>
            @endif

            @if(session('error'))
                <div
                    class="bg-red-100 text-red-800 p-4 rounded-xl mb-6 border border-red-200 font-medium flex items-center gap-3">
                    <span class="material-symbols-outlined">error</span>
                    <strong>{{ session('error') }}</strong>
                </div>
            @endif

            <header class="mb-8">
                <h1 class="text-3xl md:text-4xl font-black text-[#265e1f] mb-2">Configuración</h1>
                <p class="text-gray-500 text-[15px]">
                    Desde aquí puedes cambiar tus datos, actualizar la contraseña y cancelar tu suscripción.
                </p>
            </header>

            @php
                // Variables para mostrar el estado de la suscripción en el bloque de cancelación.
                $planActivo = $user->isPlanActive();
                $fechaHasta = optional($user->next_payment_at)->format('d/m/Y') ?? 'Sin fecha';
                $suscripcion = $user->subscription('default');
                $enPeriodoCancelacion = $suscripcion ? $suscripcion->onGracePeriod() : false;
                $cancelacionManualProgramada = !$suscripcion && $user->tarifa === 'cancelada' && $planActivo;
                $puedeCancelar = $suscripcion
                    ? !$suscripcion->canceled()
                    : ($planActivo && $user->tarifa !== 'cancelada');
            @endphp

            {{-- Datos del formulario de registro editables por el socio. --}}
            <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
                <h3 class="text-xl font-bold text-[#265e1f] mb-6">Datos de cuenta</h3>

                <form action="{{ route('configuracion.actualizar') }}" method="POST" novalidate>
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="cfg_nombre" class="block text-sm text-gray-500 font-semibold mb-1">Nombre</label>
                            <input id="cfg_nombre" type="text" name="nombre" value="{{ old('nombre', $user->nombre) }}"
                                class="w-full p-3 border border-gray-300 rounded-xl text-[#265e1f] outline-none focus:ring-2 focus:ring-[#265e1f] @error('nombre') border-red-500 bg-red-50 @enderror">
                            @error('nombre')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cfg_apellidos"
                                class="block text-sm text-gray-500 font-semibold mb-1">Apellidos</label>
                            <input id="cfg_apellidos" type="text" name="apellidos"
                                value="{{ old('apellidos', $user->apellidos) }}"
                                class="w-full p-3 border border-gray-300 rounded-xl text-[#265e1f] outline-none focus:ring-2 focus:ring-[#265e1f] @error('apellidos') border-red-500 bg-red-50 @enderror">
                            @error('apellidos')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cfg_email" class="block text-sm text-gray-500 font-semibold mb-1">Email</label>
                            <input id="cfg_email" type="email" name="email" value="{{ old('email', $user->email) }}"
                                class="w-full p-3 border border-gray-300 rounded-xl text-[#265e1f] outline-none focus:ring-2 focus:ring-[#265e1f] @error('email') border-red-500 bg-red-50 @enderror">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cfg_dni" class="block text-sm text-gray-500 font-semibold mb-1">DNI</label>
                            <input id="cfg_dni" type="text" name="dni" maxlength="9"
                                oninput="this.value=this.value.toUpperCase()" value="{{ old('dni', $user->dni) }}"
                                class="w-full p-3 border border-gray-300 rounded-xl text-[#265e1f] outline-none focus:ring-2 focus:ring-[#265e1f] @error('dni') border-red-500 bg-red-50 @enderror">
                            @error('dni')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cfg_fecha_nacimiento" class="block text-sm text-gray-500 font-semibold mb-1">Fecha
                                de nacimiento</label>
                            <input id="cfg_fecha_nacimiento" type="date" name="fecha_nacimiento"
                                value="{{ old('fecha_nacimiento', $user->fecha_nacimiento ? \Illuminate\Support\Carbon::parse($user->fecha_nacimiento)->format('Y-m-d') : '') }}"
                                class="w-full p-3 border border-gray-300 rounded-xl text-[#265e1f] outline-none focus:ring-2 focus:ring-[#265e1f] @error('fecha_nacimiento') border-red-500 bg-red-50 @enderror">
                            @error('fecha_nacimiento')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cfg_telefono"
                                class="block text-sm text-gray-500 font-semibold mb-1">Teléfono</label>
                            <input id="cfg_telefono" type="text" name="telefono" maxlength="9"
                                value="{{ old('telefono', $user->telefono) }}"
                                class="w-full p-3 border border-gray-300 rounded-xl text-[#265e1f] outline-none focus:ring-2 focus:ring-[#265e1f] @error('telefono') border-red-500 bg-red-50 @enderror">
                            @error('telefono')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="cfg_domicilio"
                                class="block text-sm text-gray-500 font-semibold mb-1">Domicilio</label>
                            <input id="cfg_domicilio" type="text" name="domicilio"
                                value="{{ old('domicilio', $user->domicilio) }}"
                                class="w-full p-3 border border-gray-300 rounded-xl text-[#265e1f] outline-none focus:ring-2 focus:ring-[#265e1f] @error('domicilio') border-red-500 bg-red-50 @enderror">
                            @error('domicilio')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit"
                            class="bg-[#265e1f] text-white px-6 py-3 rounded-xl font-bold hover:bg-[#265e1f] transition-colors">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </section>

            {{-- Cambio de contraseña. --}}
            <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
                <h3 class="text-xl font-bold text-[#265e1f] mb-6">Seguridad</h3>

                @if($errors->has('password_actual') || $errors->has('password') || $errors->has('password_confirmation'))
                    <div class="bg-red-50 text-red-700 p-3 rounded-xl text-sm mb-4 border border-red-100">
                        {{ $errors->first('password_actual') ?? $errors->first('password') ?? $errors->first('password_confirmation') }}
                    </div>
                @endif

                <form action="{{ route('perfil.password') }}" method="POST" class="grid grid-cols-1 gap-4 max-w-md">
                    @csrf
                    <input type="password" name="password_actual" placeholder="Contraseña actual"
                        class="w-full p-3 border rounded-xl bg-gray-50 outline-none focus:ring-1 focus:ring-[#265e1f]"
                        required>
                    <input type="password" name="password" placeholder="Nueva contraseña"
                        class="w-full p-3 border rounded-xl bg-gray-50 outline-none focus:ring-1 focus:ring-[#265e1f]"
                        required>
                    <input type="password" name="password_confirmation" placeholder="Confirmar nueva contraseña"
                        class="w-full p-3 border rounded-xl bg-gray-50 outline-none focus:ring-1 focus:ring-[#265e1f]"
                        required>
                    <button type="submit"
                        class="bg-[#265e1f] text-white py-3 rounded-xl font-bold hover:bg-[#265e1f] transition-colors shadow-sm">
                        Actualizar contraseña
                    </button>
                </form>
            </section>

            {{-- Cancelación de suscripción. --}}
            <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
                <h3 class="text-xl font-bold text-[#265e1f] mb-4">Suscripción</h3>

                @if($enPeriodoCancelacion || $cancelacionManualProgramada)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <p class="font-bold text-amber-800">Cancelación ya programada</p>
                        <p class="text-sm text-amber-700 mt-1">
                            Tu acceso seguirá activo hasta el {{ $fechaHasta }}.
                        </p>
                    </div>
                @elseif($puedeCancelar)
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <p class="font-bold text-[#265e1f]">Cancelar suscripción al final del período</p>
                            <p class="text-sm text-gray-500 mt-1">
                                Mantendrás el acceso hasta el {{ $fechaHasta }} y después no se harán más cobros.
                            </p>
                        </div>
                        <form action="{{ route('plan.cancelar') }}" method="POST"
                            onsubmit="return confirm('Se cancelará al final del período actual. ¿Continuar?')">
                            @csrf
                            <button type="submit"
                                class="bg-red-600 text-white px-5 py-3 rounded-xl font-bold hover:bg-red-700 transition-colors">
                                Cancelar suscripción
                            </button>
                        </form>
                    </div>
                @else
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <p class="font-bold text-gray-800">No hay una suscripción activa para cancelar</p>
                    </div>
                @endif
            </section>
        </main>
    </div>
@endsection