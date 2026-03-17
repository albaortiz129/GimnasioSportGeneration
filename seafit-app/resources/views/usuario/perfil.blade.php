@extends('moldes.inicio')

@section('titulo', 'Panel de Socio - SeaFit')

@section('contenido')
    <div class="main-container-perfil">
        {{-- BARRA LATERAL --}}
        <aside class="sidebar-perfil">
            <h2 class="sidebar-titulo">Panel de Socio</h2>
            <nav class="sidebar-nav">
                <a href="{{ route('perfil') }}" class="nav-item active">
                    <span class="material-symbols-outlined">person</span> Mi Perfil
                </a>
                <a href="{{ route('mis.reservas') }}" class="nav-item">
                    <span class="material-symbols-outlined">calendar_month</span> Mis Reservas
                </a>
                <a href="{{ route('pago.gestion') }}" class="nav-item">
                    <span class="material-symbols-outlined">payments</span> Gestión de Pago
                </a>
                <a href="#" class="nav-item">
                    <span class="material-symbols-outlined">settings</span> Configuración
                </a>
            </nav>
        </aside>

        {{-- CONTENIDO PRINCIPAL --}}
        <main class="content-perfil">
            {{-- Mensajes de éxito al cancelar --}}
            @if(session('success'))
                <div
                    style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #bbf7d0;">
                    {{ session('success') }}
                </div>
            @endif

            <header class="header-bienvenida">
                <h1>¡Hola, {{ $user->nombre }}! 👋</h1>
                <p>Bienvenido a tu panel personal. Aquí puedes gestionar tu cuenta y revisar tu progreso.</p>
            </header>

            {{-- TARJETA DE MEMBRESÍA --}}
            <section class="tarjeta-membresia">
                <div class="membresia-info">
                    <p class="label-membresia">Membresía Actual</p>
                    <h2 class="tipo-membresia">Acceso Total {{ ucfirst($user->tarifa) }}</h2>
                    <p class="validez-membresia">Válido hasta: 24/12/2025</p>
                </div>
                <button class="btn-cambiar-plan">
                    <span class="material-symbols-outlined">upgrade</span> Cambiar Plan
                </button>
            </section>

            {{-- DATOS DE CUENTA --}}
            <section class="seccion-blanca">
                <div class="seccion-header">
                    <h3>Datos de Cuenta</h3>
                </div>
                <div class="grid-datos">
                    <div class="dato-item">
                        <p class="dato-label">Nombre:</p>
                        <p class="dato-valor">{{ $user->nombre }} {{ $user->apellidos }}</p>
                    </div>
                    <div class="dato-item">
                        <p class="dato-label">Email:</p>
                        <p class="dato-valor">{{ $user->email }}</p>
                    </div>
                    <div class="dato-item">
                        <p class="dato-label">DNI:</p>
                        <p class="dato-valor">{{ $user->dni }}</p>
                    </div>
                    <div class="dato-item">
                        <p class="dato-label">Teléfono:</p>
                        <p class="dato-valor">{{ $user->telefono }}</p>
                    </div>
                    <div class="dato-item full-width">
                        <p class="dato-label">Domicilio:</p>
                        <p class="dato-valor">{{ $user->domicilio }}</p>
                    </div>
                </div>
                <a href="#" class="enlace-editar">Editar información</a>
            </section>

            {{-- MIS RESERVAS --}}
            <section class="seccion-blanca">
                <div class="seccion-header">
                    <h3>Mis Reservas ({{ $user->clases->count() }})</h3>
                </div>

                <div class="lista-reservas">
                    @forelse($user->clases as $clase)
                        <div class="reserva-card"
                            style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #eee;">
                            <div class="reserva-info">
                                <h4 style="margin: 0; color: #0A1931;">{{ $clase->nombre }} ({{ $clase->sala }})</h4>
                                <p style="margin: 5px 0 0 0; color: #64748b; font-size: 0.9rem;">
                                    {{ $clase->dia_semana }} | {{ substr($clase->hora_inicio, 0, 5) }} h
                                </p>
                            </div>
                            <div style="display: flex; gap: 15px; align-items: center;">
                                <span class="status-badge confirmado"
                                    style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 15px; font-size: 0.75rem; font-weight: bold;">
                                    ✓ Confirmada
                                </span>

                                {{-- Formulario para que el botón de cancelar funcione de verdad --}}
                                <form action="{{ route('clase.cancelar', $clase->id) }}" method="POST"
                                    onsubmit="return confirm('¿Quieres cancelar esta reserva?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        style="color: #ef4444; background: none; border: none; font-weight: bold; cursor: pointer; text-decoration: underline; font-size: 0.85rem;">
                                        Cancelar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 30px;">
                            <p style="color: #64748b;">No tienes reservas para esta semana.</p>
                            <a href="{{ route('agenda') }}" style="color: #1A3878; font-weight: bold;">Ver horario de clases</a>
                        </div>
                    @endforelse
                </div>
                @if($user->clases->count() > 0)
                    <a href="{{ route('mis.reservas') }}" class="enlace-ver-mas">Ver todas mis reservas</a>
                @endif
            </section>
        </main>
    </div>
@endsection