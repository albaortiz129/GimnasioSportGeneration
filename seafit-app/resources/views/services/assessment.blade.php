{{-- Vista de solicitud de valoracion para entrenador personal. --}}
@extends('layouts.app')

@section('titulo', 'Solicita tu Sesión de Valoración - SeaFit')

@section('contenido')
    {{-- Mensaje de éxito --}}
    @if (session('exito'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-xl mb-6 shadow-sm flex items-center">
            <span class="material-symbols-outlined mr-2">check_circle</span>
            <p class="font-bold">{{ session('exito') }}</p>
        </div>
    @endif

    <div class="bg-[#F8F8F8] min-h-screen py-16 flex justify-center items-center font-display">
        <div class="bg-white p-12 rounded-[2.5rem] shadow-2xl border border-gray-100 max-w-2xl w-full text-center">

            {{-- Título y subtítulo --}}
            <h1 class="text-[#0A3878] text-5xl font-black mb-4 tracking-tighter">
                Solicita tu Sesión de Valoración
            </h1>
            <p class="text-gray-600 text-lg mb-10 leading-relaxed">
                Un entrenador personal se pondrá en contacto contigo en las próximas 24 horas.
            </p>

            <form action="{{ route('valoracion.enviar') }}" method="POST" class="text-left space-y-6">
                @csrf

                {{-- Nombre Completo --}}
                <div>
                    <label class="block text-gray-900 font-bold mb-2 ml-1">Nombre Completo</label>
                    <input type="text" name="nombre" placeholder="Tu nombre y apellidos"
                        class="w-full p-4 rounded-xl border border-gray-200 focus:border-[#1A3878] focus:ring-2 focus:ring-[#1A3878]/20 outline-none transition-all">
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-gray-900 font-bold mb-2 ml-1">Email de Contacto</label>
                    <input type="email" name="email" placeholder="ejemplo@correo.com"
                        class="w-full p-4 rounded-xl border border-gray-200 focus:border-[#1A3878] focus:ring-2 focus:ring-[#1A3878]/20 outline-none transition-all">
                </div>

                {{-- Objetivo Principal --}}
                <div>
                    <label class="block text-gray-900 font-bold mb-2 ml-1">Objetivo Principal</label>
                    <select name="objetivo"
                        class="w-full p-4 rounded-xl border border-gray-200 focus:border-[#1A3878] outline-none appearance-none bg-no-repeat bg-right pr-10"
                        style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%23666%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-position: right 1rem center; background-size: 1.5em;">
                        <option value="" disabled selected>Selecciona tu meta</option>
                        <option value="perder-peso">Perder peso</option>
                        <option value="ganar-musculo">Ganar masa muscular</option>
                        <option value="resistencia">Mejorar resistencia</option>
                        <option value="salud">Salud y bienestar</option>
                    </select>
                </div>

                {{-- Mensaje Opcional --}}
                <div>
                    <label class="block text-gray-900 font-bold mb-2 ml-1">Mensaje (Opcional)</label>
                    <textarea name="mensaje" rows="4"
                        placeholder="Cuéntanos más sobre tus necesidades o horarios preferidos."
                        class="w-full p-4 rounded-xl border border-gray-200 focus:border-[#1A3878] outline-none transition-all resize-none"></textarea>
                </div>

                {{-- Botón Enviar --}}
                <button type="submit"
                    class="w-full bg-[#004A77] text-white font-black py-5 rounded-2xl hover:bg-[#003554] transition-all text-xl shadow-lg mt-4">
                    Enviar Solicitud
                </button>
            </form>
        </div>
    </div>
@endsection