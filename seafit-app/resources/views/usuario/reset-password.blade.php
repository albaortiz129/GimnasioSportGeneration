@extends('moldes.inicio')

@section('titulo', 'Crear Nueva Contraseña - SeaFit')

@section('contenido')
<div class="contenedor-autenticacion">
    <div class="tarjeta-login">
        <h1>Nueva Contraseña</h1>
        <p class="subtitulo-login">Crea una nueva contraseña para tu cuenta.</p>

        {{-- Alertas de error --}}
        @if ($errors->any())
            <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST" class="formulario-login">
            @csrf
            
            {{-- Pasamos el token oculto que viene por la URL --}}
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="grupo-campo">
                <label>Correo Electrónico</label>
                <input type="email" name="email" value="{{ request()->email }}" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1;">
            </div>

            <div class="grupo-campo">
                <label>Nueva Contraseña</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1;">
            </div>

            <div class="grupo-campo">
                <label>Confirmar Contraseña</label>
                <input type="password" name="password_confirmation" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1;">
            </div>

            <button type="submit" class="boton-enviar-login" style="margin-top: 10px;">Guardar Nueva Contraseña</button>
        </form>
    </div>
</div>
@endsection