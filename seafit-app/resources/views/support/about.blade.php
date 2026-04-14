{{-- Vista corporativa sobre historia, valores y proyeccion de SeaFit. --}}
@extends('layouts.app')

@section('titulo', 'Nuestra Historia - SeaFit')

@section('contenido')
    <div class="bg-[#F4F6F8] min-h-screen py-20 font-sans">
        <div class="container mx-auto max-w-4xl px-6">

            {{-- TARJETA PRINCIPAL --}}
            <div class="bg-white rounded-[2.5rem] p-10 md:p-16 shadow-sm border border-gray-100">

                {{-- Título Principal --}}
                <header class="text-center mb-12">
                    <h1 class="text-[#102A53] text-5xl font-black mb-6 tracking-tight leading-tight">
                        Nuestra Historia: La Pasión por el Mar y el Deporte
                    </h1>
                </header>

                {{-- Texto Introductorio --}}
                <div class="space-y-8">
                    <p class="text-gray-600 text-lg leading-relaxed text-center italic">
                        <strong>SeaFit Sports</strong> nació en Benalmádena con una visión simple: fusionar el entrenamiento
                        de alta intensidad con la calma inspiradora del Mediterráneo.
                    </p>

                    <p class="text-gray-600 text-[17px] leading-relaxed">
                        No somos solo un gimnasio; somos un centro de bienestar integral diseñado para que nuestros socios
                        no solo alcancen sus <strong>metas físicas</strong>, sino que encuentren un equilibrio mental.
                        Creemos que el entorno influye en el rendimiento, y por ello hemos creado un espacio donde la
                        tecnología y la naturaleza conviven.
                    </p>

                    <hr class="border-gray-100">

                    {{-- Valores --}}
                    <section>
                        <h2 class="text-[#102A53] text-3xl font-black mb-6 tracking-tight">Nuestros Valores</h2>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3 text-gray-600 text-[17px]">
                                <span class="text-[#1A3878] mt-1">•</span>
                                <span><strong>Excelencia:</strong> Equipamiento de última generación y los mejores
                                    entrenadores certificados del sector.</span>
                            </li>
                            <li class="flex items-start gap-3 text-gray-600 text-[17px]">
                                <span class="text-[#1A3878] mt-1">•</span>
                                <span><strong>Comunidad:</strong> Creamos un ambiente motivador donde todos los socios se
                                    apoyan mutuamente para superarse.</span>
                            </li>
                            <li class="flex items-start gap-3 text-gray-600 text-[17px]">
                                <span class="text-[#1A3878] mt-1">•</span>
                                <span><strong>Flexibilidad:</strong> Ofrecemos acceso ilimitado y gestión 100% online para
                                    adaptarnos a tu ritmo de vida actual.</span>
                            </li>
                            <li class="flex items-start gap-3 text-gray-600 text-[17px]">
                                <span class="text-[#1A3878] mt-1">•</span>
                                <span><strong>Bienestar Integral:</strong> Priorizamos tanto la fuerza física como la salud
                                    mental a través de nuestras zonas wellness y relax.</span>
                            </li>
                        </ul>
                    </section>

                    <hr class="border-gray-100">

                    {{-- Futuro --}}
                    <section>
                        <h2 class="text-[#102A53] text-3xl font-black mb-6 tracking-tight">El Futuro de SeaFit</h2>
                        <p class="text-gray-600 text-[17px] leading-relaxed">
                            Continuamos expandiendo nuestros servicios, introduciendo nuevas clases colectivas y tecnología
                            de seguimiento deportivo de vanguardia. Nuestro objetivo es ser tu <strong>aliado
                                definitivo</strong> en el camino hacia un estilo de vida más saludable y activo en la Costa
                            del Sol. ¡Únete a la marea SeaFit!
                        </p>
                    </section>

                    {{-- Link a Tarifas --}}
                    <div class="pt-10 text-center">
                        <a href="{{ url('/tarifas') }}"
                            class="text-[#102A53] font-black text-xl underline hover:text-[#1A3878] transition-colors decoration-2 underline-offset-8">
                            Consulta nuestras tarifas y comienza hoy mismo.
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection