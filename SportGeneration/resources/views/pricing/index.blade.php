{{-- Tarifas. --}}
@extends('layouts.app')

@section('titulo', 'MembresÃ­a y tarifas')

@section('contenido')
    <div class="max-w-[1200px] mx-auto py-10 px-5 text-[#265E1F]">

        {{-- Banner. --}}
        <section
            class="h-[250px] flex items-end p-10 rounded-[15px] text-white mb-[30px] bg-cover bg-center bg-no-repeat relative overflow-hidden"
            style="background-image: url('{{ asset('imagenes/sauna-tarifas-banner.jpg') }}');">
            <div class="absolute inset-0 bg-black bg-opacity-40"></div>

            <div class="relative z-10">
                <nav class="text-sm font-medium mb-2 opacity-80">Inicio / Tarifas</nav>
                <h1 class="text-3xl sm:text-4xl font-black m-0 drop-shadow-lg">Tarifas de acceso total (MembresÃ­a)</h1>
            </div>
        </section>

        {{-- Contenedor principal: Columna en mÃ³vil, Fila en PC. --}}
        <div class="flex flex-col lg:flex-row gap-10 items-start">

            {{-- Columna izquierda: InformaciÃ³n. --}}
            <div class="flex-[2] w-full">
                <section class="mb-10">
                    <h2 class="text-2xl font-bold mb-3">Acceso ilimitado a todas nuestras instalaciones</h2>
                    <p class="text-gray-600 mb-8">Con la MembresÃ­a Total, obtienes la llave de Sport Generation para disfrutar de cada
                        Ã¡rea: gimnasio, piscina, pistas y clases. Sin lÃ­mites de horario.</p>

                    {{-- Grid de servicios. --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 my-8">
                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between">
                            <img src="{{ asset('imagenes/gimnasio-cardio-logo.png') }}" alt="Icono gimnasio"
                                class="w-[50px] h-[50px] mb-2 block">
                            <h3 class="text-black mt-0 mb-1 text-lg font-bold leading-tight">Gimnasio y cardio</h3>
                            <p class="text-gray-500 text-sm m-0 leading-snug">Maquinaria y zonas de entrenamiento libre.</p>
                        </div>
                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between">
                            <img src="{{ asset('imagenes/clases-logo.png') }}" alt="Icono clases"
                                class="w-[50px] h-[50px] mb-2 block">
                            <h3 class="text-black mt-0 mb-1 text-lg font-bold leading-tight">Clases colectivas</h3>
                            <p class="text-gray-500 text-sm m-0 leading-snug">Acceso ilimitado al catÃ¡logo de mÃ¡s de 50
                                clases.</p>
                        </div>
                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between">
                            <img src="{{ asset('imagenes/piscina-logo.png') }}" alt="Icono piscina"
                                class="w-[50px] h-[50px] mb-2 block">
                            <h3 class="text-black mt-0 mb-1 text-lg font-bold leading-tight">Piscina y wellness</h3>
                            <p class="text-gray-500 text-sm m-0 leading-snug">Uso libre de piscina climatizada, sauna y baÃ±o
                                turco.</p>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-bold mb-6">Â¿CÃ³mo funciona la membresÃ­a?</h2>
                    <div class="relative pl-5 border-l-2 border-[#265E1F]">

                        <div class="relative pb-8 pl-5">
                            <div
                                class="absolute -left-[27px] top-0 w-4 h-4 bg-[#265E1F] rounded-full border-4 border-white">
                            </div>
                            <strong class="block mb-1 text-lg">1. Elige tu plan</strong>
                            <p class="text-gray-600 text-sm m-0">Selecciona tu modalidad (mensual, trimestral o anual) y
                                regÃ­strate en lÃ­nea o en recepciÃ³n.</p>
                        </div>

                        <div class="relative pb-8 pl-5">
                            <div
                                class="absolute -left-[27px] top-0 w-4 h-4 bg-[#265E1F] rounded-full border-4 border-white">
                            </div>
                            <strong class="block mb-1 text-lg">2. Acceso total</strong>
                            <p class="text-gray-600 text-sm m-0">Recibe tu tarjeta de socio. Desde el primer dÃ­a tendrÃ¡s
                                acceso a todas las zonas.</p>
                        </div>

                        <div class="relative pl-5">
                            <div
                                class="absolute -left-[27px] top-0 w-4 h-4 bg-[#265E1F] rounded-full border-4 border-white">
                            </div>
                            <strong class="block mb-1 text-lg">3. Reserva de clases</strong>
                            <p class="text-gray-600 text-sm m-0">Usa nuestra web para reservar tu plaza en cualquiera de
                                nuestras clases.</p>
                        </div>

                    </div>
                </section>
            </div>

            {{-- Columna derecha: Tarjeta de precio. --}}
            <aside class="flex-1 w-full lg:sticky lg:top-5">
                <div class="bg-white p-6 rounded-[20px] shadow-[0_15px_35px_rgba(0,0,0,0.1)] border border-gray-100">
                    <h3 class="text-[#265E1F] text-xl font-bold mb-5 text-left">Elige tu plan</h3>

                    {{-- Selector de tarifa. --}}
                    <div class="bg-[#EAF7DB] p-1 rounded-xl flex mb-6 gap-1">
                        <button id="btn-mensual"
                            class="flex-1 border-none py-2.5 rounded-lg cursor-pointer font-bold transition-all text-xs bg-[#265E1F] text-white">Mensual</button>
                        <button id="btn-trimestral"
                            class="flex-1 border-none py-2.5 rounded-lg cursor-pointer font-bold transition-all text-xs bg-transparent text-gray-500 hover:text-gray-800">Trimestral</button>
                        <button id="btn-anual"
                            class="flex-1 border-none py-2.5 rounded-lg cursor-pointer font-bold transition-all text-xs bg-transparent text-gray-500 hover:text-gray-800">Anual</button>
                    </div>

                    <div class="text-center my-6">
                        <span id="precio-monto" class="text-5xl font-black text-[#265E1F]">29,99â‚¬</span>
                        <span id="precio-mes" class="text-xl text-gray-500 font-medium">/mes</span>
                    </div>

                    {{-- LÃ³gica de seguridad para socios --}}
                    @auth
                        @if(auth()->user()->isPlanActive())
                            <div class="bg-[#EAF7DB] border border-[#EAF7DB] p-4 rounded-xl text-center mb-6">
                                <p class="text-[#265E1F] font-black text-sm uppercase tracking-wider">
                                    Tu plan actual: {{ ucfirst(auth()->user()->tarifa) }}
                                </p>
                                <p class="text-[11px] text-[#265E1F] font-medium mt-1">
                                    Ya tienes acceso total activo.
                                </p>
                            </div>

                            <a href="{{ route('valoracion') }}"
                                class="block bg-[#265E1F] text-white text-center py-4 rounded-xl font-bold transition-transform hover:scale-105 mb-6">
                                Contratar entrenador personal
                            </a>
                        @else
                            <a href="{{ route('pago.gestion') }}"
                                class="block bg-[#265E1F] text-white text-center py-4 rounded-xl font-bold transition-transform hover:scale-105 mb-6">
                                Activar mi plan
                            </a>
                        @endif
                    @else
                        <a href="{{ url('/registro') }}"
                            class="block bg-[#265E1F] text-white text-center py-4 rounded-xl font-bold transition-transform hover:scale-105 mb-6">
                            Â¡Ãšnete ahora!
                        </a>
                    @endauth


                    <div class="mt-8">
                        <h4 class="text-xs tracking-wider text-[#265E1F] font-bold mb-4 uppercase">ACCESO ILIMITADO INCLUYE:
                        </h4>
                        <ul class="list-none p-0 m-0 text-sm text-gray-600">
                            <li class="flex items-start gap-3 mb-3">
                                <img src="{{ asset('imagenes/check-logo.png') }}" alt="Check"
                                    class="w-5 h-5 flex-shrink-0 mt-0.5">
                                <span>Gimnasio, piscina y wellness</span>
                            </li>
                            <li class="flex items-start gap-3 mb-3">
                                <img src="{{ asset('imagenes/check-logo.png') }}" alt="Check"
                                    class="w-5 h-5 flex-shrink-0 mt-0.5">
                                <span>Reserva ilimitada de clases</span>
                            </li>
                            <li class="flex items-start gap-3 mb-3">
                                <img src="{{ asset('imagenes/check-logo.png') }}" alt="Check"
                                    class="w-5 h-5 flex-shrink-0 mt-0.5">
                                <span id="texto-permanencia">Sin permanencia obligatoria</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </aside>

            <script>
                const btnMensual = document.getElementById('btn-mensual');
                const btnTrimestral = document.getElementById('btn-trimestral');
                const btnAnual = document.getElementById('btn-anual');

                const precioMonto = document.getElementById('precio-monto');
                const precioMes = document.getElementById('precio-mes');
                const textoPermanencia = document.getElementById('texto-permanencia');

                const botones = [btnMensual, btnTrimestral, btnAnual];

                function actualizarUI(btnActivo, precio, sufijo, permanencia) {
                    // Restablece todos los botones.
                    botones.forEach(btn => {
                        btn.classList.remove('bg-[#265E1F]', 'text-white');
                        btn.classList.add('bg-transparent', 'text-gray-500');
                    });

                    // Activa el seleccionado.
                    btnActivo.classList.add('bg-[#265E1F]', 'text-white');
                    btnActivo.classList.remove('bg-transparent', 'text-gray-500');

                    // Actualiza los textos.
                    precioMonto.innerText = precio;
                    precioMes.innerText = sufijo;
                    textoPermanencia.innerText = permanencia;
                }

                btnMensual.addEventListener('click', () => {
                    actualizarUI(btnMensual, '29,99â‚¬', '/mes', 'Sin permanencia obligatoria');
                });

                btnTrimestral.addEventListener('click', () => {
                    actualizarUI(btnTrimestral, '75,00â‚¬', '/total', 'Pago Ãºnico cada 3 meses');
                });

                btnAnual.addEventListener('click', () => {
                    actualizarUI(btnAnual, '250,00â‚¬', '/aÃ±o', 'Permanencia de 1 aÃ±o');
                });
            </script>
@endsection


