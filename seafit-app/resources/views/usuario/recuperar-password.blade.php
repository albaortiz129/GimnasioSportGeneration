@extends('moldes.inicio')

@section('titulo', 'Recuperar Contraseña - SeaFit')

@section('contenido')
<div class="contenedor-autenticacion">
    <div class="tarjeta-login">
        <h1>Recuperar Contraseña</h1>
        <p class="subtitulo-login">Introduce tu email y te enviaremos un enlace para crear una nueva contraseña.</p>

        {{-- Alerta de éxito (cuando se envía el correo) --}}
        @if (session('success'))
            <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align: center; font-weight: bold;">
                ✓ {{ session('success') }}
            </div>
        @endif

        {{-- Alerta de error (si el email no existe) --}}
        @if ($errors->any())
            <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- FORMULARIO --}}
        <form action="{{ route('password.email') }}" method="POST" class="formulario-login">
            @csrf
            
            <div class="grupo-campo">
                <label>Correo Electrónico</label>
                <input type="email" name="email" placeholder="tu@email.com" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1;">
            </div>

            <button type="submit" class="boton-enviar-login" style="margin-top: 10px;">Enviar Enlace de Recuperación</button>
        </form>

        <p class="pie-tarjeta-login" style="margin-top: 20px;">
            <a href="{{ route('login') }}">← Volver al inicio de sesión</a>
        </p>
    </div>
</div>
@endsection