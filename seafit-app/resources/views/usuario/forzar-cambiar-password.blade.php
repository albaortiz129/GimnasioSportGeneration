{{-- Vista obligatoria para cambiar la contrasena temporal en el primer acceso. --}}
@extends('moldes.inicio')

@section('titulo', 'Cambiar contrasena')

@section('contenido')
    <div class="max-w-lg mx-auto my-12 bg-white p-8 rounded-2xl shadow border">
        <h1 class="text-2xl font-bold mb-2 text-[#0A1931]">Cambia tu contrasena</h1>
        <p class="text-sm text-gray-500 mb-6">Es obligatorio en el primer acceso.</p>

        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('password.force.update') }}" method="POST" class="space-y-4">
            @csrf
            <input type="password" name="password" placeholder="Nueva contrasena" class="w-full border rounded-xl p-3"
                required>
            <input type="password" name="password_confirmation" placeholder="Confirmar contrasena"
                class="w-full border rounded-xl p-3" required>
            <button type="submit" class="w-full bg-[#0A1931] text-white py-3 rounded-xl font-bold">Guardar</button>
        </form>
    </div>
@endsection
