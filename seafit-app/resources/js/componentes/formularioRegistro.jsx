import React, { useState } from 'react';
import { CardElement, useStripe, useElements } from '@stripe/react-stripe-js';

// Campos del primer paso (datos personales).
const CAMPOS_PASO_1 = [
    'nombre',
    'apellidos',
    'dni',
    'fecha_nacimiento',
    'email',
    'password',
    'password_confirmation',
    'telefono',
    'domicilio',
];

// Etiquetas legibles para cada campo del paso 1.
const ETIQUETAS_CAMPOS = {
    nombre: 'Nombre',
    apellidos: 'Apellidos',
    dni: 'DNI',
    fecha_nacimiento: 'Fecha de nacimiento',
    email: 'Email',
    password: 'Contraseña',
    password_confirmation: 'Confirmar contraseña',
    telefono: 'Telefono',
    domicilio: 'Domicilio',
};

// Tarifas disponibles para registro.
const TARIFAS = [
    { id: 'mensual', nombre: 'Mensual', precio: '29.99 EUR', desc: 'Sin permanencia' },
    { id: 'trimestral', nombre: 'Trimestral', precio: '75.00 EUR', desc: 'Ahorra un 15%' },
    { id: 'anual', nombre: 'Anual', precio: '250.00 EUR', desc: 'La mejor opcion' },
];

// Metodos de pago admitidos en registro.
const METODOS_PAGO = [
    { id: 'bizum', nombre: 'Bizum', icono: 'payments' },
    { id: 'paypal', nombre: 'PayPal', icono: 'payments' },
    { id: 'visa', nombre: 'Tarjeta de credito', icono: 'credit_card' },
    { id: 'efectivo', nombre: 'Efectivo', icono: 'payments' },
];

// Devuelve el precio visible segun la tarifa elegida.
const obtenerPrecioTarifa = (tarifa) => {
    if (tarifa === 'anual') return '250.00 EUR';
    if (tarifa === 'trimestral') return '75.00 EUR';
    return '29.99 EUR';
};

