@extends('moldes.inicio')

@section('titulo', 'Configuración - SeaFit')

@section('contenido')
<div class="main-container-perfil">
    {{-- BARRA LATERAL --}}
    <aside class="sidebar-perfil">
        <h2 class="sidebar-titulo">Panel de Socio</h2>
        <nav class="sidebar-nav">
            <a href="{{ route('perfil') }}" class="nav-item">
                <span class="material-symbols-outlined">person</span> Mi Perfil
            </a>
            <a href="{{ route('mis.reservas') }}" class="nav-item">
                <span class="material-symbols-outlined">calendar_month</span> Mis Reservas
            </a>
            <a href="{{ route('pago.gestion') }}" class="nav-item">
                <span class="material-symbols-outlined">payments</span> Gestión de Pago
            </a>
            <a href="{{ route('configuracion') }}" class="nav-item active">
                <span class="material-symbols-outlined">settings</span> Configuración
            </a>
        </nav>
    </aside>

    {{-- CONTENIDO PRINCIPAL --}}
    <main class="content-perfil">
        @if(session('success'))
            <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #bbf7d0; display: flex; align-items: center; gap: 10px;">
                <span class="material-symbols-outlined">check_circle</span>
                <strong>{{ session('success') }}</strong>
            </div>
        @endif

        <header class="header-bienvenida">
            <h1>¡Hola, {{ $user->nombre }}! 👋</h1>
            <p>Bienvenida a tu panel personal. Aquí puedes gestionar tu cuenta y revisar tu progreso.</p>
        </header>

        {{-- TARJETA DE MEMBRESÍA (Oscura como en tu diseño) --}}
        <section style="background-color: #0A1931; color: white; border-radius: 15px; padding: 25px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="margin: 0; font-size: 0.85rem; color: #cbd5e1;">Membresía Actual</p>
                <h2 style="margin: 5px 0; font-size: 1.8rem; color: white;">Acceso Total {{ ucfirst($user->tarifa) }}</h2>
                <p style="margin: 0; font-size: 0.85rem; color: #cbd5e1;">Válido hasta: 24/12/2026</p>
            </div>
            {{-- Enlace que lleva a Gestión de Pago --}}
            <a href="{{ route('pago.gestion') }}" style="background-color: #4ade80; color: #0A1931; font-weight: 800; padding: 12px 20px; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: transform 0.2s;">
                <span class="material-symbols-outlined" style="font-size: 20px;">upgrade</span> Cambiar Plan
            </a>
        </section>

        {{-- DATOS DE CUENTA --}}
        <section class="seccion-blanca" style="background: white; border-radius: 15px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <div class="seccion-header" style="margin-bottom: 20px;">
                <h3 style="color: #0A1931; font-size: 1.3rem; margin: 0;">Datos de Cuenta</h3>
            </div>

            {{-- VISTA 1: MODO LECTURA (Foto 1) --}}
            <div id="vista-lectura">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                    <div>
                        <p style="margin: 0; font-size: 0.9rem; color: #0A1931; font-weight: bold;">Nombre: <span style="font-weight: normal; color: #475569;">{{ $user->nombre }} {{ $user->apellidos }}</span></p>
                    </div>
                    <div>
                        <p style="margin: 0; font-size: 0.9rem; color: #0A1931; font-weight: bold;">Email: <span style="font-weight: normal; color: #475569;">{{ $user->email }}</span></p>
                    </div>
                    <div>
                        <p style="margin: 0; font-size: 0.9rem; color: #0A1931; font-weight: bold;">DNI: <span style="font-weight: normal; color: #475569;">{{ $user->dni }}</span></p>
                    </div>
                    <div>
                        <p style="margin: 0; font-size: 0.9rem; color: #0A1931; font-weight: bold;">Teléfono: <span style="font-weight: normal; color: #475569;">{{ $user->telefono }}</span></p>
                    </div>
                    <div style="grid-column: span 2;">
                        <p style="margin: 0; font-size: 0.9rem; color: #0A1931; font-weight: bold;">Domicilio: <span style="font-weight: normal; color: #475569;">{{ $user->domicilio }}</span></p>
                    </div>
                </div>
                <button onclick="activarEdicion()" style="color: #1A3878; font-weight: 700; background: none; border: none; padding: 0; cursor: pointer; text-decoration: none; font-size: 0.9rem;">
                    Editar Información
                </button>
            </div>

            {{-- VISTA 2: MODO EDICIÓN (Foto 2) --}}
            <form id="vista-edicion" action="{{ route('configuracion.actualizar') }}" method="POST" style="display: none;">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px;">Nombre</label>
                        <input type="text" name="nombre" value="{{ $user->nombre }}" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; color: #0A1931; font-family: inherit;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px;">Email</label>
                        <input type="email" name="email" value="{{ $user->email }}" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; color: #0A1931; font-family: inherit;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px;">DNI</label>
                        <input type="text" name="dni" value="{{ $user->dni }}" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; color: #0A1931; font-family: inherit;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px;">Teléfono</label>
                        <input type="text" name="telefono" value="{{ $user->telefono }}" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; color: #0A1931; font-family: inherit;">
                    </div>
                    <div style="grid-column: span 2;">
                        <label style="display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px;">Domicilio</label>
                        <input type="text" name="domicilio" value="{{ $user->domicilio }}" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; color: #0A1931; font-family: inherit;">
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px;">
                    <button type="submit" style="background-color: #1A3878; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">save</span> Guardar Cambios
                    </button>
                    <button type="button" onclick="cancelarEdicion()" style="background-color: white; color: #64748b; border: 1px solid #cbd5e1; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer;">
                        Cancelar
                    </button>
                </div>
            </form>
        </section>
    </main>
</div>

{{-- SCRIPT PARA CAMBIAR ENTRE LECTURA Y EDICIÓN --}}
<script>
    function activarEdicion() {
        document.getElementById('vista-lectura').style.display = 'none';
        document.getElementById('vista-edicion').style.display = 'block';
    }

    function cancelarEdicion() {
        document.getElementById('vista-edicion').style.display = 'none';
        document.getElementById('vista-lectura').style.display = 'block';
    }
</script>
@endsection