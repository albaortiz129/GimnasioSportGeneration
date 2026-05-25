{{-- Inicio de sesión. --}}
@extends('layouts.app')

@section('titulo', 'Inicia sesión')

@section('contenido')
    {{-- Contenedor de autenticación --}}
    <div class="bg-[#fcfdfe] min-h-[80vh] flex justify-center items-center py-10 px-5">

        {{-- Tarjeta Login --}}
        <div
            class="bg-white w-full max-w-[450px] p-10 sm:p-12 rounded-[20px] shadow-[0_10px_40px_rgba(0,0,0,0.03)] text-center">

            <h1 class="text-[#265e1f] text-[32px] font-extrabold mb-1">Inicia sesión</h1>
            <p class="text-gray-500 mb-8 text-[15px]">Accede a tu cuenta Sport Generation.</p>

            {{-- Alertas de error si el login falla --}}
            @if ($errors->any())
                <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-5 text-sm text-left">
                    @foreach ($errors->all() as $error)
                        <p class="m-0">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Formulario --}}
            <form action="{{ url('/login') }}" method="POST" class="text-left">
                @csrf

                <div class="mb-6">
                    <label class="block font-semibold mb-2.5 text-[14px] text-gray-800">Email</label>
                    <input type="email" name="email" placeholder="tu@email.com" value="{{ old('email') }}" required
                        class="w-full p-4 border border-gray-200 rounded-xl bg-[#fdfdfd] text-[14px] outline-none focus:border-[#265e1f] focus:ring-1 focus:ring-[#265e1f] transition-all">
                </div>

                <div class="mb-6">
                    <label class="block font-semibold mb-2.5 text-[14px] text-gray-800">Contraseña</label>
                    <div class="relative flex items-center">
                        <input type="password" name="password" id="passInput" placeholder="••••••" required
                            class="w-full p-4 pr-10 border border-gray-200 rounded-xl bg-[#fdfdfd] text-[14px] outline-none focus:border-[#265e1f] focus:ring-1 focus:ring-[#265e1f] transition-all">

                        {{-- Icono del ojo --}}
                        <span onclick="togglePass()"
                            class="absolute right-4 cursor-pointer text-gray-400 hover:text-gray-600 transition-colors">
                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                        </span>
                    </div>

                    <a href="{{ route('password.request') }}"
                        class="block text-right text-[12px] text-[#265e1f] no-underline mt-2 font-semibold hover:underline">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                {{-- Botón Enviar --}}
                <button type="submit"
                    class="w-full bg-[#265e1f] text-white p-4 border-none rounded-xl text-[16px] font-bold cursor-pointer my-5 transition-colors duration-300 hover:bg-[#265e1f]">
                    Iniciar Sesión
                </button>
            </form>

            {{-- Pie de Tarjeta --}}
            <p class="text-[13px] text-gray-500 m-0">
                ¿Aún no tienes cuenta?
                <a href="{{ url('/registro') }}" class="text-[#265e1f] font-bold no-underline hover:underline">Regístrate
                    aquí</a>
            </p>
        </div>
    </div>

    <script>
        function togglePass() {
            const x = document.getElementById("passInput");
            const icon = document.querySelector(".material-symbols-outlined");

            if (x.type === "password") {
                x.type = "text";
                icon.innerText = "visibility_off"; // Cambia el icono cuando se ve la contraseña
            } else {
                x.type = "password";
                icon.innerText = "visibility"; // Vuelve al icono normal
            }
        }
    </script>
@endsection