// Formulario en 3 pasos: datos personales, tarifa y pago.
const FormularioRegistro = () => {
    const stripe = useStripe();
    const elements = useElements();

    const [paso, setPaso] = useState(1);
    const [datos, setDatos] = useState({
        nombre: '',
        apellidos: '',
        dni: '',
        fecha_nacimiento: '',
        telefono: '',
        email: '',
        password: '',
        password_confirmation: '',
        domicilio: '',
        tarifa: '',
        metodo_pago: 'bizum',
        cupon: '',
    });

    const [errores, setErrores] = useState({});
    const [cargando, setCargando] = useState(false);

    const validarDNIMatematico = (dni) => {
        const regexDni = /^[0-9]{8}[A-Z]$/i;
        if (!regexDni.test(dni)) return false;

        const letrasValidas = 'TRWAGMYFPDXBNJZSQVHLCKE';
        const numero = parseInt(dni.substring(0, 8), 10);
        const letraEscrita = dni.charAt(8).toUpperCase();
        return letraEscrita === letrasValidas.charAt(numero % 23);
    };

    const validarPasswordFuerte = (password) => {
        const regexPassword = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        return regexPassword.test(password);
    };

    const validarCampo = (campo, valor) => {
        let error = '';

        switch (campo) {
            case 'nombre':
                if (!valor.trim()) error = 'El nombre es obligatorio';
                break;
            case 'apellidos':
                if (!valor.trim()) error = 'Los apellidos son obligatorios';
                break;
            case 'dni':
                if (!validarDNIMatematico(valor)) error = 'DNI invalido (letra incorrecta)';
                break;
            case 'fecha_nacimiento':
                if (!valor) error = 'La fecha es obligatoria';
                break;
            case 'email': {
                const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!regexEmail.test(valor)) error = 'Formato de email invalido';
                break;
            }
            case 'password':
                if (!validarPasswordFuerte(valor)) {
                    error = 'Min. 8 caracteres, 1 mayuscula, 1 numero y 1 simbolo';
                }
                break;
            case 'password_confirmation':
                if (!valor) {
                    error = 'Debes confirmar la contraseña';
                } else if (valor !== datos.password) {
                    error = 'Las contraseñas no coinciden';
                }
                break;
            case 'telefono':
                if (!/^[6789]\d{8}$/.test(valor)) error = 'Telefono no valido';
                break;
            case 'domicilio':
                if (!valor.trim()) error = 'El domicilio es obligatorio';
                break;
            default:
                break;
        }

        setErrores((prev) => ({ ...prev, [campo]: error }));
        return error === '';
    };

    const handleChange = (campo, valor) => {
        setDatos((prev) => ({ ...prev, [campo]: valor }));

        // Si cambia la password, se limpia/revalida la confirmacion.
        if (campo === 'password') {
            setErrores((prev) => ({ ...prev, password_confirmation: '' }));
        }

        if (errores[campo]) {
            setErrores((prev) => ({ ...prev, [campo]: '' }));
        }
    };

    const validarDisponibilidadCampo = async (campo, valor) => {
        if (campo !== 'dni' && campo !== 'email') {
            return true;
        }

        const limpio = (valor || '').trim();
        if (!limpio) {
            return true;
        }

        const payload = campo === 'dni'
            ? { dni: limpio.toUpperCase() }
            : { email: limpio.toLowerCase() };

        try {
            const respuesta = await fetch('/api/registro/disponibilidad', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(payload),
            });

            const resultado = await respuesta.json();

            const disponible = campo === 'dni'
                ? resultado.dni_disponible
                : resultado.email_disponible;

            if (disponible === false) {
                const mensaje = campo === 'dni'
                    ? 'Ya existe un usuario registrado con ese DNI.'
                    : 'Ya existe un usuario registrado con ese email.';

                setErrores((prev) => ({ ...prev, [campo]: mensaje }));
                return false;
            }

            return true;
        } catch (error) {
            // Si falla la comprobacion, no bloqueamos al usuario aqui.
            return true;
        }
    };

    const validarPaso1 = async () => {
        const validacionLocal = CAMPOS_PASO_1.every((campo) => validarCampo(campo, datos[campo]));

        if (!validacionLocal) {
            return false;
        }

        const dniLibre = await validarDisponibilidadCampo('dni', datos.dni);
        const emailLibre = await validarDisponibilidadCampo('email', datos.email);

        return dniLibre && emailLibre;
    };

    // Envia el registro y, si aplica, crea payment method en Stripe.
    const finalizarRegistro = async () => {
        setCargando(true);
        let stripePaymentMethodId = null;

        if (datos.metodo_pago === 'visa') {
            if (!stripe || !elements) {
                alert('El sistema de pagos no ha cargado. Reintenta.');
                setCargando(false);
                return;
            }

            const cardElement = elements.getElement(CardElement);

            const { error, paymentMethod } = await stripe.createPaymentMethod({
                type: 'card',
                card: cardElement,
                billing_details: {
                    name: `${datos.nombre} ${datos.apellidos}`,
                    email: datos.email,
                },
            });

            if (error) {
                alert(`Error en la tarjeta: ${error.message}`);
                setCargando(false);
                return;
            }

            stripePaymentMethodId = paymentMethod.id;
        }

        try {
            const respuesta = await fetch('/api/registro', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    ...datos,
                    stripeCodigo: stripePaymentMethodId,
                }),
            });

            const resultado = await respuesta.json();

            if (respuesta.ok) {
                alert(resultado.mensaje || 'Registro completado con exito.');
                window.location.href = '/login';
                return;
            }

            if (resultado?.errors?.dni || resultado?.errors?.email) {
                const nuevosErrores = {};

                if (resultado.errors.dni) {
                    nuevosErrores.dni = 'Ya existe un usuario registrado con ese DNI.';
                }

                if (resultado.errors.email) {
                    nuevosErrores.email = 'Ya existe un usuario registrado con ese email.';
                }

                setErrores((prev) => ({ ...prev, ...nuevosErrores }));
                setPaso(1);
                return;
            }

            let msg = 'Error en el registro:\n';
            if (resultado.errors) {
                msg += Object.values(resultado.errors).flat().join('\n- ');
            } else {
                msg += resultado.error || 'Ocurrio un problema.';
            }
            alert(msg);
        } catch (error) {
            alert('No hay conexion con el servidor.');
        } finally {
            setCargando(false);
        }
    };

    const siguientePaso = async () => {
        if (paso === 1) {
            if (await validarPaso1()) setPaso(2);
            return;
        }

        if (paso === 2) {
            if (datos.tarifa) {
                setPaso(3);
            } else {
                alert('Por favor, selecciona una tarifa.');
            }
        }
    };

    const volverPaso = () => setPaso((prev) => prev - 1);

    const precioActual = obtenerPrecioTarifa(datos.tarifa);

    return (
        <div className="bg-white w-full mx-auto overflow-hidden rounded-[20px] shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-gray-100">
            {/* Barra de progreso */}
            <div className="px-8 sm:px-14 pt-10 pb-4 text-left">
                <span className="text-[13px] text-[#0A1931] font-bold tracking-wider uppercase">Paso {paso} de 3</span>
                <div className="w-full h-[6px] bg-[#f0f4f8] rounded-full mt-2 overflow-hidden">
                    <div className="h-full bg-[#1A3878] rounded-full transition-all duration-500" style={{ width: `${(paso / 3) * 100}%` }}></div>
                </div>
            </div>

            {/* Paso 1: datos */}
            {paso === 1 && (
                <section className="px-8 sm:px-14 pb-8">
                    <h1 className="text-3xl font-extrabold text-[#0A1931] mb-2">Crea tu cuenta</h1>
                    <p className="text-[15px] text-gray-500 mb-8">Datos personales</p>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
                        {CAMPOS_PASO_1.map((campo) => (
                            <div key={campo} className="min-w-0">
                                <label className="block text-[14px] font-semibold text-gray-800 mb-2">
                                    {ETIQUETAS_CAMPOS[campo] || campo.replace('_', ' ')}
                                </label>
                                <input
                                    type={(campo === 'password' || campo === 'password_confirmation')
                                        ? 'password'
                                        : campo === 'fecha_nacimiento'
                                            ? 'date'
                                            : 'text'}
                                    className={`w-full p-3.5 border rounded-xl outline-none focus:ring-1 focus:ring-[#1A3878] ${errores[campo] ? 'border-red-500 bg-red-50' : 'border-gray-200 bg-[#fdfdfd]'}`}
                                    value={datos[campo]}
                                    onChange={(e) => handleChange(campo, e.target.value)}
                                    onBlur={async (e) => {
                                        const ok = validarCampo(campo, e.target.value);

                                        if (ok && (campo === 'dni' || campo === 'email')) {
                                            await validarDisponibilidadCampo(campo, e.target.value);
                                        }
                                    }}
                                />
                                {errores[campo] && <p className="text-red-500 text-xs mt-1.5 break-words font-medium">{errores[campo]}</p>}
                            </div>
                        ))}
                    </div>
                </section>
            )}

            {/* Paso 2: tarifa */}
            {paso === 2 && (
                <section className="px-8 sm:px-14 pb-8 text-left">
                    <h1 className="text-3xl font-extrabold text-[#0A1931] mb-2">Elige tu plan</h1>
                    <p className="text-[15px] text-gray-500 mb-8">Paso 2: seleccion de tarifa</p>

                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        {TARIFAS.map((t) => (
                            <div
                                key={t.id}
                                className={`p-8 rounded-2xl border-2 transition-all cursor-pointer text-center ${datos.tarifa === t.id ? 'border-[#1A3878] bg-[#f0f7ff]' : 'border-gray-200 hover:border-[#1A3878]'}`}
                                onClick={() => setDatos((prev) => ({ ...prev, tarifa: t.id }))}
                            >
                                <h3 className="font-bold text-xl text-[#0A1931]">{t.nombre}</h3>
                                <p className="text-3xl font-black text-[#1A3878] my-4">{t.precio}</p>
                                <p className="text-sm text-gray-500">{t.desc}</p>
                            </div>
                        ))}
                    </div>

                    <div className="mt-8">
                        <label className="block text-sm font-bold text-[#0A1931] mb-2">
                            Codigo de descuento (opcional)
                        </label>
                        <input
                            type="text"
                            value={datos.cupon}
                            onChange={(e) => handleChange('cupon', e.target.value.toUpperCase())}
                            placeholder="Ej: SEAFIT20"
                            className="w-full p-3.5 border border-gray-200 rounded-xl outline-none focus:ring-1 focus:ring-[#1A3878] bg-[#fdfdfd]"
                        />
                        <p className="text-xs text-gray-500 mt-1">
                            Si tienes un codigo, escribelo aqui. Se validara al finalizar el registro.
                        </p>
                    </div>
                </section>
            )}

            {/* Paso 3: pago */}
            {paso === 3 && (
                <div className="px-8 sm:px-14 pb-8 text-left">
                    <h1 className="text-3xl font-extrabold text-[#0A1931] mb-2">Metodo de pago</h1>
                    <p className="text-[15px] text-gray-500 mb-8">Total: <strong className="text-[#1A3878]">{precioActual}</strong></p>

                    <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 mb-8">
                        {METODOS_PAGO.map((metodo) => (
                            <label key={metodo.id} className={`flex items-center gap-4 p-5 border-2 rounded-xl cursor-pointer transition-all ${datos.metodo_pago === metodo.id ? 'border-[#1A3878] bg-[#f0f7ff]' : 'border-gray-200'}`}>
                                <input
                                    type="radio"
                                    className="hidden"
                                    name="pay"
                                    checked={datos.metodo_pago === metodo.id}
                                    onChange={() => handleChange('metodo_pago', metodo.id)}
                                />
                                <span className="material-symbols-outlined text-[#1A3878]">{metodo.icono}</span>
                                <span className="font-bold text-[#0A1931]">{metodo.nombre}</span>
                            </label>
                        ))}
                    </div>

                    {datos.metodo_pago === 'bizum' && (
                        <div className="mb-8 p-5 border border-green-200 rounded-xl bg-green-50 shadow-sm">
                            <p className="text-sm text-green-700 m-0 leading-relaxed">
                                Para finalizar, realiza un Bizum al: <strong className="text-[#0A1931]">600 000 000</strong><br />
                                Concepto: <strong className="text-[#0A1931]">{datos.dni} - SeaFit</strong>
                            </p>
                        </div>
                    )}

                    {datos.metodo_pago === 'paypal' && (
                        <div className="mb-8 p-5 border border-blue-200 rounded-xl bg-blue-50 shadow-sm">
                            <div className="flex items-center gap-2 mb-2 text-blue-800 font-bold">
                                <span className="material-symbols-outlined">info</span>
                                Pago via PayPal
                            </div>
                            <p className="text-sm text-blue-700 m-0 leading-relaxed">
                                Al hacer clic en "Finalizar Registro", procesaremos tu solicitud.<br />
                                <strong>Recibiras un enlace de pago seguro en tu correo</strong> ({datos.email}) para completar la suscripcion de {precioActual}.
                            </p>
                        </div>
                    )}

                    {datos.metodo_pago === 'efectivo' && (
                        <div className="mb-8 p-5 border border-amber-200 rounded-xl bg-amber-50 shadow-sm">
                            <div className="flex items-center gap-2 mb-2 text-amber-800 font-bold">
                                <span className="material-symbols-outlined">info</span>
                                Pago en efectivo
                            </div>
                            <p className="text-sm text-amber-700 m-0 leading-relaxed">
                                Puedes pagar directamente en recepcion antes de tu primera clase.<br />
                                <strong>Tu cuenta quedara pendiente</strong> hasta que el administrador confirme el cobro.
                            </p>
                        </div>
                    )}

                    {datos.metodo_pago === 'visa' && (
                        <div className="mb-8 p-5 border border-blue-200 rounded-xl bg-blue-50">
                            <label className="block text-sm font-bold text-[#0A1931] mb-3 text-left">Datos de tu tarjeta</label>
                            <div className="bg-white p-4 border border-gray-300 rounded-lg shadow-sm">
                                <CardElement options={{ style: { base: { fontSize: '16px', color: '#0A1931', fontFamily: 'Montserrat, sans-serif' } } }} />
                            </div>
                        </div>
                    )}

                    <button
                        onClick={finalizarRegistro}
                        disabled={cargando}
                        className={`w-full py-4 rounded-xl text-white font-bold text-lg transition-all ${cargando ? 'bg-gray-400' : 'bg-[#1A3878] hover:bg-[#0A1931]'}`}
                    >
                        {cargando ? 'Procesando...' : 'Finalizar Registro'}
                    </button>
                </div>
            )}

            {/* Botonera inferior */}
            <div className="flex justify-between items-center bg-[#f8fafc] px-8 sm:px-14 py-6 border-t">
                {paso > 1 && (
                    <button onClick={volverPaso} className="text-gray-500 font-bold hover:underline">
                        Atras
                    </button>
                )}
                {paso < 3 && (
                    <button onClick={siguientePaso} className="ml-auto bg-[#1A3878] text-white py-3 px-10 rounded-xl font-bold hover:bg-[#0A1931]">
                        Siguiente
                    </button>
                )}
            </div>
        </div>
    );
};

export default FormularioRegistro;
