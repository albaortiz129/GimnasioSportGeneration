@extends('moldes.inicio')

@section('titulo', 'Configuración - SeaFit')

@section('contenido')
<div class="flex flex-col md:flex-row min-h-screen bg-[#f8fafc] font-sans">
    
    {{-- BARRA LATERAL --}}
    <aside class="w-full md:w-[280px] md:min-w-[280px] bg-white p-6 md:p-8 border-b md:border-b-0 md:border-r border-gray-200">
        <h2 class="text-xl font-extrabold text-[#0A1931] mb-8">Panel de Socio</h2>
        <nav class="flex flex-col gap-2">
            <a href="{{ route('perfil') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#0A1931]">
                <span class="material-symbols-outlined">person</span> Mi Perfil
            </a>
            <a href="{{ route('mis.reservas') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#0A1931]">
                <span class="material-symbols-outlined">calendar_month</span> Mis Reservas
            </a>
            <a href="{{ route('pago.gestion') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#0A1931]">
                <span class="material-symbols-outlined">payments</span> Gestión de Pago
            </a>
            {{-- Link Activo --}}
            <a href="{{ route('configuracion') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors bg-[#e6f3ff] text-[#1A3878]">
                <span class="material-symbols-outlined">settings</span> Configuración
            </a>
        </nav>
    </aside>

    {{-- CONTENIDO PRINCIPAL --}}
    <main class="flex-1 p-6 md:p-10 lg:p-12 max-w-[1000px]">
        
        @if(session('success'))
            <div class="bg-green-100 text-green-800 p-4 rounded-xl mb-6 border border-green-200 font-medium flex items-center gap-3">
                <span class="material-symbols-outlined">check_circle</span>
                <strong>{{ session('success') }}</strong>
            </div>
        @endif

        <header class="mb-8">
            <h1 class="text-3xl md:text-4xl font-black text-[#0A1931] mb-2">¡Hola, {{ $user->nombre }}! 👋</h1>
            <p class="text-gray-500 text-[15px]">Bienvenida a tu panel personal. Aquí puedes gestionar tu cuenta y revisar tu progreso.</p>
        </header>

        {{-- TARJETA DE MEMBRESÍA --}}
        <section class="bg-[#0A1931] text-white p-6 md:p-8 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 mb-10 shadow-lg relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full -mr-10 -mt-10 pointer-events-none"></div>
            
            <div class="relative z-10">
                <p class="text-xs uppercase tracking-widest text-gray-300 font-bold mb-1">Membresía Actual</p>
                <h2 class="text-2xl md:text-3xl font-bold mb-1">Acceso Total {{ ucfirst($user->tarifa) }}</h2>
                <p class="text-sm text-gray-400">Válido hasta: 24/12/2026</p>
            </div>
            
            <a href="{{ route('pago.gestion') }}" class="relative z-10 bg-[#a3e635] text-[#0A1931] px-6 py-3 rounded-xl font-bold flex items-center gap-2 hover:scale-105 transition-transform duration-300 shadow-md no-underline">
                <span class="material-symbols-outlined">upgrade</span> Cambiar Plan
            </a>
        </section>

        {{-- DATOS DE CUENTA --}}
        <section class="bg-white rounded-2xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-[#0A1931]">Datos de Cuenta</h3>
            </div>

            {{-- VISTA 1: MODO LECTURA --}}
            <div id="vista-lectura">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-sm font-bold text-[#0A1931] m-0">Nombre: <span class="font-normal text-gray-500">{{ $user->nombre }} {{ $user->apellidos }}</span></p>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-[#0A1931] m-0">Email: <span class="font-normal text-gray-500">{{ $user->email }}</span></p>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-[#0A1931] m-0">DNI: <span class="font-normal text-gray-500">{{ $user->dni }}</span></p>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-[#0A1931] m-0">Teléfono: <span class="font-normal text-gray-500">{{ $user->telefono }}</span></p>
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <p class="text-sm font-bold text-[#0A1931] m-0">Domicilio: <span class="font-normal text-gray-500">{{ $user->domicilio }}</span></p>
                    </div>
                </div>
                <button onclick="activarEdicion()" class="text-[#1A3878] font-bold bg-transparent border-none p-0 cursor-pointer text-sm underline hover:text-[#0A1931] transition-colors">
                    Editar Información
                </button>
            </div>

            {{-- VISTA 2: MODO EDICIÓN --}}
            <form id="vista-edicion" action="{{ route('configuracion.actualizar') }}" method="POST" class="hidden">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
                    <div>
                        <label class="block text-sm text-gray-500 font-semibold mb-1">Nombre</label>
                        <input type="text" name="nombre" value="{{ $user->nombre }}" class="w-full p-3 border border-gray-300 rounded-xl text-[#0A1931] focus:ring-2 focus:ring-[#1A3878] outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-500 font-semibold mb-1">Email</label>
                        <input type="email" name="email" value="{{ $user->email }}" class="w-full p-3 border border-gray-300 rounded-xl text-[#0A1931] focus:ring-2 focus:ring-[#1A3878] outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-500 font-semibold mb-1">DNI</label>
                        <input type="text" name="dni" value="{{ $user->dni }}" class="w-full p-3 border border-gray-300 rounded-xl text-[#0A1931] focus:ring-2 focus:ring-[#1A3878] outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-500 font-semibold mb-1">Teléfono</label>
                        <input type="text" name="telefono" value="{{ $user->telefono }}" class="w-full p-3 border border-gray-300 rounded-xl text-[#0A1931] focus:ring-2 focus:ring-[#1A3878] outline-none transition-all">
                    </div>
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-sm text-gray-500 font-semibold mb-1">Domicilio</label>
                        <input type="text" name="domicilio" value="{{ $user->domicilio }}" class="w-full p-3 border border-gray-300 rounded-xl text-[#0A1931] focus:ring-2 focus:ring-[#1A3878] outline-none transition-all">
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" class="bg-[#1A3878] text-white px-6 py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition-colors hover:bg-[#0A1931]">
                        <span class="material-symbols-outlined text-[18px]">save</span> Guardar Cambios
                    </button>
                    <button type="button" onclick="cancelarEdicion()" class="bg-white text-gray-500 border border-gray-300 px-6 py-3 rounded-xl font-bold transition-colors hover:bg-gray-50 hover:text-[#0A1931]">
                        Cancelar
                    </button>
                </div>
            </form>
        </section>
    </main>
</div>

<script>
    function activarEdicion() {
        document.getElementById('vista-lectura').classList.add('hidden');
        document.getElementById('vista-edicion').classList.remove('hidden');
        document.getElementById('vista-edicion').classList.add('block');
    }

    function cancelarEdicion() {
        document.getElementById('vista-edicion').classList.add('hidden');
        document.getElementById('vista-edicion').classList.remove('block');
        document.getElementById('vista-lectura').classList.remove('hidden');
    }
</script>
@endsection