{{-- Vista de edicion de datos de un usuario desde el panel admin. --}}
@extends('moldes.inicio')

@section('contenido')
    <div class="max-w-2xl mx-auto py-10">
        <h1 class="text-2xl font-bold mb-5">Editar Usuario: {{ $user->nombre }}</h1>

        {{--
        Formulario para cambios basicos del usuario.
        Campos permitidos: nombre y email.
        --}}
        <form action="{{ route('admin.user.update', $user->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-bold">Nombre</label>
                <input type="text" name="nombre" value="{{ $user->nombre }}" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block font-bold">Email</label>
                <input type="email" name="email" value="{{ $user->email }}" class="w-full border rounded p-2">
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Guardar Cambios</button>
                <a href="{{ route('admin.dashboard') }}" class="text-gray-600 py-2">Cancelar</a>
            </div>
        </form>
    </div>
@endsection