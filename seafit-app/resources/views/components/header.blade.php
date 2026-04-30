{{-- Cabecera principal: logo, menú y acceso de sesión. --}}
<header class="w-full bg-white shadow-sm">
    <div
        class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 md:gap-0 max-w-[1200px] mx-auto py-2 md:py-2 px-4 sm:px-5">

        {{-- Logo --}}
        <div class="w-full md:flex-1 flex justify-center md:justify-start">
            <a href="{{ url('/') }}" class="block">
                <img src="{{ asset('imagenes/logo-header.png') }}" alt="Sea Fit"
                    class="h-[40px] sm:h-[44px] block object-contain">
            </a>
        </div>

        {{-- Menú central --}}
        <div class="w-full md:flex-1 flex justify-center">
            <nav class="flex flex-wrap justify-center gap-x-4 gap-y-2 sm:gap-[25px] text-sm sm:text-base">
                <a href="{{ url('/') }}"
                    class="{{ Request::is('/') ? 'text-[#1A3878] font-bold' : 'text-gray-600 font-medium hover:text-[#1A3878] transition-colors duration-300' }}">Inicio</a>
                <a href="{{ url('/servicios') }}"
                    class="{{ Request::is('servicios') ? 'text-[#1A3878] font-bold' : 'text-gray-600 font-medium hover:text-[#1A3878] transition-colors duration-300' }}">Servicios</a>
                <a href="{{ url('/tarifas') }}"
                    class="{{ Request::is('tarifas') ? 'text-[#1A3878] font-bold' : 'text-gray-600 font-medium hover:text-[#1A3878] transition-colors duration-300' }}">Tarifas</a>
            </nav>
        </div>

        {{-- Zona derecha: auth/admin --}}
        <div class="w-full md:flex-[1.2] flex items-center justify-center md:justify-end">
            @guest
                <div class="flex">
                    <a href="{{ url('/registro') }}"
                        class="bg-[#1A3878] text-white py-2 px-4 sm:px-5 border-2 border-[#1A3878] rounded-l-xl font-bold text-xs sm:text-sm">
                        Regístrate
                    </a>
                    <a href="{{ url('/login') }}"
                        class="bg-transparent text-[#1A3878] py-2 px-4 sm:px-5 border-2 border-[#1A3878] border-l-0 rounded-r-xl font-bold text-xs sm:text-sm transition-colors duration-300 hover:bg-gray-50">
                        Iniciar sesión
                    </a>
                </div>
            @endguest

            @auth
                <div
                    class="flex flex-wrap md:flex-nowrap items-center justify-center md:justify-end gap-2 sm:gap-3 whitespace-nowrap">
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}"
                            class="inline-flex items-center gap-1 text-red-600 font-bold text-xs sm:text-sm hover:text-red-800 transition-colors bg-red-50 px-3 py-2 rounded-full border border-red-200">
                            <span class="material-symbols-outlined">admin_panel_settings</span>
                            Panel Admin
                        </a>
                    @else
                        {{-- Acceso directo al perfil (restaurado como estaba antes). --}}
                        <a href="{{ url('/perfil') }}"
                            class="inline-flex items-center gap-1.5 text-[#0A1931] font-semibold text-sm sm:text-base hover:text-[#1A3878] transition-colors whitespace-nowrap leading-none shrink-0">
                            <span class="material-symbols-outlined text-[22px] leading-none">account_circle</span>
                            Mi Perfil
                        </a>
                    @endif

                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit"
                            class="bg-[#0A1931] text-white py-2 px-4 sm:px-[18px] rounded-full font-bold text-xs sm:text-sm transition-colors hover:bg-[#1A3878]">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            @endauth
        </div>

    </div>
</header>

@auth
    @if(!auth()->user()->is_admin && auth()->user()->isPlanActive())
        {{-- Modal QR para socios con cuenta activa. --}}
        <div id="modalQrHeader"
            class="fixed inset-0 bg-black/70 backdrop-blur-md z-[120] hidden flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl p-6 md:p-8 max-w-sm w-full shadow-2xl text-center">
                <h3 class="text-2xl font-black text-[#0A1931] mb-2">Tu QR</h3>

                <div class="bg-white border border-gray-200 rounded-2xl p-4 inline-block">
                    <img id="qrImgHeader" width="220" height="220" alt="QR" class="w-[220px] h-[220px] object-contain" />
                </div>

                <button type="button" id="cerrarQrHeader"
                    class="mt-5 w-full bg-[#0A1931] text-white py-3 rounded-xl font-bold hover:bg-[#1A3878] transition-colors">
                    Cerrar
                </button>
            </div>
        </div>

        <script>
            (() => {
                // Elementos del modal e imagen QR.
                const cerrarBtn = document.getElementById('cerrarQrHeader');
                const modal = document.getElementById('modalQrHeader');
                const qrImg = document.getElementById('qrImgHeader');

                // ID del usuario actual para que su QR sea único.
                const userId = @json(auth()->id());
                let intervaloQr = null;

                // Crea el texto interno que se codifica en el QR.
                function crearTextoQr() {
                    const random = Math.random().toString(36).slice(2, 10).toUpperCase();
                    return `SEAFIT-CHECKIN|USER:${userId}|TS:${Date.now()}|RND:${random}`;
                }

                // Solicita una imagen QR nueva a la API pública.
                function actualizarQr() {
                    if (!qrImg) return;
                    const data = encodeURIComponent(crearTextoQr());
                    qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${data}&margin=0`;
                }

                // Abre el modal y refresca el QR cada 20 segundos.
                function abrirModal() {
                    if (!modal) return;
                    modal.classList.remove('hidden');
                    actualizarQr();
                    clearInterval(intervaloQr);
                    intervaloQr = setInterval(actualizarQr, 20000);
                }

                // Cierra el modal y para el refresco automático.
                function cerrarModal() {
                    if (!modal) return;
                    modal.classList.add('hidden');
                    clearInterval(intervaloQr);
                    intervaloQr = null;
                }

                // Exponemos apertura global para usar el QR desde el botón del inicio.
                window.openGymQrModal = abrirModal;

                // Evento de cerrar.
                cerrarBtn?.addEventListener('click', cerrarModal);

                // Cierra también si haces clic fuera del cuadro.
                modal?.addEventListener('click', (event) => {
                    if (event.target === modal) cerrarModal();
                });
            })();
        </script>
    @endif
@endauth