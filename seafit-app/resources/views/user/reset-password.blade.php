{{-- Vista para establecer una nueva contraseña mediante token. --}}
@extends('layouts.app')

@section('titulo', 'Crear Nueva Contraseña - SeaFit')

@section('contenido')
    <div class="min-h-[80vh] flex items-center justify-center bg-[#F8FAFC] px-4 py-12">
        <div class="max-w-md w-full">
            {{-- Tarjeta central del formulario de restablecimiento. --}}
            <div
                class="bg-white rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.04)] border border-gray-100 overflow-hidden">
                <div class="h-2 bg-[#1A3878]"></div>

                <div class="p-8 sm:p-12">
                    {{-- Cabecera visual del formulario. --}}
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 bg-[#1A3878]/5 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <span class="material-symbols-outlined text-[#1A3878] text-3xl">lock_open</span>
                        </div>
                        <h1 class="text-2xl font-black text-[#0A1931] tracking-tight">Nueva Contraseña</h1>
                        <p class="text-gray-500 text-sm mt-2 leading-relaxed">Configura tu nueva clave de acceso.</p>
                    </div>

                    {{-- Lista de errores de validacion del backend. --}}
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-2xl">
                            @foreach ($errors->all() as $error)
                                <p class="text-red-600 text-xs font-bold">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    {{-- Este token oculto lo valida el servidor antes de cambiar la contraseña --}}
                    <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        {{-- Campo email del usuario. --}}
                        <div>
                            <label
                                class="block text-xs font-black text-[#0A1931] uppercase tracking-widest ml-1 mb-2">Correo
                                Electrónico</label>
                            <input type="email" name="email" value="{{ request()->email }}" required
                                class="w-full px-4 py-4 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-[#1A3878]/10 focus:border-[#1A3878] transition-all text-sm">
                        </div>

                        {{-- Campo de nueva contraseña. --}}
                        <div>
                            <label class="block text-xs font-black text-[#0A1931] uppercase tracking-widest ml-1 mb-2">Nueva
                                Contraseña</label>
                            <input type="password" name="password" required
                                class="w-full px-4 py-4 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-[#1A3878]/10 focus:border-[#1A3878] transition-all text-sm">
                        </div>

                        {{-- Confirmacion de la nueva contraseña. --}}
                        <div>
                            <label
                                class="block text-xs font-black text-[#0A1931] uppercase tracking-widest ml-1 mb-2">Confirmar
                                Contraseña</label>
                            <input type="password" name="password_confirmation" required
                                class="w-full px-4 py-4 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-[#1A3878]/10 focus:border-[#1A3878] transition-all text-sm">
                        </div>

                        {{-- Boton final para guardar la nueva clave. --}}
                        <button type="submit"
                            class="w-full bg-[#1A3878] text-white py-4 rounded-xl font-black text-sm uppercase tracking-widest hover:bg-[#0A1931] transition-all shadow-lg shadow-blue-900/10">
                            Guardar Nueva Contraseña
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection