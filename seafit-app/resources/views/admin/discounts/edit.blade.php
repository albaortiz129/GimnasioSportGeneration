{{-- Vista de admin para editar un codigo de descuento. --}}
@extends('layouts.app')

@section('titulo', 'Editar descuento - Admin')

@section('contenido')
    <div class="max-w-4xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-black text-[#0A1931] mb-6">Editar codigo: {{ $discountCode->code }}</h1>

        {{-- Error general de validacion devuelto por backend. --}}
        @if($errors->any())
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800 border border-red-200">
                Revisa los campos marcados.
            </div>
        @endif

        {{-- Formulario de edicion del codigo seleccionado. --}}
        <form action="{{ route('admin.discounts.update', $discountCode) }}" method="POST"
            class="bg-white border rounded-2xl p-6 space-y-4">
            @csrf
            @method('PUT')

            {{-- Codigo editable del cupon. --}}
            <div>
                <label class="block text-sm font-bold mb-1">Codigo</label>
                <input type="text" name="code" value="{{ old('code', $discountCode->code) }}"
                    class="w-full border rounded p-3 @error('code') border-red-500 @enderror" required>
                @error('code') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Tipo y valor del descuento. --}}
                <div>
                    <label class="block text-sm font-bold mb-1">Tipo</label>
                    <select name="type" class="w-full border rounded p-3 @error('type') border-red-500 @enderror" required>
                        <option value="percent" @selected(old('type', $discountCode->type) === 'percent')>Porcentaje</option>
                        <option value="fixed" @selected(old('type', $discountCode->type) === 'fixed')>Importe fijo</option>
                    </select>
                    @error('type') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold mb-1">Valor</label>
                    <input type="number" step="0.01" min="0.01" name="value"
                        value="{{ old('value', $discountCode->value) }}"
                        class="w-full border rounded p-3 @error('value') border-red-500 @enderror" required>
                    @error('value') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Rango opcional de fechas de activacion/caducidad. --}}
                <div>
                    <label class="block text-sm font-bold mb-1">Inicio (opcional)</label>
                    <input type="datetime-local" name="starts_at"
                        value="{{ old('starts_at', optional($discountCode->starts_at)->format('Y-m-d\TH:i')) }}"
                        class="w-full border rounded p-3 @error('starts_at') border-red-500 @enderror">
                    @error('starts_at') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold mb-1">Fin (opcional)</label>
                    <input type="datetime-local" name="ends_at"
                        value="{{ old('ends_at', optional($discountCode->ends_at)->format('Y-m-d\TH:i')) }}"
                        class="w-full border rounded p-3 @error('ends_at') border-red-500 @enderror">
                    @error('ends_at') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                {{-- Limite global de usos (si se quiere restringir). --}}
                <label class="block text-sm font-bold mb-1">Maximo de usos (opcional)</label>
                <input type="number" min="1" name="max_uses" value="{{ old('max_uses', $discountCode->max_uses) }}"
                    class="w-full border rounded p-3 @error('max_uses') border-red-500 @enderror">
                @error('max_uses') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                {{-- Vinculacion opcional con cupon de Stripe. --}}
                <label class="block text-sm font-bold mb-1">Stripe coupon id (opcional)</label>
                <input type="text" name="stripe_coupon_id"
                    value="{{ old('stripe_coupon_id', $discountCode->stripe_coupon_id) }}"
                    class="w-full border rounded p-3 @error('stripe_coupon_id') border-red-500 @enderror">
                @error('stripe_coupon_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                {{-- Notas internas para administracion. --}}
                <label class="block text-sm font-bold mb-1">Notas (opcional)</label>
                <textarea name="notes" rows="3"
                    class="w-full border rounded p-3 @error('notes') border-red-500 @enderror">{{ old('notes', $discountCode->notes) }}</textarea>
                @error('notes') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-wrap items-center gap-6">
                {{-- Opciones de estado y limite por usuario. --}}
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $discountCode->is_active))>
                    <span class="text-sm font-medium">Activo</span>
                </label>

                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="one_use_per_user" value="1" @checked(old('one_use_per_user', $discountCode->one_use_per_user))>
                    <span class="text-sm font-medium">1 uso por usuario</span>
                </label>
            </div>

            <div class="pt-2 flex gap-3">
                {{-- Acciones finales del formulario. --}}
                <button class="bg-[#0A1931] text-white px-4 py-2 rounded font-bold">Guardar cambios</button>
                <a href="{{ route('admin.discounts.index') }}"
                    class="bg-gray-200 text-gray-800 px-4 py-2 rounded font-bold">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
