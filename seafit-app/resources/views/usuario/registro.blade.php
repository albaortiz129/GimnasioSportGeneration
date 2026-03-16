@extends('moldes.inicio')

@section('titulo', 'Crea tu cuenta - SeaFit')

@section('contenido')
<div class="contenedor-autenticacion">
    {{-- React tomará el control de este DIV --}}
    <div id="react-root"></div>
</div>

@viteReactRefresh
@vite(['resources/js/app.jsx']) 
@endsection