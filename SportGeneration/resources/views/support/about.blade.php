{{-- Sobre nosotros. --}}
@extends('layouts.app')

@section('titulo', 'Sobre nosotros')

@section('contenido')
    <div class="bg-[#F4F6F8] min-h-screen py-20 font-sans">
        <div class="container mx-auto max-w-4xl px-6">

            {{-- TARJETA PRINCIPAL --}}
            <div class="bg-white rounded-[2.5rem] p-10 md:p-16 shadow-sm border border-gray-100">

                {{-- Título --}}
                <header class="text-center mb-12">
                    <h1 class="text-[#265e1f] text-5xl font-black mb-6 tracking-tight leading-tight">
                        Nuestra historia: La pasión por el deporte.
                    </h1>
                </header>

                {{-- Texto --}}
                <div class="space-y-8">
                    <p class="text-gray-600 text-lg leading-relaxed text-center italic">
                        <strong>Sport Generation Sports</strong> nació en Málaga con una visión simple: hacer la vida fitness mucho
                        más sencilla.
                    </p>

                    <p class="text-gray-600 text-[17px] leading-relaxed">
                        No somos solo un gimnasio; somos un centro de bienestar integral diseñado para que nuestros socios
                        no solo alcancen sus metas físicas, sino que encuentren un equilibrio mental.
                    </p>

                    <hr class="border-gray-100">

                    {{-- Valores --}}
                    <section>
                        <h2 class="text-[#265e1f] text-3xl font-black mb-6 tracking-tight">Nuestros valores</h2>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3 text-gray-600 text-[17px]">
                                <span class="text-[#265e1f] mt-1">•</span>
                                <span><strong>Excelencia:</strong> Equipamiento de última generación y los mejores
                                    entrenadores certificados del sector.</span>
                            </li>
                            <li class="flex items-start gap-3 text-gray-600 text-[17px]">
                                <span class="text-[#265e1f] mt-1">•</span>
                                <span><strong>Comunidad:</strong> Creamos un ambiente motivador donde todos nos apoyamos
                                    mutuamente para poder mejorar cada día.</span>
                            </li>
                            <li class="flex items-start gap-3 text-gray-600 text-[17px]">
                                <span class="text-[#265e1f] mt-1">•</span>
                                <span><strong>Flexibilidad:</strong> Ofrecemos acceso ilimitado y gestión 100% online para
                                    adaptarnos a tu ritmo de vida actual.</span>
                            </li>
                            <li class="flex items-start gap-3 text-gray-600 text-[17px]">
                                <span class="text-[#265e1f] mt-1">•</span>
                                <span><strong>Bienestar Integral:</strong> Priorizamos tanto la fuerza física como la salud
                                    mental a través de nuestras zonas wellness y relax.</span>
                            </li>
                        </ul>
                    </section>

                    <hr class="border-gray-100">

                    {{-- Futuro --}}
                    <section>
                        <h2 class="text-[#265e1f] text-3xl font-black mb-6 tracking-tight">El futuro de Sport Generation</h2>
                        <p class="text-gray-600 text-[17px] leading-relaxed">
                            Continuamos expandiendo nuestros servicios, introduciendo nuevas clases colectivas y tecnología
                            de seguimiento deportivo. Nuestro objetivo es ser tu aliado definitivo en el camino hacia un
                            estilo de vida más saludable y activo.
                        </p>
                    </section>

                    {{-- Link a tarifas --}}
                    <div class="pt-10 text-center">
                        <a href="{{ url('/tarifas') }}"
                            class="text-[#265e1f] font-black text-xl underline hover:text-[#265e1f] transition-colors decoration-2 underline-offset-8">
                            Consulta nuestras tarifas y comienza hoy mismo.
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection