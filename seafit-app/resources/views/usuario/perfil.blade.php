@extends('moldes.inicio')

@section('titulo', 'Panel de Socio - SeaFit')

@section('contenido')
<div class="main-container-perfil">
    {{-- BARRA LATERAL --}}
    <aside class="sidebar-perfil">
        <h2 class="sidebar-titulo">Panel de Socio</h2>
        <nav class="sidebar-nav">
            <a href="#" class="nav-item active">
                <span class="material-symbols-outlined">person</span> Mi Perfil
            </a>
            <a href="{{ route('agenda') }}" class="nav-item">
                <span class="material-symbols-outlined">calendar_month</span> Reservar Clase
            </a>
            <a href="#" class="nav-item">
                <span class="material-symbols-outlined">payments</span> Gestión de Pago
            </a>
            <a href="#" class="nav-item">
                <span class="material-symbols-outlined">settings</span> Configuración
            </a>
        </nav>
    </aside>

    {{-- CONTENIDO PRINCIPAL --}}
    <main class="content-perfil">
        <header class="header-bienvenida">
            <h1>¡Hola, {{ $user->nombre }}! 👋</h1>
            <p>Bienvenido a tu panel personal de SeaFit. Aquí puedes gestionar tu cuenta y revisar tus clases.</p>
        </header>

        {{-- TARJETA DE MEMBRESÍA --}}
        <section class="tarjeta-membresia">
            <div class="membresia-info">
                <p class="label-membresia">Membresía Actual</p>
                <h2 class="tipo-membresia">Acceso Total {{ ucfirst($user->tarifa) }}</h2>
                <p class="validez-membresia">Estado: <span style="color: #a3e635; font-weight: bold;">Activa</span></p>
            </div>
            <button class="btn-cambiar-plan">
                <span class="material-symbols-outlined">upgrade</span> Cambiar Plan
            </button>
        </section>

        {{-- DATOS DE CUENTA DINÁMICOS --}}
        <section class="seccion-blanca">
            <div class="seccion-header">
                <h3>Datos de Cuenta</h3>
            </div>
            <div class="grid-datos">
                <div class="dato-item">
                    <p class="dato-label">Nombre completo:</p>
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

        {{-- PRÓXIMAS RESERVAS REALES --}}
        <section class="seccion-blanca">
            <div class="seccion-header">
                <h3>Mis Reservas Actuales</h3>
            </div>
            <div class="lista-reservas">
                @forelse($user->clases as $reserva)
                    <div class="reserva-card">
                        <div class="reserva-info">
                            <h4>{{ $reserva->nombre }}</h4>
                            <p>{{ $reserva->dia_semana }} | {{ substr($reserva->hora_inicio, 0, 5) }} h | {{ $reserva->sala }}</p>
                        </div>
                        <span class="status-badge confirmado">✓ Confirmada</span>
                    </div>
                @empty
                    <div style="text-align: center; padding: 20px;">
                        <p style="color: #64748b; margin-bottom: 15px;">Aún no tienes clases reservadas para esta semana.</p>
                        <a href="{{ route('agenda') }}" class="btn-reg" style="text-decoration: none; display: inline-block;">Ver Agenda</a>
                    </div>
                @endforelse
            </div>
            @if($user->clases->count() > 0)
                <a href="#" class="enlace-ver-mas">Ver historial de clases</a>
            @endif
        </section>
    </main>
</div>
@endsection