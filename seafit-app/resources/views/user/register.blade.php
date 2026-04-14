{{-- Vista contenedora del formulario de registro implementado en React. --}}
@extends('layouts.app')

@section('titulo', 'Crea tu cuenta - SeaFit')

@section('contenido')
    {{--
    Esta vista solo pinta el contenedor.
    El formulario completo se monta desde resources/js/componentes/formularioRegistro.jsx
    --}}
    <div class="bg-[#fcfdfe] min-h-[80vh] flex justify-center items-center py-[60px] px-5">
        {{-- Nodo donde React monta el formulario de registro. --}}
        <div id="react-root" class="w-full max-w-[900px]"></div>
    </div>
@endsection
