{{-- Vista de tarifas y comparativa visual de planes de membresia. --}}
@extends ('moldes.inicio')

@section('titulo', 'Membresía y Tarifas - SeaFit')

@section('contenido')
    <div class="max-w-[1200px] mx-auto py-10 px-5 text-[#0A1931]">

        {{-- Banner Superior --}}
        <section
            class="h-[250px] flex items-end p-10 rounded-[15px] text-white mb-[30px] bg-cover bg-center bg-no-repeat relative overflow-hidden"
            style="background-image: url('{{ asset('imagenes/sauna-tarifas-banner.jpg') }}');">
            {{-- Capa oscura para que el texto se lea bien sobre la imagen --}}
            <div class="absolute inset-0 bg-black bg-opacity-40"></div>

            <div class="relative z-10">
                <nav class="text-sm font-medium mb-2 opacity-80">Inicio / Tarifas</nav>
                <h1 class="text-3xl sm:text-4xl font-black m-0 drop-shadow-lg">Tarifas de Acceso Total (Membresía)</h1>
            </div>
        </section>

        {{-- Contenedor Principal: Columna en móvil, Fila en PC --}}
        <div class="flex flex-col lg:flex-row gap-10 items-start">

            {{-- Columna Izquierda: Información (Ocupa 2/3 en PC) --}}
            <div class="flex-[2] w-full">
                <section class="mb-10">
                    <h2 class="text-2xl font-bold mb-3">Acceso Ilimitado a todas nuestras instalaciones</h2>
                    <p class="text-gray-600 mb-8">Con la Membresía Total, obtienes la llave de SeaFit para disfrutar de cada
                        área: gimnasio, piscina, pistas y clases. Sin límites de horario.</p>

                    {{-- Grid de Servicios: 1 columna en móvil, 3 en PC --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 my-8">
                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between">
                            <img src="{{ asset('imagenes/gimnasio-cardio-logo.png') }}" alt="Icono gimnasio"
                                class="w-[50px] h-[50px] mb-2 block">
                            <h3 class="text-black mt-0 mb-1 text-lg font-bold leading-tight">Gimnasio y Cardio</h3>
                            <p class="text-gray-500 text-sm m-0 leading-snug">Maquinaria y zonas de entrenamiento libre.</p>
                        </div>
                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between">
                            <img src="{{ asset('imagenes/clases-logo.png') }}" alt="Icono clases"
                                class="w-[50px] h-[50px] mb-2 block">
                            <h3 class="text-black mt-0 mb-1 text-lg font-bold leading-tight">Clases Colectivas</h3>
                            <p class="text-gray-500 text-sm m-0 leading-snug">Acceso ilimitado al catálogo de más de 50
                                clases.</p>
                        </div>
                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between">
                            <img src="{{ asset('imagenes/piscina-logo.png') }}" alt="Icono piscina"
                                class="w-[50px] h-[50px] mb-2 block">
                            <h3 class="text-black mt-0 mb-1 text-lg font-bold leading-tight">Piscina y Wellness</h3>
                            <p class="text-gray-500 text-sm m-0 leading-snug">Uso libre de piscina climatizada, sauna y baño
                                turco.</p>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-bold mb-6">¿Cómo funciona la Membresía?</h2>
                    <div class="relative pl-5 border-l-2 border-[#0A1931]">

                        <div class="relative pb-8 pl-5">
                            <div
                                class="absolute -left-[27px] top-0 w-4 h-4 bg-[#0A1931] rounded-full border-4 border-white">
                            </div>
                            <strong class="block mb-1 text-lg">1. Elige tu Plan</strong>
                            <p class="text-gray-600 text-sm m-0">Selecciona tu modalidad (mensual, trimestral o anual) y
                                regístrate en línea o en recepción.</p>
                        </div>

                        <div class="relative pb-8 pl-5">
                            <div
                                class="absolute -left-[27px] top-0 w-4 h-4 bg-[#0A1931] rounded-full border-4 border-white">
                            </div>
                            <strong class="block mb-1 text-lg">2. Acceso Total</strong>
                            <p class="text-gray-600 text-sm m-0">Recibe tu tarjeta de socio. Desde el primer día tendrás
                                acceso a todas las zonas.</p>
                        </div>

                        <div class="relative pl-5">
                            <div
                                class="absolute -left-[27px] top-0 w-4 h-4 bg-[#0A1931] rounded-full border-4 border-white">
                            </div>
                            <strong class="block mb-1 text-lg">3. Reserva de Clases</strong>
                            <p class="text-gray-600 text-sm m-0">Usa nuestra web para reservar tu plaza en cualquiera de
                                nuestras clases.</p>
                        </div>

                    </div>
                </section>
            </div>

            {{-- Columna Derecha: Tarjeta de Precio (Sticky) --}}
            <aside class="flex-1 w-full lg:sticky lg:top-5">
                <div class="bg-white p-6 rounded-[20px] shadow-[0_15px_35px_rgba(0,0,0,0.1)] border border-gray-100">
                    <h3 class="text-[#0A1931] text-xl font-bold mb-5 text-left">Elige tu Plan</h3>

                    {{-- Selector de 3 botones --}}
                    <div class="bg-[#f1f3f6] p-1 rounded-xl flex mb-6 gap-1">
                        <button id="btn-mensual"
                            class="flex-1 border-none py-2.5 rounded-lg cursor-pointer font-bold transition-all text-xs bg-[#0A1931] text-white">Mensual</button>
                        <button id="btn-trimestral"
                            class="flex-1 border-none py-2.5 rounded-lg cursor-pointer font-bold transition-all text-xs bg-transparent text-gray-500 hover:text-gray-800">Trimestral</button>
                        <button id="btn-anual"
                            class="flex-1 border-none py-2.5 rounded-lg cursor-pointer font-bold transition-all text-xs bg-transparent text-gray-500 hover:text-gray-800">Anual</button>
                    </div>

                    <div class="text-center my-6">
                        <span id="precio-monto" class="text-5xl font-black text-[#0A1931]">29,99€</span>
                        <span id="precio-mes" class="text-xl text-gray-500 font-medium">/mes</span>
                    </div>

                    {{-- LÓGICA DE SEGURIDAD PARA SOCIOS --}}
                    @auth
                        @if(auth()->user()->planActivo())
                            <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl text-center mb-6">
                                <p class="text-[#1A3878] font-black text-sm uppercase tracking-wider">
                                    Tu plan actual: {{ ucfirst(auth()->user()->tarifa) }}
                                </p>
                                <p class="text-[11px] text-blue-400 font-medium mt-1">
                                    Ya tienes acceso total activo.
                                </p>
                            </div>

                            <a href="{{ route('valoracion') }}"
                                class="block bg-[#0A1931] text-white text-center py-4 rounded-xl font-bold transition-transform hover:scale-105 mb-6">
                                Contratar Entrenador Personal
                            </a>
                        @else
                            <a href="{{ route('pago.gestion') }}"
                                class="block bg-[#0A1931] text-white text-center py-4 rounded-xl font-bold transition-transform hover:scale-105 mb-6">
                                Activar mi Plan
                            </a>
                        @endif
                    @else
                        <a href="{{ url('/registro') }}"
                            class="block bg-[#0A1931] text-white text-center py-4 rounded-xl font-bold transition-transform hover:scale-105 mb-6">
                            ¡Únete Ahora!
                        </a>
                    @endauth


                    <div class="mt-8">
                        <h4 class="text-xs tracking-wider text-[#0A1931] font-bold mb-4 uppercase">ACCESO ILIMITADO INCLUYE:
                        </h4>
                        <ul class="list-none p-0 m-0 text-sm text-gray-600">
                            <li class="flex items-start gap-3 mb-3">
                                <img src="{{ asset('imagenes/check-logo.png') }}" alt="Check"
                                    class="w-5 h-5 flex-shrink-0 mt-0.5">
                                <span>Gimnasio, Piscina y Wellness</span>
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
                    // Resetear todos los botones
                    botones.forEach(btn => {
                        btn.classList.remove('bg-[#0A1931]', 'text-white');
                        btn.classList.add('bg-transparent', 'text-gray-500');
                    });

                    // Activar el seleccionado
                    btnActivo.classList.add('bg-[#0A1931]', 'text-white');
                    btnActivo.classList.remove('bg-transparent', 'text-gray-500');

                    // Actualizar textos
                    precioMonto.innerText = precio;
                    precioMes.innerText = sufijo;
                    textoPermanencia.innerText = permanencia;
                }

                btnMensual.addEventListener('click', () => {
                    actualizarUI(btnMensual, '29,99€', '/mes', 'Sin permanencia obligatoria');
                });

                btnTrimestral.addEventListener('click', () => {
                    actualizarUI(btnTrimestral, '75,00€', '/total', 'Pago único cada 3 meses');
                });

                btnAnual.addEventListener('click', () => {
                    actualizarUI(btnAnual, '250,00€', '/año', 'El mejor precio por mes');
                });
            </script>
@endsection