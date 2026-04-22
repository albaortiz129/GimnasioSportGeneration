{{-- Vista de configuración del socio para editar datos de cuenta. --}}
@extends('layouts.app')

@section('titulo', 'Configuración - SeaFit')

@section('contenido')
    <div class="flex flex-col md:flex-row min-h-screen bg-[#f8fafc] font-sans">

        {{-- BARRA LATERAL --}}
        <aside
            class="w-full md:w-[280px] md:min-w-[280px] bg-white p-6 md:p-8 border-b md:border-b-0 md:border-r border-gray-200">
            <h2 class="text-xl font-extrabold text-[#0A1931] mb-8">Panel de Socio</h2>
            <nav class="flex flex-col gap-2">
                <a href="{{ route('perfil') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#0A1931]">
                    <span class="material-symbols-outlined">person</span> Mi Perfil
                </a>
                <a href="{{ route('mis.reservas') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#0A1931]">
                    <span class="material-symbols-outlined">calendar_month</span> Mis Reservas
                </a>
                <a href="{{ route('pago.gestion') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-[#0A1931]">
                    <span class="material-symbols-outlined">payments</span> Gestión de Pago
                </a>
                {{-- Enlace activo: página de configuración --}}
                <a href="{{ route('configuracion') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors bg-[#e6f3ff] text-[#1A3878]">
                    <span class="material-symbols-outlined">settings</span> Configuración
                </a>
            </nav>
        </aside>

        {{-- CONTENIDO PRINCIPAL --}}
        <main class="flex-1 p-6 md:p-10 lg:p-12 max-w-[1000px]">

            @if(session('success'))
                <div
                    class="bg-green-100 text-green-800 p-4 rounded-xl mb-6 border border-green-200 font-medium flex items-center gap-3">
                    <span class="material-symbols-outlined">check_circle</span>
                    <strong>{{ session('success') }}</strong>
                </div>
            @endif

            <header class="mb-8">
                <h1 class="text-3xl md:text-4xl font-black text-[#0A1931] mb-2">Hola, {{ $user->nombre }}!</h1>
                <p class="text-gray-500 text-[15px]">Bienvenida a tu panel personal. Aquí puedes gestionar tu cuenta y
                    revisar tu progreso.</p>
            </header>

            {{-- Tarjeta resumen de la membresía actual --}}
            <section
                class="bg-[#0A1931] text-white p-6 md:p-8 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 mb-10 shadow-lg relative overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full -mr-10 -mt-10 pointer-events-none">
                </div>

                <div class="relative z-10">
                    <p class="text-xs uppercase tracking-widest text-gray-300 font-bold mb-1">Membresía Actual</p>
                    <h2 class="text-2xl md:text-3xl font-bold mb-1">Acceso Total {{ ucfirst($user->tarifa) }}</h2>
                    <p class="text-sm text-gray-400">
                        Válido hasta: {{ optional($user->next_payment_at)->format('d/m/Y') ?? 'Pendiente de validación' }}
                    </p>

                </div>

                <a href="{{ route('pago.gestion') }}"
                    class="relative z-10 bg-[#a3e635] text-[#0A1931] px-6 py-3 rounded-xl font-bold flex items-center gap-2 hover:scale-105 transition-transform duration-300 shadow-md no-underline">
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
                    {{-- Datos en modo solo lectura. --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-sm font-bold text-[#0A1931] m-0">Nombre: <span
                                    class="font-normal text-gray-500">{{ $user->nombre }} {{ $user->apellidos }}</span></p>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-[#0A1931] m-0">Email: <span
                                    class="font-normal text-gray-500">{{ $user->email }}</span></p>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-[#0A1931] m-0">DNI: <span
                                    class="font-normal text-gray-500">{{ $user->dni }}</span></p>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-[#0A1931] m-0">Teléfono: <span
                                    class="font-normal text-gray-500">{{ $user->telefono }}</span></p>
                        </div>
                        <div class="col-span-1 sm:col-span-2">
                            <p class="text-sm font-bold text-[#0A1931] m-0">Domicilio: <span
                                    class="font-normal text-gray-500">{{ $user->domicilio }}</span></p>
                        </div>
                    </div>
                    <button onclick="activarEdicion()"
                        class="text-[#1A3878] font-bold bg-transparent border-none p-0 cursor-pointer text-sm underline hover:text-[#0A1931] transition-colors">
                        Editar Información
                    </button>
                </div>

                {{-- VISTA 2: MODO EDICION --}}
                <form id="vista-edicion" action="{{ route('configuracion.actualizar') }}" method="POST" novalidate class="hidden">
                    @csrf
                    {{-- Campos editables por el socio. --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
                        <div>
                            <label class="block text-sm text-gray-500 font-semibold mb-1">Nombre</label>
                            <input id="cfg_nombre" type="text" name="nombre" value="{{ old('nombre', $user->nombre) }}"
                                class="w-full p-3 border border-gray-300 rounded-xl text-[#0A1931] focus:ring-2 focus:ring-[#1A3878] outline-none transition-all @error('nombre') border-red-500 bg-red-50 @enderror">
                            <p id="cfg_nombre_error" class="text-red-500 text-xs mt-1 font-medium hidden"></p>
                            @error('nombre')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm text-gray-500 font-semibold mb-1">Email</label>
                            <input id="cfg_email" type="email" name="email" value="{{ old('email', $user->email) }}"
                                class="w-full p-3 border border-gray-300 rounded-xl text-[#0A1931] focus:ring-2 focus:ring-[#1A3878] outline-none transition-all @error('email') border-red-500 bg-red-50 @enderror">
                            <p id="cfg_email_error" class="text-red-500 text-xs mt-1 font-medium hidden"></p>
                            @error('email')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm text-gray-500 font-semibold mb-1">DNI</label>
                            <input id="cfg_dni" type="text" name="dni" value="{{ old('dni', $user->dni) }}"
                                maxlength="9" oninput="this.value = this.value.toUpperCase()"
                                class="w-full p-3 border border-gray-300 rounded-xl text-[#0A1931] focus:ring-2 focus:ring-[#1A3878] outline-none transition-all @error('dni') border-red-500 bg-red-50 @enderror">
                            <p id="cfg_dni_error" class="text-red-500 text-xs mt-1 font-medium hidden"></p>
                            @error('dni')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm text-gray-500 font-semibold mb-1">Teléfono</label>
                            <input id="cfg_telefono" type="text" name="telefono" value="{{ old('telefono', $user->telefono) }}"
                                maxlength="9"
                                class="w-full p-3 border border-gray-300 rounded-xl text-[#0A1931] focus:ring-2 focus:ring-[#1A3878] outline-none transition-all @error('telefono') border-red-500 bg-red-50 @enderror">
                            <p id="cfg_telefono_error" class="text-red-500 text-xs mt-1 font-medium hidden"></p>
                            @error('telefono')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-sm text-gray-500 font-semibold mb-1">Domicilio</label>
                            <input id="cfg_domicilio" type="text" name="domicilio" value="{{ old('domicilio', $user->domicilio) }}"
                                class="w-full p-3 border border-gray-300 rounded-xl text-[#0A1931] focus:ring-2 focus:ring-[#1A3878] outline-none transition-all @error('domicilio') border-red-500 bg-red-50 @enderror">
                            <p id="cfg_domicilio_error" class="text-red-500 text-xs mt-1 font-medium hidden"></p>
                            @error('domicilio')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="submit"
                            class="bg-[#1A3878] text-white px-6 py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition-colors hover:bg-[#0A1931]">
                            <span class="material-symbols-outlined text-[18px]">save</span> Guardar Cambios
                        </button>
                        <button type="button" onclick="cancelarEdicion()"
                            class="bg-white text-gray-500 border border-gray-300 px-6 py-3 rounded-xl font-bold transition-colors hover:bg-gray-50 hover:text-[#0A1931]">
                            Cancelar
                        </button>
                    </div>
                </form>
            </section>
        </main>
    </div>

    <script>
        // Muestra el formulario para editar datos.
        function activarEdicion() {
            document.getElementById('vista-lectura').classList.add('hidden');
            document.getElementById('vista-edicion').classList.remove('hidden');
            document.getElementById('vista-edicion').classList.add('block');
        }

        // Vuelve a la vista de solo lectura sin guardar.
        function cancelarEdicion() {
            document.getElementById('vista-edicion').classList.add('hidden');
            document.getElementById('vista-edicion').classList.remove('block');
            document.getElementById('vista-lectura').classList.remove('hidden');
        }

        (function() {
            // Referencias de formulario y campos.
            const form = document.getElementById('vista-edicion');
            const lectura = document.getElementById('vista-lectura');

            const nombreInput = document.getElementById('cfg_nombre');
            const emailInput = document.getElementById('cfg_email');
            const dniInput = document.getElementById('cfg_dni');
            const telefonoInput = document.getElementById('cfg_telefono');
            const domicilioInput = document.getElementById('cfg_domicilio');

            const nombreError = document.getElementById('cfg_nombre_error');
            const emailError = document.getElementById('cfg_email_error');
            const dniError = document.getElementById('cfg_dni_error');
            const telefonoError = document.getElementById('cfg_telefono_error');
            const domicilioError = document.getElementById('cfg_domicilio_error');

            if (!form || !lectura || !nombreInput || !emailInput || !dniInput || !telefonoInput || !domicilioInput) {
                return;
            }

            // Pinta o limpia el error visual del campo.
            function setError(input, errorNode, message) {
                if (!message) {
                    errorNode.textContent = '';
                    errorNode.classList.add('hidden');
                    input.classList.remove('border-red-500', 'bg-red-50');
                    return true;
                }

                errorNode.textContent = message;
                errorNode.classList.remove('hidden');
                input.classList.add('border-red-500', 'bg-red-50');
                return false;
            }

            function validarDNIMatematico(dni) {
                const regexDni = /^[0-9]{8}[A-Z]$/i;
                if (!regexDni.test(dni)) return false;

                const letrasValidas = 'TRWAGMYFPDXBNJZSQVHLCKE';
                const numero = parseInt(dni.substring(0, 8), 10);
                const letraEscrita = dni.charAt(8).toUpperCase();
                return letraEscrita === letrasValidas.charAt(numero % 23);
            }

            function validarNombreCampo() {
                const valor = (nombreInput.value || '').trim();
                if (!valor) return setError(nombreInput, nombreError, 'El nombre es obligatorio');
                return setError(nombreInput, nombreError, '');
            }

            function validarEmailCampo() {
                const valor = (emailInput.value || '').trim();
                const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!valor) return setError(emailInput, emailError, 'El email es obligatorio');
                if (!regexEmail.test(valor)) return setError(emailInput, emailError, 'Formato de email inválido');
                return setError(emailInput, emailError, '');
            }

            function validarDniCampo() {
                const valor = (dniInput.value || '').trim().toUpperCase();
                dniInput.value = valor;

                if (!valor) return setError(dniInput, dniError, 'El DNI es obligatorio');
                if (!/^[0-9]{8}[A-Z]$/.test(valor)) return setError(dniInput, dniError, 'Formato DNI: 8 números y 1 letra');
                if (!validarDNIMatematico(valor)) return setError(dniInput, dniError, 'DNI inválido (letra incorrecta)');
                return setError(dniInput, dniError, '');
            }

            function validarTelefonoCampo() {
                const valor = (telefonoInput.value || '').trim();

                if (!valor) return setError(telefonoInput, telefonoError, 'El teléfono es obligatorio');
                if (!/^[6789]\d{8}$/.test(valor)) {
                    return setError(telefonoInput, telefonoError, 'Teléfono válido: 9 dígitos empezando por 6, 7, 8 o 9');
                }
                return setError(telefonoInput, telefonoError, '');
            }

            function validarDomicilioCampo() {
                const valor = (domicilioInput.value || '').trim();
                if (!valor) return setError(domicilioInput, domicilioError, 'El domicilio es obligatorio');
                return setError(domicilioInput, domicilioError, '');
            }

            nombreInput.addEventListener('input', validarNombreCampo);
            nombreInput.addEventListener('blur', validarNombreCampo);
            emailInput.addEventListener('input', validarEmailCampo);
            emailInput.addEventListener('blur', validarEmailCampo);
            dniInput.addEventListener('input', validarDniCampo);
            dniInput.addEventListener('blur', validarDniCampo);
            telefonoInput.addEventListener('input', validarTelefonoCampo);
            telefonoInput.addEventListener('blur', validarTelefonoCampo);
            domicilioInput.addEventListener('input', validarDomicilioCampo);
            domicilioInput.addEventListener('blur', validarDomicilioCampo);

            form.addEventListener('submit', function(event) {
                // Valida todos los campos antes de enviar.
                const okNombre = validarNombreCampo();
                const okEmail = validarEmailCampo();
                const okDni = validarDniCampo();
                const okTelefono = validarTelefonoCampo();
                const okDomicilio = validarDomicilioCampo();

                if (!okNombre || !okEmail || !okDni || !okTelefono || !okDomicilio) {
                    event.preventDefault();
                }
            });

            // Si el backend devolvió errores, abrir directamente modo edición.
            const hayErroresServidor = @json($errors->any());
            if (hayErroresServidor) {
                lectura.classList.add('hidden');
                form.classList.remove('hidden');
                form.classList.add('block');
            }
        })();
    </script>
@endsection



