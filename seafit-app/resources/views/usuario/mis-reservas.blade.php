@extends('moldes.inicio')

@section('titulo', 'Mis Reservas - SeaFit')

@section('contenido')
<div class="main-container-perfil">
    {{-- BARRA LATERAL --}}
    <aside class="sidebar-perfil">
        <h2 class="sidebar-titulo">Panel de Socio</h2>
        <nav class="sidebar-nav">
            <a href="{{ route('perfil') }}" class="nav-item">
                <span class="material-symbols-outlined">person</span> Mi Perfil
            </a>
            <a href="{{ route('mis.reservas') }}" class="nav-item active">
                <span class="material-symbols-outlined">calendar_month</span> Mis Reservas
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
        {{-- Bloque de Alertas de Éxito o Info --}}
        @if(session('success'))
            <div style="background-color: #dcfce7; color: #166534; padding: 15px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #bbf7d0; display: flex; align-items: center; gap: 10px;">
                <span class="material-symbols-outlined">check_circle</span>
                <strong>{{ session('success') }}</strong>
            </div>
        @endif

        <header class="header-bienvenida">
            <h1>Mis Reservas</h1>
            <p>Aquí puedes ver y gestionar tus próximas clases y sesiones reservadas.</p>
        </header>

        <section class="seccion-blanca">
            <div class="seccion-header">
                <h3>Próximas Clases ({{ $user->clases->count() }})</h3>
            </div>
            
            <div class="lista-reservas">
                @forelse($user->clases as $clase)
                    <div class="reserva-card" style="display: flex; justify-content: space-between; align-items: center; padding: 20px; border: 1px solid #e2e8f0; border-radius: 15px; margin-bottom: 15px;">
                        <div class="reserva-info">
                            <h4 style="font-size: 1.1rem; color: #0A1931; margin: 0;">{{ $clase->nombre }} ({{ $clase->sala }})</h4>
                            <p style="color: #64748b; margin: 5px 0 0 0;">{{ $clase->dia_semana }} | {{ substr($clase->hora_inicio, 0, 5) }} h</p>
                        </div>
                        
                        <div style="display: flex; gap: 20px; align-items: center;">
                            <span class="status-badge confirmado" style="background: #dcfce7; color: #166534; padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">
                                ✓ Confirmada
                            </span>

                            {{-- FORMULARIO DE CANCELACIÓN REAL --}}
                            <form action="{{ route('clase.cancelar', $clase->id) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres cancelar tu plaza en {{ $clase->nombre }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: none; border: none; color: #ef4444; font-size: 0.85rem; font-weight: 700; text-decoration: underline; cursor: pointer; padding: 0;">
                                    Cancelar
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 40px;">
                        <p style="color: #64748b;">No tienes clases reservadas actualmente.</p>
                        <a href="{{ route('agenda') }}" class="btn-reg" style="display: inline-block; margin-top: 15px; text-decoration: none; background-color: #1A3878; color: white; padding: 10px 20px; border-radius: 8px;">Ir a la Agenda</a>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- SECCIÓN DE HISTORIAL --}}
        <section class="seccion-blanca" style="opacity: 0.6;">
            <div class="seccion-header">
                <h3>Reservas Anteriores</h3>
            </div>
            <div class="reserva-card" style="background: #f1f5f9; border: 1px solid #e2e8f0; padding: 15px; border-radius: 12px;">
                <div class="reserva-info">
                    <h4 style="color: #475569; margin: 0;">Spinning Avanzado</h4>
                    <p style="margin: 5px 0 0 0;">24 Nov | 10:30 h</p>
                </div>
                <span style="color: #64748b; font-size: 0.8rem; font-weight: bold; float: right; margin-top: -25px;">Finalizada</span>
            </div>
        </section>
    </main>
</div>
@endsection