{{-- Vista de empleo para candidaturas y captacion de talento. --}}
@extends('layouts.app')

@section('titulo', 'Trabaja con Nosotros - SeaFit')

@section('contenido')
    <div class="bg-[#F4F6F8] min-h-screen py-20 font-sans">
        <div class="container mx-auto max-w-4xl px-6">

            <header class="text-center mb-12">
                <h1 class="text-[#102A53] text-5xl font-black mb-6 tracking-tight italic">Únete al Equipo SeaFit</h1>
                <p class="text-gray-600 text-[17px] font-medium max-w-2xl mx-auto leading-relaxed">
                    ¿Te apasiona el fitness y el bienestar? Buscamos talento para seguir transformando vidas en la Costa del
                    Sol.
                </p>
            </header>

            {{-- TARJETA PRINCIPAL --}}
            <div class="bg-white rounded-[2.5rem] p-10 md:p-16 shadow-sm border border-gray-100">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-12">
                    <div>
                        <h2 class="text-[#102A53] text-2xl font-black mb-4 uppercase tracking-tighter">¿Qué buscamos?</h2>
                        <p class="text-gray-600 leading-relaxed mb-6">
                            En SeaFit valoramos la energía positiva, la formación continua y, sobre todo, la capacidad de
                            motivar a nuestros socios. Buscamos perfiles que compartan nuestra filosofía de equilibrio entre
                            mente y cuerpo.
                        </p>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3 text-gray-700 font-bold text-sm">
                                <span class="material-symbols-outlined text-[#1A3878]">check_circle</span> Instructores de
                                Clases Colectivas
                            </li>
                            <li class="flex items-center gap-3 text-gray-700 font-bold text-sm">
                                <span class="material-symbols-outlined text-[#1A3878]">check_circle</span> Entrenadores
                                Personales Certificados
                            </li>
                            <li class="flex items-center gap-3 text-gray-700 font-bold text-sm">
                                <span class="material-symbols-outlined text-[#1A3878]">check_circle</span> Personal de
                                Recepción y Ventas
                            </li>
                        </ul>
                    </div>
                    <div class="bg-[#F8FAFC] p-8 rounded-3xl border border-gray-100">
                        <h2 class="text-[#102A53] text-2xl font-black mb-4 uppercase tracking-tighter">Beneficios</h2>
                        <ul class="space-y-4 text-gray-600 text-sm font-medium">
                            <li>🚀 Plan de carrera y formación interna.</li>
                            <li>🏋️ Acceso gratuito a todas las instalaciones.</li>
                            <li>🕒 Horarios flexibles y buen ambiente.</li>
                            <li>🌊 Ubicación privilegiada frente al mar.</li>
                        </ul>
                    </div>
                </div>

                <hr class="border-gray-100 mb-12">

                {{-- FORMULARIO DE ENVÍO DE CV --}}
                <div class="max-w-xl mx-auto">
                    <h3 class="text-[#102A53] text-center text-2xl font-black mb-8">Envíanos tu CV</h3>
                    <form action="#" method="POST" class="space-y-5">
                        @csrf
                        <input type="text" placeholder="Nombre completo"
                            class="w-full px-5 py-4 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-[#1A3878]/10 transition-all">
                        <input type="email" placeholder="Correo electrónico"
                            class="w-full px-5 py-4 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-[#1A3878]/10 transition-all">

                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1">Adjuntar CV
                                (PDF)</label>
                            <input type="file"
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-black file:bg-[#1A3878] file:text-white hover:file:bg-[#102A53] cursor-pointer">
                        </div>

                        <button type="submit"
                            class="w-full bg-[#1A3878] text-white py-5 rounded-2xl font-black text-lg hover:bg-[#102A53] transition-all shadow-lg mt-4 uppercase tracking-widest">
                            Enviar Candidatura
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection