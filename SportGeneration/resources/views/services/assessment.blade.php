{{-- Formulario de entrenador personal. --}}
@extends('layouts.app')

@section('titulo', 'Solicita tu sesión de valoración')

@section('contenido')
    {{-- Errores de validación o de envío del formulario. --}}
    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-6 shadow-sm">
            <p class="font-bold mb-1">Revisa los datos del formulario:</p>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Mensaje de éxito. --}}
    @if (session('exito'))
        <div class="bg-[#ADFE01] border-l-4 border-green-500 text-green-700 p-4 rounded-xl mb-6 shadow-sm flex items-center">
            <span class="material-symbols-outlined mr-2">check_circle</span>
            <p class="font-bold">{{ session('exito') }}</p>
        </div>
    @endif

    <div class="bg-[#F8F8F8] min-h-screen py-16 flex justify-center items-center font-display">
        <div class="bg-white p-12 rounded-[2.5rem] shadow-2xl border border-gray-100 max-w-2xl w-full text-center">

            {{-- Título --}}
            <h1 class="text-[#265E1F] text-5xl font-black mb-4 tracking-tighter">
                Solicita tu sesión de valoración
            </h1>
            <p class="text-gray-600 text-lg mb-10 leading-relaxed">
                Un entrenador personal se pondrá en contacto contigo en las próximas 24 horas.
            </p>

            <form action="{{ route('valoracion.enviar') }}" method="POST" class="text-left space-y-6">
                @csrf

                {{-- Datos. --}}
                <div>
                    <label class="block text-gray-900 font-bold mb-2 ml-1">Nombre completo</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Tu nombre y apellidos"
                        class="w-full p-4 rounded-xl border border-gray-200 focus:border-[#265E1F] focus:ring-2 focus:ring-[#265E1F]/20 outline-none transition-all">
                </div>

                <div>
                    <label class="block text-gray-900 font-bold mb-2 ml-1">Email de contacto</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="ejemplo@correo.com"
                        class="w-full p-4 rounded-xl border border-gray-200 focus:border-[#265E1F] focus:ring-2 focus:ring-[#265E1F]/20 outline-none transition-all">
                </div>

                {{-- Objetivo --}}
                <div>
                    <label class="block text-gray-900 font-bold mb-2 ml-1">Objetivo principal</label>
                    <select name="objetivo"
                        class="w-full p-4 rounded-xl border border-gray-200 focus:border-[#265E1F] outline-none appearance-none bg-no-repeat bg-right pr-10"
                        style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23666%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-position: right 1rem center; background-size: 1.5em;">
                        <option value="" disabled {{ old('objetivo') ? '' : 'selected' }}>Selecciona tu meta</option>
                        <option value="perder-peso" {{ old('objetivo') === 'perder-peso' ? 'selected' : '' }}>Perder peso
                        </option>
                        <option value="ganar-musculo" {{ old('objetivo') === 'ganar-musculo' ? 'selected' : '' }}>Ganar masa
                            muscular</option>
                        <option value="resistencia" {{ old('objetivo') === 'resistencia' ? 'selected' : '' }}>Mejorar
                            resistencia</option>
                        <option value="salud" {{ old('objetivo') === 'salud' ? 'selected' : '' }}>Salud y bienestar</option>
                    </select>
                </div>

                {{-- Mensaje opcional. --}}
                <div>
                    <label class="block text-gray-900 font-bold mb-2 ml-1">Mensaje opcional</label>
                    <textarea name="mensaje" rows="4"
                        placeholder="Cuéntanos más sobre tus necesidades o horarios preferidos."
                        class="w-full p-4 rounded-xl border border-gray-200 focus:border-[#265E1F] outline-none transition-all resize-none">{{ old('mensaje') }}</textarea>
                </div>

                {{-- Botón enviar. --}}
                <button type="submit"
                    class="w-full bg-[#265E1F] text-white font-black py-5 rounded-2xl hover:bg-[#265E1F] transition-all text-xl shadow-lg mt-4">
                    Enviar solicitud
                </button>
            </form>
        </div>
    </div>
@endsection
