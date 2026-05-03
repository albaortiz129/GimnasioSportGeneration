{{-- Solicitar email de recuperación de contraseña. --}}
@extends('layouts.app')

@section('titulo', 'Recuperar contraseña')

@section('contenido')
    <div class="min-h-[80vh] flex items-center justify-center bg-[#F8FAFC] px-4 py-12">
        <div class="max-w-md w-full">

            {{-- Tarjeta Principal --}}
            <div
                class="bg-white rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.04)] border border-gray-100 overflow-hidden">
                <div class="h-2 bg-[#1A3878]"></div>
                <div class="p-8 sm:p-12">
                    {{-- Título --}}
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 bg-[#1A3878]/5 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <span class="material-symbols-outlined text-[#1A3878] text-3xl">lock_reset</span>
                        </div>
                        <h1 class="text-2xl font-black text-[#0A1931] tracking-tight">¿Olvidaste tu clave?</h1>
                        <p class="text-gray-500 text-sm mt-2 leading-relaxed">
                            No te preocupes. Introduce tu email y te enviaremos un enlace para que crees una nueva.
                        </p>
                    </div>

                    {{-- Mensaje de éxito --}}
                    @if (session('status'))
                        <div
                            class="mb-8 p-4 bg-green-50 border border-green-100 rounded-2xl flex items-center gap-3 animate-fade-in">
                            <span class="material-symbols-outlined text-green-500 text-xl">check_circle</span>
                            <p class="text-green-700 text-xs font-bold uppercase tracking-wide">
                                {{ session('status') }}
                            </p>
                        </div>
                    @endif

                    {{-- Formulario --}}
                    <form action="{{ route('password.email') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label for="email"
                                class="block text-xs font-black text-[#0A1931] uppercase tracking-widest ml-1 mb-2">
                                Correo electrónico
                            </label>
                            <div class="relative">
                                <span
                                    class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl">email</span>
                                <input type="email" name="email" id="email" required
                                    class="w-full pl-12 pr-4 py-4 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-[#1A3878]/10 focus:border-[#1A3878] transition-all placeholder:text-gray-300 text-sm"
                                    placeholder="tu@email.com">
                            </div>
                            @error('email')
                                <p class="text-red-500 text-xs mt-2 ml-1 font-bold italic">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="w-full bg-[#1A3878] text-white py-4 rounded-xl font-black text-sm uppercase tracking-widest hover:bg-[#0A1931] hover:-translate-y-0.5 transition-all shadow-lg shadow-blue-900/10">
                            Enviar enlace
                        </button>
                    </form>

                    {{-- Volver --}}
                    <div class="mt-10 text-center">
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 text-sm font-bold text-gray-400 hover:text-[#1A3878] transition-colors group">
                            <span
                                class="material-symbols-outlined text-lg group-hover:-translate-x-1 transition-transform">arrow_back</span>
                            Volver al inicio de sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.4s ease-out forwards;
        }
    </style>
@endsection