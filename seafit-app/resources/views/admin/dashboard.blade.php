@extends('moldes.inicio')

@section('titulo', 'Panel de Administración - SeaFit')

@section('contenido')
<div class="container mx-auto px-4 py-8">
    {{-- Encabezado con mensajes de éxito/error --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tighter">Panel de Gestión</h1>
        <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-xs font-bold uppercase">Modo Administrador</span>
    </div>

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
        <table class="min-w-full leading-normal">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Usuario</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rol</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($usuarios as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-4">
                        <p class="text-gray-900 font-bold">{{ $user->nombre }} {{ $user->apellidos }}</p>
                        <p class="text-gray-500 text-xs uppercase">{{ $user->dni }}</p>
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-gray-600">{{ $user->email }}</p>
                    </td>
                    <td class="px-5 py-4">
                        @if($user->is_admin)
                            <span class="px-2 py-1 text-xs font-black bg-red-600 text-white rounded">ADMIN</span>
                        @else
                            <span class="px-2 py-1 text-xs font-bold bg-blue-100 text-blue-600 rounded">CLIENTE</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex justify-end gap-3">
                            {{-- Botón Editar --}}
                            <a href="{{ route('admin.user.edit', $user->id) }}" 
                               class="text-indigo-600 hover:text-indigo-900 text-sm font-bold">
                               Editar
                            </a>

                            {{-- Botón Eliminar --}}
                            @if(!$user->is_admin)
                                <form action="{{ route('admin.user.delete', $user->id) }}" method="POST" 
                                      onsubmit="return confirm('¿Estás seguro de que quieres eliminar a {{ $user->nombre }}?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-bold">
                                        Eliminar
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection