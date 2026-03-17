@extends('moldes.inicio')

@section('titulo', 'Añadir Método - SeaFit')

@section('contenido')
<div class="main-container-perfil">
    <main class="content-perfil" style="text-align: center; padding: 50px;">
        <span class="material-symbols-outlined" style="font-size: 60px; color: #1A3878;">credit_score</span>
        <h2>Añadir Método de Pago</h2>
        <p>Próximamente integraremos la pasarela de pago seguro aquí.</p>
        <a href="{{ route('pago.gestion') }}" class="btn-reg">Volver a mis pagos</a>
    </main>
</div>
@endsection