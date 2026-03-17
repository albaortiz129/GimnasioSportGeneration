<header class="site-header">
    <div class="container">
        <div class="left-side">
            <a href="{{ url('/') }}" class="logo">
                <img src="{{ asset('imagenes/Logo transparente.png') }}" alt="Sea Fit">
            </a>
        </div>

        <div class="center">
            <nav class="nav-links">
                <a href="{{ url('/') }}" class="{{ Request::is('/') ? 'active' : '' }}">Inicio</a>
                <a href="{{ url('/servicios') }}" class="{{ Request::is('servicios') ? 'active' : '' }}">Servicios</a>
                <a href="{{ url('/tarifas') }}" class="{{ Request::is('tarifas') ? 'active' : '' }}">Tarifas</a>
            </nav>
        </div>

        <div class="right-group">
            @guest
                {{-- Esto solo se muestra si NO están logueados --}}
                <a href="{{ url('/registro') }}" class="btn-reg">Regístrate</a>
                <a href="{{ url('/login') }}" class="btn-login">Iniciar Sesión</a>
            @endguest

            @auth
                {{-- Esto solo se muestra si están logueados --}}
                <div class="user-logged-nav" style="display: flex; align-items: center; gap: 20px;">
                    <a href="{{ url('/perfil') }}" class="user-profile-link" style="display: flex; align-items: center; gap: 5px; text-decoration: none; color: #0A1931; font-weight: 600;">
                        <span class="material-symbols-outlined">account_circle</span>
                        Mi Perfil
                    </a>
                    
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn-logout" style="background-color: #0A1931; color: white; padding: 8px 18px; border-radius: 25px; border: none; font-weight: 700; cursor: pointer; font-size: 0.9rem;">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</header>