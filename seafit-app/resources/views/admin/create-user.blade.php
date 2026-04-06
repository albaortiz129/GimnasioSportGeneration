{{-- Vista de alta de clientes desde el panel de administracion. --}}
@extends('moldes.inicio')

@section('titulo', 'Nuevo Cliente - Admin')

@section('contenido')
    <div class="max-w-3xl mx-auto py-10 px-4">
        <h1 class="text-2xl font-black mb-6">Crear cliente</h1>

        @if($errors->any())
            <div class="bg-red-100 text-red-700 border border-red-200 rounded-xl p-4 mb-4">
                <p class="font-bold mb-2">Revisa los datos del formulario:</p>
                <ul class="list-disc pl-5 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="admin-create-user-form" action="{{ route('admin.user.store') }}" method="POST" novalidate
            class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-6 rounded-2xl border">
            @csrf

            <div>
                <input id="nombre" name="nombre" placeholder="Nombre" value="{{ old('nombre') }}"
                    class="border rounded p-3 w-full @error('nombre') border-red-500 bg-red-50 @enderror" required>
                <p id="nombre_error" class="text-red-500 text-xs mt-1 font-medium hidden"></p>
                @error('nombre')
                    <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <input id="apellidos" name="apellidos" placeholder="Apellidos" value="{{ old('apellidos') }}"
                    class="border rounded p-3 w-full @error('apellidos') border-red-500 bg-red-50 @enderror" required>
                <p id="apellidos_error" class="text-red-500 text-xs mt-1 font-medium hidden"></p>
                @error('apellidos')
                    <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <input id="dni" name="dni" placeholder="DNI" value="{{ old('dni') }}"
                    class="border rounded p-3 w-full @error('dni') border-red-500 bg-red-50 @enderror" required
                    maxlength="9" oninput="this.value = this.value.toUpperCase()">
                <p id="dni_error" class="text-red-500 text-xs mt-1 font-medium hidden"></p>
                @error('dni')
                    <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <input id="fecha_nacimiento" type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}"
                    class="border rounded p-3 w-full @error('fecha_nacimiento') border-red-500 bg-red-50 @enderror" required>
                <p id="fecha_nacimiento_error" class="text-red-500 text-xs mt-1 font-medium hidden"></p>
                @error('fecha_nacimiento')
                    <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <input id="telefono" name="telefono" placeholder="Telefono" value="{{ old('telefono') }}"
                    class="border rounded p-3 w-full @error('telefono') border-red-500 bg-red-50 @enderror" required
                    maxlength="9">
                <p id="telefono_error" class="text-red-500 text-xs mt-1 font-medium hidden"></p>
                @error('telefono')
                    <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <input id="email" type="email" name="email" placeholder="Email" value="{{ old('email') }}"
                    class="border rounded p-3 w-full @error('email') border-red-500 bg-red-50 @enderror" required>
                <p id="email_error" class="text-red-500 text-xs mt-1 font-medium hidden"></p>
                @error('email')
                    <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <input id="domicilio" name="domicilio" placeholder="Domicilio" value="{{ old('domicilio') }}"
                    class="border rounded p-3 w-full @error('domicilio') border-red-500 bg-red-50 @enderror" required>
                <p id="domicilio_error" class="text-red-500 text-xs mt-1 font-medium hidden"></p>
                @error('domicilio')
                    <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <select id="tarifa" name="tarifa"
                    class="border rounded p-3 w-full @error('tarifa') border-red-500 bg-red-50 @enderror" required>
                    <option value="">Selecciona tarifa</option>
                    <option value="mensual" @selected(old('tarifa') === 'mensual')>Mensual</option>
                    <option value="trimestral" @selected(old('tarifa') === 'trimestral')>Trimestral</option>
                    <option value="anual" @selected(old('tarifa') === 'anual')>Anual</option>
                </select>
                <p id="tarifa_error" class="text-red-500 text-xs mt-1 font-medium hidden"></p>
                @error('tarifa')
                    <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <select id="metodo_pago" name="metodo_pago"
                    class="border rounded p-3 w-full @error('metodo_pago') border-red-500 bg-red-50 @enderror" required>
                    <option value="">Selecciona metodo de pago</option>
                    <option value="bizum" @selected(old('metodo_pago') === 'bizum')>Bizum</option>
                    <option value="paypal" @selected(old('metodo_pago') === 'paypal')>PayPal</option>
                    <option value="visa" @selected(old('metodo_pago') === 'visa')>Visa</option>
                    <option value="amex" @selected(old('metodo_pago') === 'amex')>Amex</option>
                    <option value="efectivo" @selected(old('metodo_pago') === 'efectivo')>Efectivo</option>
                    <option value="transferencia" @selected(old('metodo_pago') === 'transferencia')>Transferencia</option>
                </select>
                <p id="metodo_pago_error" class="text-red-500 text-xs mt-1 font-medium hidden"></p>
                @error('metodo_pago')
                    <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <p class="text-sm bg-blue-50 border border-blue-200 text-blue-900 rounded-xl p-3">
                    Contrasena temporal automatica para nuevos clientes: <strong>NUEVO12</strong>
                </p>
            </div>

            <button class="bg-[#0A1931] text-white py-3 rounded-xl font-bold md:col-span-2">Crear cliente</button>
        </form>
    </div>

    <script>
        (function() {
            // Referencias de formulario y campos.
            const form = document.getElementById('admin-create-user-form');
            const nombreInput = document.getElementById('nombre');
            const apellidosInput = document.getElementById('apellidos');
            const dniInput = document.getElementById('dni');
            const fechaNacimientoInput = document.getElementById('fecha_nacimiento');
            const telefonoInput = document.getElementById('telefono');
            const emailInput = document.getElementById('email');
            const domicilioInput = document.getElementById('domicilio');
            const tarifaInput = document.getElementById('tarifa');
            const metodoPagoInput = document.getElementById('metodo_pago');

            const nombreError = document.getElementById('nombre_error');
            const apellidosError = document.getElementById('apellidos_error');
            const dniError = document.getElementById('dni_error');
            const fechaNacimientoError = document.getElementById('fecha_nacimiento_error');
            const telefonoError = document.getElementById('telefono_error');
            const emailError = document.getElementById('email_error');
            const domicilioError = document.getElementById('domicilio_error');
            const tarifaError = document.getElementById('tarifa_error');
            const metodoPagoError = document.getElementById('metodo_pago_error');

            if (!form || !nombreInput || !apellidosInput || !dniInput || !fechaNacimientoInput || !telefonoInput ||
                !emailInput || !domicilioInput || !tarifaInput || !metodoPagoInput) {
                return;
            }

            // Muestra error en rojo o limpia el estado del campo.
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

            function validarNombreCampo() {
                const valor = (nombreInput.value || '').trim();
                if (!valor) return setError(nombreInput, nombreError, 'El nombre es obligatorio');
                return setError(nombreInput, nombreError, '');
            }

            function validarApellidosCampo() {
                const valor = (apellidosInput.value || '').trim();
                if (!valor) return setError(apellidosInput, apellidosError, 'Los apellidos son obligatorios');
                return setError(apellidosInput, apellidosError, '');
            }

            function validarDNIMatematico(dni) {
                const regexDni = /^[0-9]{8}[A-Z]$/i;
                if (!regexDni.test(dni)) return false;

                const letrasValidas = 'TRWAGMYFPDXBNJZSQVHLCKE';
                const numero = parseInt(dni.substring(0, 8), 10);
                const letraEscrita = dni.charAt(8).toUpperCase();
                return letraEscrita === letrasValidas.charAt(numero % 23);
            }

            function validarDniCampo() {
                const valor = (dniInput.value || '').trim().toUpperCase();
                dniInput.value = valor;

                if (!valor) return setError(dniInput, dniError, 'El DNI es obligatorio');
                if (!/^[0-9]{8}[A-Z]$/.test(valor)) return setError(dniInput, dniError, 'Formato DNI: 8 numeros y 1 letra');
                if (!validarDNIMatematico(valor)) return setError(dniInput, dniError, 'DNI invalido (letra incorrecta)');
                return setError(dniInput, dniError, '');
            }

            function validarFechaNacimientoCampo() {
                const valor = (fechaNacimientoInput.value || '').trim();
                if (!valor) return setError(fechaNacimientoInput, fechaNacimientoError, 'La fecha es obligatoria');
                return setError(fechaNacimientoInput, fechaNacimientoError, '');
            }

            function validarTelefonoCampo() {
                const valor = (telefonoInput.value || '').trim();

                if (!valor) return setError(telefonoInput, telefonoError, 'El telefono es obligatorio');
                if (!/^[6789]\d{8}$/.test(valor)) {
                    return setError(telefonoInput, telefonoError, 'Telefono valido: 9 digitos empezando por 6, 7, 8 o 9');
                }

                return setError(telefonoInput, telefonoError, '');
            }

            function validarEmailCampo() {
                const valor = (emailInput.value || '').trim();
                const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!valor) return setError(emailInput, emailError, 'El email es obligatorio');
                if (!regexEmail.test(valor)) return setError(emailInput, emailError, 'Formato de email invalido');
                return setError(emailInput, emailError, '');
            }

            function validarDomicilioCampo() {
                const valor = (domicilioInput.value || '').trim();
                if (!valor) return setError(domicilioInput, domicilioError, 'El domicilio es obligatorio');
                return setError(domicilioInput, domicilioError, '');
            }

            function validarTarifaCampo() {
                const valor = (tarifaInput.value || '').trim();
                if (!valor) return setError(tarifaInput, tarifaError, 'Debes seleccionar una tarifa');
                return setError(tarifaInput, tarifaError, '');
            }

            function validarMetodoPagoCampo() {
                const valor = (metodoPagoInput.value || '').trim();
                if (!valor) return setError(metodoPagoInput, metodoPagoError, 'Debes seleccionar un metodo de pago');
                return setError(metodoPagoInput, metodoPagoError, '');
            }

            nombreInput.addEventListener('input', validarNombreCampo);
            nombreInput.addEventListener('blur', validarNombreCampo);
            apellidosInput.addEventListener('input', validarApellidosCampo);
            apellidosInput.addEventListener('blur', validarApellidosCampo);
            dniInput.addEventListener('input', validarDniCampo);
            dniInput.addEventListener('blur', validarDniCampo);
            fechaNacimientoInput.addEventListener('change', validarFechaNacimientoCampo);
            fechaNacimientoInput.addEventListener('blur', validarFechaNacimientoCampo);
            telefonoInput.addEventListener('input', validarTelefonoCampo);
            telefonoInput.addEventListener('blur', validarTelefonoCampo);
            emailInput.addEventListener('input', validarEmailCampo);
            emailInput.addEventListener('blur', validarEmailCampo);
            domicilioInput.addEventListener('input', validarDomicilioCampo);
            domicilioInput.addEventListener('blur', validarDomicilioCampo);
            tarifaInput.addEventListener('change', validarTarifaCampo);
            metodoPagoInput.addEventListener('change', validarMetodoPagoCampo);

            form.addEventListener('submit', function(event) {
                const okNombre = validarNombreCampo();
                const okApellidos = validarApellidosCampo();
                const okDni = validarDniCampo();
                const okFechaNacimiento = validarFechaNacimientoCampo();
                const okTelefono = validarTelefonoCampo();
                const okEmail = validarEmailCampo();
                const okDomicilio = validarDomicilioCampo();
                const okTarifa = validarTarifaCampo();
                const okMetodoPago = validarMetodoPagoCampo();

                if (!okNombre || !okApellidos || !okDni || !okFechaNacimiento || !okTelefono || !okEmail ||
                    !okDomicilio || !okTarifa || !okMetodoPago) {
                    event.preventDefault();
                }
            });
        })();
    </script>
@endsection
