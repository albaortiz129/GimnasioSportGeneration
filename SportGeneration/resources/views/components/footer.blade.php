{{-- Pie de página. --}}
<footer class="bg-[#EAF7DB] text-[#265E1F] py-[60px] border-t border-[#EAF7DB] shrink-0">
    {{-- Contenedor principal: columna en móvil, fila en PC --}}
    <div
        class="flex flex-col md:flex-row justify-between items-start max-w-[1200px] mx-auto px-5 sm:px-10 gap-10 md:gap-[30px]">

        {{-- Logo y Texto --}}
        <div class="flex-[1.5] min-w-[200px] w-full md:w-auto">
            <img src="{{ asset('imagenes/Logo transparente.png') }}"
                class="w-[100px] sm:w-[150px] h-auto block -mt-[15px] mb-5" alt="Sport Generation Logo">
            {{-- <p class="text-[14px] text-[#265E1F] leading-[1.6] m-0">
                Tu centro deportivo de confianza. Encuentra tu pasión y supérate cada día con nosotros.
            </p>--}}
        </div>

        {{-- Soporte --}}
        <div class="flex-1">
            <h3 class="text-[16px] font-bold uppercase mb-[25px] text-[#265E1F] leading-none">Soporte</h3>
            <ul class="list-none p-0 m-0">
                <li class="mb-[10px]">
                    <a href="{{ route('faq') }}"
                        class="text-[#1f2937] text-[14px] font-medium transition-colors duration-300 hover:text-[#265E1F]">
                        Preguntas Frecuentes (FAQ)
                    </a>
                </li>
                <li class="mb-[10px]">
                    <a href="{{ route('contacto') }}"
                        class="text-[#1f2937] text-[14px] font-medium transition-colors duration-300 hover:text-[#265E1F]">
                        Contacto
                    </a>
                </li>
            </ul>
        </div>

        {{-- Empresa --}}
        <div class="flex-1">
            <h3 class="text-[16px] font-bold uppercase mb-[25px] text-[#265E1F] leading-none">Empresa</h3>
            <ul class="list-none p-0 m-0">
                <li class="mb-[10px]">
                    <a href="{{ route('nosotros') }}"
                        class="text-[#1f2937] text-[14px] font-medium transition-colors duration-300 hover:text-[#265E1F]">
                        Sobre nosotros
                    </a>
                </li>
            </ul>
        </div>

        {{-- Redes sociales--}}
        <div class="flex-1">
            <h3 class="text-[16px] font-bold uppercase mb-[25px] text-[#265E1F] leading-none">Redes sociales</h3>
            <div class="flex flex-col gap-[15px]">
                <p class="text-[14px] text-[#265E1F] leading-[1.6] m-0">
                    Síguenos para estar al día de todas las novedades y eventos.
                </p>

                {{-- Iconos de redes sociales--}}
                <div class="flex flex-row gap-6 sm:gap-7 items-center mt-[5px]">
                    {{-- La clase `group` permite animar la imagen al pasar el ratón por el enlace. --}}
                    <a href="https://www.instagram.com/gimnasiosport_generation/" target="_blank"
                        class="group inline-flex p-1.5 rounded-md">
                        <img src="{{ asset('imagenes/instagram-logo.svg') }}" alt="Instagram"
                            class="w-[30px] h-[30px] transition-all duration-300 group-hover:scale-110 group-hover:opacity-80">
                    </a>
                    <a href="https://www.facebook.com/SportGenerationGYM" target="_blank"
                        class="group inline-flex p-1.5 rounded-md">
                        <img src="{{ asset('imagenes/facebook-logo.svg') }}" alt="Facebook"
                            class="w-[30px] h-[30px] transition-all duration-300 group-hover:scale-110 group-hover:opacity-80">
                    </a>
                    <a href="https://maps.app.goo.gl/AfaphK9Agihu4u3y5" target="_blank"
                        class="group inline-flex p-1.5 rounded-md">
                        <img src="{{ asset('imagenes/maps.svg') }}" alt="Google Maps"
                            class="w-[30px] h-[30px] transition-all duration-300 group-hover:scale-110 group-hover:opacity-80">
                    </a>
                </div>
            </div>
        </div>

    </div>
</footer>