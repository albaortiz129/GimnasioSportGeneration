{{-- Vista de edición de datos de un usuario desde el panel admin. --}}
@extends('layouts.app')

@section('contenido')
    <div class="max-w-4xl mx-auto py-10 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Editar usuario: {{ $user->nombre }} {{ $user->apellidos }}</h1>
            <a href="{{ route('admin.dashboard') }}" class="text-[#1A3878] font-bold">Volver al panel</a>
        </div>

        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Formulario de edición completa de la ficha del cliente. --}}
        <form action="{{ route('admin.user.update', $user->id) }}" method="POST"
            class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white border rounded-2xl p-6">
            @csrf
            @method('PUT')

            {{-- Datos personales. --}}
            <div>
                <label class="block font-bold">Nombre</label>
                <input type="text" name="nombre" value="{{ old('nombre', $user->nombre) }}"
                    class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block font-bold">Apellidos</label>
                <input type="text" name="apellidos" value="{{ old('apellidos', $user->apellidos) }}"
                    class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block font-bold">DNI</label>
                <input type="text" name="dni" value="{{ old('dni', $user->dni) }}" class="w-full border rounded p-2"
                    required>
            </div>

            <div>
                <label class="block font-bold">Fecha de nacimiento</label>
                <input type="date" name="fecha_nacimiento"
                    value="{{ old('fecha_nacimiento', optional($user->fecha_nacimiento)->format('Y-m-d') ?? $user->fecha_nacimiento) }}"
                    class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block font-bold">Teléfono</label>
                <input type="text" name="telefono" value="{{ old('telefono', $user->telefono) }}"
                    class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block font-bold">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border rounded p-2"
                    required>
            </div>

            <div class="md:col-span-2">
                <label class="block font-bold">Domicilio</label>
                <input type="text" name="domicilio" value="{{ old('domicilio', $user->domicilio) }}"
                    class="w-full border rounded p-2" required>
            </div>

            <div>
                <label class="block font-bold">Nueva contraseña (opcional)</label>
                <input type="password" name="password" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block font-bold">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" class="w-full border rounded p-2">
            </div>


            {{-- Datos de plan y estado de cobro. --}}
            <div>
                <label class="block font-bold">Tarifa</label>
                <select name="tarifa" class="w-full border rounded p-2" required>
                    @foreach(['mensual', 'trimestral', 'anual', 'cancelada'] as $tarifa)
                        <option value="{{ $tarifa }}" @selected(old('tarifa', $user->tarifa) === $tarifa)>
                            {{ ucfirst($tarifa) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold">Método de pago</label>
                @php
                    $metodoPagoActual = strtolower((string) old('metodo_pago', $user->metodo_pago));
                @endphp
                <select name="metodo_pago" class="w-full border rounded p-2" required>
                    <option value="visa" @selected($metodoPagoActual === 'visa')>Visa</option>
                    <option value="efectivo" @selected($metodoPagoActual === 'efectivo')>Efectivo</option>
                </select>
            </div>

            <div>
                <label class="block font-bold">Estado de pago</label>
                <select name="payment_status" class="w-full border rounded p-2" required>
                    @foreach(['al_dia', 'pendiente', 'impagado'] as $estado)
                        <option value="{{ $estado }}" @selected(old('payment_status', $user->payment_status) === $estado)>
                            {{ str_replace('_', ' ', ucfirst($estado)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold">Próximo cobro</label>
                <input type="date" name="next_payment_at"
                    value="{{ old('next_payment_at', optional($user->next_payment_at)->format('Y-m-d')) }}"
                    class="w-full border rounded p-2">
            </div>

            {{-- Acciones finales del formulario. --}}
            <div class="flex gap-4 md:col-span-2 pt-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Guardar Cambios</button>
                <a href="{{ route('admin.dashboard') }}" class="text-gray-600 py-2">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
