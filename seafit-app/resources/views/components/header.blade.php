{{-- Cabecera principal: logo, menú y acceso de sesión. --}}
<header class="w-full bg-white shadow-sm">
    <div class="flex justify-between items-center max-w-[1200px] mx-auto py-[10px] px-5">

        {{-- Logo --}}
        <div class="flex-1">
            <a href="{{ url('/') }}" class="block">
                <img src="{{ asset('imagenes/Logo transparente.png') }}" alt="Sea Fit" class="h-[55px] block">
            </a>
        </div>

        {{-- Menú central --}}
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

        {{-- Zona derecha: auth/admin --}}
        <div class="flex items-center justify-end flex-1">
            @guest
                <div class="flex">
                    <a href="{{ url('/registro') }}"
                        class="bg-[#1A3878] text-white py-2 px-5 border-2 border-[#1A3878] rounded-l-xl font-bold text-sm">
                        Regístrate
                    </a>
                    <a href="{{ url('/login') }}"
                        class="bg-transparent text-[#1A3878] py-2 px-5 border-2 border-[#1A3878] border-l-0 rounded-r-xl font-bold text-sm transition-colors duration-300 hover:bg-gray-50">
                        Iniciar sesión
                    </a>
                </div>
            @endguest

            @auth
                <div class="flex items-center gap-5">
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}"
                            class="flex items-center gap-1 text-red-600 font-bold text-base hover:text-red-800 transition-colors bg-red-50 px-3 py-1 rounded-lg border border-red-200">
                            <span class="material-symbols-outlined">admin_panel_settings</span>
                            Panel Admin
                        </a>
                    @else
                        <button type="button" id="abrirQrHeader"
                            class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-[#1A3878]/30 text-[#1A3878] hover:bg-[#1A3878]/10 transition-colors"
                            title="Mostrar QR">
                            <span class="material-symbols-outlined text-[18px]">qr_code_2</span>
                        </button>
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

@auth
    @if(!auth()->user()->is_admin)
        {{-- Modal QR para socios --}}
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
                const abrirBtn = document.getElementById('abrirQrHeader');
                const cerrarBtn = document.getElementById('cerrarQrHeader');
                const modal = document.getElementById('modalQrHeader');
                const qrImg = document.getElementById('qrImgHeader');
                const userId = @json(auth()->id());
                let intervaloQr = null;

                function crearTextoQr() {
                    const random = Math.random().toString(36).slice(2, 10).toUpperCase();
                    return `SEAFIT-CHECKIN|USER:${userId}|TS:${Date.now()}|RND:${random}`;
                }

                function actualizarQr() {
                    if (!qrImg) return;
                    const data = encodeURIComponent(crearTextoQr());
                    qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${data}&margin=0`;
                }

                function abrirModal() {
                    if (!modal) return;
                    modal.classList.remove('hidden');
                    actualizarQr();
                    clearInterval(intervaloQr);
                    intervaloQr = setInterval(actualizarQr, 20000);
                }

                function cerrarModal() {
                    if (!modal) return;
                    modal.classList.add('hidden');
                    clearInterval(intervaloQr);
                    intervaloQr = null;
                }

                abrirBtn?.addEventListener('click', abrirModal);
                cerrarBtn?.addEventListener('click', cerrarModal);

                modal?.addEventListener('click', (event) => {
                    if (event.target === modal) cerrarModal();
                });
            })();
        </script>
    @endif
@endauth