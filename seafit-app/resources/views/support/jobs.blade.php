{{--Trabaja con nosotros.--}}
@extends('layouts.app')

@section('titulo', 'Trabaja con nosotros')

@section('contenido')
    <div class="bg-[#F4F6F8] min-h-screen py-20 font-sans">
        <div class="container mx-auto max-w-4xl px-6">

            {{-- Cabecera visual de la pagina. --}}
            <header class="text-center mb-12">
                <h1 class="text-[#102A53] text-5xl font-black mb-6 tracking-tight italic">Únete al equipo SeaFit</h1>
                <p class="text-gray-600 text-[17px] font-medium max-w-2xl mx-auto leading-relaxed">
                    ¿¿Te apasiona el fitness y el bienestar? Buscamos talento para seguir transformando vidas.
                </p>
            </header>

            {{-- Tarjeta principal con informacion y formulario. --}}
            <div class="bg-white rounded-[2.5rem] p-10 md:p-16 shadow-sm border border-gray-100">

                {{-- Bloque informativo (izquierda) + beneficios (derecha). --}}
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
                                clases colectivas
                            </li>
                            <li class="flex items-center gap-3 text-gray-700 font-bold text-sm">
                                <span class="material-symbols-outlined text-[#1A3878]">check_circle</span> Entrenadores
                                personales certificados
                            </li>
                            <li class="flex items-center gap-3 text-gray-700 font-bold text-sm">
                                <span class="material-symbols-outlined text-[#1A3878]">check_circle</span> Personal de
                                recepción y ventas
                            </li>
                        </ul>
                    </div>

                    <div class="bg-[#F8FAFC] p-8 rounded-3xl border border-gray-100">
                        <h2 class="text-[#102A53] text-2xl font-black mb-4 uppercase tracking-tighter">Beneficios</h2>
                        <ul class="space-y-4 text-gray-600 text-sm font-medium">
                            <li>Plan de carrera y formación interna.</li>
                            <li>Acceso gratuito a todas las instalaciones.</li>
                            <li>Horarios flexibles y buen ambiente.</li>
                        </ul>
                    </div>
                </div>

                <hr class="border-gray-100 mb-12">

                {{-- Formulario real de candidatura.
                - action: ruta que procesa el envio.
                - enctype: necesario para subir archivos (CV). --}}
                <div class="max-w-xl mx-auto">
                    <h3 class="text-[#102A53] text-center text-2xl font-black mb-8">Envíanos tu candidatura</h3>

                    {{-- Mensaje de exito cuando el correo se envia bien. --}}
                    @if (session('success'))
                        <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Error general (por ejemplo, fallo de servidor/correo). --}}
                    @if ($errors->has('formulario'))
                        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ $errors->first('formulario') }}
                        </div>
                    @endif

                    <form action="{{ route('empleo.enviar') }}" method="POST" enctype="multipart/form-data"
                        class="space-y-5">
                        @csrf

                        {{-- Nombre completo del candidato. --}}
                        <div>
                            <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Nombre completo"
                                class="w-full px-5 py-4 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-[#1A3878]/10 transition-all">
                            @error('nombre')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Correo del candidato (se usa para replyTo). --}}
                        <div>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="Correo electrónico"
                                class="w-full px-5 py-4 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-[#1A3878]/10 transition-all">
                            @error('email')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Telefono opcional. --}}
                        <div>
                            <input type="text" name="telefono" value="{{ old('telefono') }}"
                                placeholder="Teléfono (opcional)"
                                class="w-full px-5 py-4 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-[#1A3878]/10 transition-all">
                            @error('telefono')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Puesto al que se presenta. --}}
                        <div>
                            <input type="text" name="puesto" value="{{ old('puesto') }}"
                                placeholder="Puesto al que te presentas"
                                class="w-full px-5 py-4 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-[#1A3878]/10 transition-all">
                            @error('puesto')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Mensaje opcional de experiencia/motivacion. --}}
                        <div>
                            <textarea name="mensaje" rows="5" placeholder="Cuéntanos tu experiencia (opcional)"
                                class="w-full px-5 py-4 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-[#1A3878]/10 transition-all resize-none">{{ old('mensaje') }}</textarea>
                            @error('mensaje')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Subida del CV en PDF. --}}
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest ml-1">
                                Adjuntar CV (PDF, máximo 5 MB)
                            </label>
                            <input type="file" name="cv" accept="application/pdf"
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-black file:bg-[#1A3878] file:text-white hover:file:bg-[#102A53] cursor-pointer">
                            @error('cv')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Boton de envio final del formulario. --}}
                        <button type="submit"
                            class="w-full bg-[#1A3878] text-white py-5 rounded-2xl font-black text-lg hover:bg-[#102A53] transition-all shadow-lg mt-4 uppercase tracking-widest">
                            Enviar candidatura
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
