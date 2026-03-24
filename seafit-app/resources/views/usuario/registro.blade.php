@extends('moldes.inicio')

@section('titulo', 'Crea tu cuenta - SeaFit')

@section('contenido')
{{-- Contenedor de fondo gris claro que ocupa toda la pantalla --}}
<div class="bg-[#fcfdfe] min-h-[80vh] flex justify-center items-center py-[60px] px-5">
    {{-- React tomará el control de este DIV.--}}
    <div id="react-root" class="w-full max-w-[900px]"></div>
</div>

@viteReactRefresh
@vite(['resources/js/app.jsx']) 
@endsection