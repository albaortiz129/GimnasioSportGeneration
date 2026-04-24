{{-- Componente de pie de página global con enlaces corporativos y redes. --}}
<footer class="bg-[#051221] text-white py-[60px] shrink-0">
    {{-- Contenedor principal: columna en móvil, fila en PC --}}
    <div
        class="flex flex-col md:flex-row justify-between items-start max-w-[1200px] mx-auto px-5 sm:px-10 gap-10 md:gap-[30px]">

        {{-- Logo y Texto --}}
        <div class="flex-[1.5] min-w-[200px] w-full md:w-auto">
            <img src="{{ asset('imagenes/Logo transparente.png') }}" class="w-[210px] sm:w-[250px] h-auto block -mt-[15px] mb-5"
                alt="Sea Fit Logo">
            <p class="text-[14px] text-[#a0aec0] leading-[1.6] m-0">
                Tu centro deportivo de confianza. Encuentra tu pasión y supérate cada día con nosotros.
            </p>
        </div>

        {{-- Soporte --}}
        <div class="flex-1">
            <h3 class="text-[16px] font-bold uppercase mb-[25px] text-white leading-none">Soporte</h3>
            <ul class="list-none p-0 m-0">
                <li class="mb-[10px]">
                    <a href="{{ route('faq') }}"
                        class="text-[#a0aec0] text-[14px] transition-colors duration-300 hover:text-white">
                        Preguntas Frecuentes (FAQ)
                    </a>
                </li>
                <li class="mb-[10px]">
                    <a href="{{ route('contacto') }}"
                        class="text-[#a0aec0] text-[14px] transition-colors duration-300 hover:text-white">
                        Contacto
                    </a>
                </li>
            </ul>
        </div>

        {{-- Empresa --}}
        <div class="flex-1">
            <h3 class="text-[16px] font-bold uppercase mb-[25px] text-white leading-none">Empresa</h3>
            <ul class="list-none p-0 m-0">
                <li class="mb-[10px]">
                    <a href="{{ route('nosotros') }}"
                        class="text-[#a0aec0] text-[14px] transition-colors duration-300 hover:text-white">
                        Sobre Nosotros
                    </a>
                </li>
                <li class="mb-[10px]">
                    <a href="{{ route('empleo') }}"
                        class="text-[#a0aec0] text-[14px] transition-colors duration-300 hover:text-white">
                        Trabaja con Nosotros
                    </a>
                </li>
            </ul>
        </div>

        {{-- Redes --}}
        <div class="flex-1">
            <h3 class="text-[16px] font-bold uppercase mb-[25px] text-white leading-none">Redes Sociales</h3>
            <div class="flex flex-col gap-[15px]">
                <p class="text-[14px] text-[#a0aec0] leading-[1.6] m-0">
                    Síguenos para estar al día de todas las novedades y eventos.
                </p>

                {{-- Iconos de Redes --}}
                <div class="flex flex-row gap-[20px] items-center mt-[5px]">
                    {{-- La clase `group` permite animar la imagen al pasar el ratón por el enlace. --}}
                    <a href="https://www.instagram.com" target="_blank" class="group">
                        <img src="{{ asset('imagenes/instagram-logo.png') }}" alt="Instagram"
                            class="w-[24px] h-[24px] brightness-0 invert transition-all duration-300 group-hover:scale-125 group-hover:opacity-80">
                    </a>
                    <a href="https://www.facebook.com/" target="_blank" class="group">
                        <img src="{{ asset('imagenes/facebook-logo.png') }}" alt="Facebook"
                            class="w-[24px] h-[24px] brightness-0 invert transition-all duration-300 group-hover:scale-125 group-hover:opacity-80">
                    </a>
                    <a href="https://www.x.com/" target="_blank" class="group">
                        <img src="{{ asset('imagenes/twitter-logo.png') }}" alt="X"
                            class="w-[24px] h-[24px] brightness-0 invert transition-all duration-300 group-hover:scale-125 group-hover:opacity-80">
                    </a>
                </div>
            </div>
        </div>

    </div>
</footer>
