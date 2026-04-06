{{-- Componente de cabecera global con navegacion y estado de sesion. --}}
<header class="w-full bg-white shadow-sm">
    <div class="flex justify-between items-center max-w-[1200px] mx-auto py-[10px] px-5">

        {{-- Lado Izquierdo: Logo --}}
        {{-- Le ponemos flex-1 para que empuje al centro exactamente igual que el lado derecho --}}
        <div class="flex-1">
            <a href="{{ url('/') }}" class="block">
                <img src="{{ asset('imagenes/Logo transparente.png') }}" alt="Sea Fit" class="h-[55px] block">
            </a>
        </div>

        {{-- Centro: Navegación --}}
        <div class="flex justify-center flex-[2]">
            <nav class="flex gap-[25px]">
                <a href="{{ url('/') }}"
                    class="{{ Request::is('/') ? 'text-[#1A3878] font-bold' : 'text-gray-600 font-medium hover:text-[#1A3878] transition-colors duration-300' }}">Inicio</a>
                <a href="{{ url('/servicios') }}"
                    class="{{ Request::is('servicios') ? 'text-[#1A3878] font-bold' : 'text-gray-600 font-medium hover:text-[#1A3878] transition-colors duration-300' }}">Servicios</a>
                <a href="{{ url('/tarifas') }}"
                    class="{{ Request::is('tarifas') ? 'text-[#1A3878] font-bold' : 'text-gray-600 font-medium hover:text-[#1A3878] transition-colors duration-300' }}">Tarifas</a>
            </nav>
        </div>

        {{-- Lado Derecho: Botones --}}
        <div class="flex items-center justify-end flex-1">
            @guest
                {{-- Botones unidos (Regístrate e Iniciar Sesión) --}}
                <div class="flex">
                    <a href="{{ url('/registro') }}"
                        class="bg-[#1A3878] text-white py-2 px-5 border-2 border-[#1A3878] rounded-l-xl font-bold text-sm">
                        Regístrate
                    </a>
                    <a href="{{ url('/login') }}"
                        class="bg-transparent text-[#1A3878] py-2 px-5 border-2 border-[#1A3878] border-l-0 rounded-r-xl font-bold text-sm transition-colors duration-300 hover:bg-gray-50">
                        Iniciar Sesión
                    </a>
                </div>
            @endguest

            @auth
                {{-- Panel de usuario logueado --}}
                <div class="flex items-center gap-5">

                    {{-- BOTÓN EXCLUSIVO PARA ADMINISTRADORES --}}
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}"
                            class="flex items-center gap-1 text-red-600 font-bold text-base hover:text-red-800 transition-colors bg-red-50 px-3 py-1 rounded-lg border border-red-200">
                            <span class="material-symbols-outlined">admin_panel_settings</span>
                            Panel Admin
                        </a>
                    @else
                        <a href="{{ url('/perfil') }}"
                            class="flex items-center gap-1 text-[#0A1931] font-semibold text-base hover:text-[#1A3878] transition-colors">
                            <span class="material-symbols-outlined">account_circle</span>
                            Mi Perfil
                        </a>
                    @endif


                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit"
                            class="bg-[#0A1931] text-white py-2 px-[18px] rounded-full font-bold text-sm transition-colors hover:bg-[#1A3878]">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            @endauth
        </div>

    </div>
</header>
