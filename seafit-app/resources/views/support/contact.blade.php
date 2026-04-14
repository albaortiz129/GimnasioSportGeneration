{{-- Vista de formulario de contacto para soporte general. --}}
@extends('layouts.app')

@section('titulo', 'Contacta con Nosotros - SeaFit')

@section('contenido')
    <div class="bg-[#F4F6F8] min-h-screen py-20 font-sans">
        <div class="container mx-auto max-w-2xl px-6">

            {{-- CABECERA --}}
            <header class="text-center mb-12">
                <h1 class="text-[#102A53] text-5xl font-extrabold mb-6 tracking-tight">Contactános</h1>
                <p class="text-gray-600 text-[17px] font-medium leading-relaxed">
                    Envíanos un mensaje y te responderemos lo antes posible.
                </p>
            </header>

            {{-- TARJETA DEL FORMULARIO --}}
            <div class="bg-white rounded-[2rem] p-10 shadow-sm border border-gray-100">
                <form action="#" method="POST" class="space-y-6">
                    @csrf

                    {{-- Nombre Completo --}}
                    <div>
                        <label class="block text-[#102A53] font-bold text-[16px] mb-2">Nombre Completo</label>
                        <input type="text" name="nombre" placeholder="Tu nombre"
                            class="w-full px-5 py-4 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-[#1A3878]/10 outline-none transition-all text-gray-700 placeholder:text-gray-400">
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-[#102A53] font-bold text-[16px] mb-2">Email</label>
                        <input type="email" name="email" placeholder="email@ejemplo.com"
                            class="w-full px-5 py-4 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-[#1A3878]/10 outline-none transition-all text-gray-700 placeholder:text-gray-400">
                    </div>

                    {{-- Asunto --}}
                    <div>
                        <label class="block text-[#102A53] font-bold text-[16px] mb-2">Asunto</label>
                        <div class="relative">
                            <select name="asunto"
                                class="w-full px-5 py-4 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-[#1A3878]/10 outline-none transition-all text-gray-700 appearance-none">
                                <option value="" disabled selected>Selecciona un motivo</option>
                                <option value="suscripcion">Dudas sobre Suscripción</option>
                                <option value="clases">Información sobre Clases</option>
                                <option value="tecnico">Problema Técnico</option>
                                <option value="otro">Otro motivo</option>
                            </select>
                            {{-- Icono de flecha para el select --}}
                            <span
                                class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                        </div>
                    </div>

                    {{-- Mensaje --}}
                    <div>
                        <label class="block text-[#102A53] font-bold text-[16px] mb-2">Mensaje</label>
                        <textarea name="mensaje" rows="5" placeholder="Describe tu consulta..."
                            class="w-full px-5 py-4 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-[#1A3878]/10 outline-none transition-all text-gray-700 placeholder:text-gray-400 resize-none"></textarea>
                    </div>

                    {{-- Botón Enviar --}}
                    <button type="submit"
                        class="w-full bg-[#1A3878] text-white py-5 rounded-2xl font-black text-lg hover:bg-[#102A53] transition-all shadow-lg shadow-[#1A3878]/20 uppercase tracking-widest mt-4">
                        Enviar Mensaje
                    </button>
                </form>
            </div>

        </div>
    </div>
@endsection