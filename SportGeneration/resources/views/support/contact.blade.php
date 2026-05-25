{{-- Formulario de contacto. --}}
@extends('layouts.app')

@section('titulo', 'Contacta con nosotros')

@section('contenido')
    <div class="bg-[#F4F6F8] min-h-screen py-20 font-sans">
        <div class="container mx-auto max-w-2xl px-6">

            {{-- Cabecera. --}}
            <header class="text-center mb-12">
                <h1 class="text-[#265e1f] text-5xl font-extrabold mb-6 tracking-tight">Contactános</h1>
                <p class="text-gray-600 text-[17px] font-medium leading-relaxed">
                    Envíanos un mensaje y te responderemos lo antes posible.
                </p>
            </header>

            {{-- Formulario --}}
            <div class="bg-white rounded-[2rem] p-10 shadow-sm border border-gray-100">
                <form action="#" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-[#265e1f] font-bold text-[16px] mb-2">Nombre completo</label>
                        <input type="text" name="nombre" placeholder="Tu nombre"
                            class="w-full px-5 py-4 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-[#265e1f]/10 outline-none transition-all text-gray-700 placeholder:text-gray-400">
                    </div>

                    <div>
                        <label class="block text-[#265e1f] font-bold text-[16px] mb-2">Email</label>
                        <input type="email" name="email" placeholder="email@ejemplo.com"
                            class="w-full px-5 py-4 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-[#265e1f]/10 outline-none transition-all text-gray-700 placeholder:text-gray-400">
                    </div>

                    <div>
                        <label class="block text-[#265e1f] font-bold text-[16px] mb-2">Asunto</label>
                        <div class="relative">
                            <select name="asunto"
                                class="w-full px-5 py-4 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-[#265e1f]/10 outline-none transition-all text-gray-700 appearance-none">
                                <option value="" disabled selected>Selecciona un motivo</option>
                                <option value="suscripcion">Dudas sobre suscripción</option>
                                <option value="clases">Información sobre clases</option>
                                <option value="tecnico">Problema técnico</option>
                                <option value="otro">Otro motivo</option>
                            </select>
                            {{-- Icono de flecha para el select --}}
                            <span
                                class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                        </div>
                    </div>

                    {{-- Mensaje --}}
                    <div>
                        <label class="block text-[#265e1f] font-bold text-[16px] mb-2">Mensaje</label>
                        <textarea name="mensaje" rows="5" placeholder="Describe tu consulta..."
                            class="w-full px-5 py-4 rounded-xl border border-gray-200 bg-white focus:ring-2 focus:ring-[#265e1f]/10 outline-none transition-all text-gray-700 placeholder:text-gray-400 resize-none"></textarea>
                    </div>

                    {{-- Botón enviar --}}
                    <button type="submit"
                        class="w-full bg-[#265e1f] text-white py-5 rounded-2xl font-black text-lg hover:bg-[#265e1f] transition-all shadow-lg shadow-[#265e1f]/20 uppercase tracking-widest mt-4">
                        Enviar Mensaje
                    </button>
                </form>
            </div>

        </div>
    </div>
@endsection