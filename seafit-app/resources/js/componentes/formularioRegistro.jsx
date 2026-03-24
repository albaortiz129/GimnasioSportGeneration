import React, { useState } from 'react';
// Importamos las herramientas de Stripe
import { CardElement, useStripe, useElements } from '@stripe/react-stripe-js';

const FormularioRegistro = () => {
    // Activamos los hooks de Stripe
    const stripe = useStripe();
    const elements = useElements();

    const [paso, setPaso] = useState(1);
    const [datos, setDatos] = useState({
        nombre: '', apellidos: '', dni: '', fecha_nacimiento: '',
        telefono: '', email: '', password: '',
        domicilio: '', tarifa: '',
        metodo_pago: 'bizum', cupon: ''
    });
    const [errores, setErrores] = useState({});
    const [cargando, setCargando] = useState(false);

    // --- FUNCIONES DE VALIDACIÓN ---
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
            case 'nombre': if (!valor.trim()) error = 'El nombre es obligatorio'; break;
            case 'apellidos': if (!valor.trim()) error = 'Los apellidos son obligatorios'; break;
            case 'dni': if (!validarDNIMatematico(valor)) error = 'DNI inválido (letra incorrecta)'; break;
            case 'fecha_nacimiento': if (!valor) error = 'La fecha es obligatoria'; break;
            case 'email':
                const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!regexEmail.test(valor)) error = 'Formato de email inválido';
                break;
            case 'password': if (!validarPasswordFuerte(valor)) error = 'Mín. 8 caracteres, 1 mayúscula, 1 número y 1 símbolo'; break;
            case 'telefono': if (!/^[6789]\d{8}$/.test(valor)) error = 'Teléfono no válido'; break;
            case 'domicilio': if (!valor.trim()) error = 'El domicilio es obligatorio'; break;
            default: break;
        }
        setErrores(prev => ({ ...prev, [campo]: error }));
        return error === '';
    };

    const handleChange = (campo, valor) => {
        setDatos({ ...datos, [campo]: valor });
        if (errores[campo]) setErrores({ ...errores, [campo]: '' });
    };

    const validarPaso1 = () => {
        const campos = ['nombre', 'apellidos', 'dni', 'fecha_nacimiento', 'email', 'password', 'telefono', 'domicilio'];
        return campos.every(campo => validarCampo(campo, datos[campo]));
    };

    // --- FUNCIÓN PRINCIPAL DE ENVÍO Y PAGO ---
    const finalizarRegistro = async () => {
        setCargando(true);
        let stripeTokenId = null;

        // 1. Lógica para TARJETAS (Stripe)
        if (datos.metodo_pago === 'visa' || datos.metodo_pago === 'amex') {
            if (!stripe || !elements) {
                alert("El sistema de pagos no ha cargado. Reintenta.");
                setCargando(false);
                return;
            }

            const cardElement = elements.getElement(CardElement);

            // CAMBIO AQUÍ: Usamos createPaymentMethod en lugar de createToken
            const { error, paymentMethod } = await stripe.createPaymentMethod({
                type: 'card',
                card: cardElement,
                billing_details: {
                    name: `${datos.nombre} ${datos.apellidos}`,
                    email: datos.email,
                },
            });

            if (error) {
                alert("Error en la tarjeta: " + error.message);
                setCargando(false);
                return;
            }
            // Ahora guardamos el ID que empieza por pm_...
            stripeTokenId = paymentMethod.id;
        }
        // 2. Envío a Laravel (Común para todos los métodos)
        try {
            const respuesta = await fetch('/api/registro', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    ...datos,
                    stripeToken: stripeTokenId
                })
            });

            const resultado = await respuesta.json();

            if (respuesta.ok) {
                alert(resultado.mensaje || "¡Registro completado con éxito!");
                window.location.href = '/login';
            } else {
                let msg = "Error en el registro:\n";
                if (resultado.errors) {
                    msg += Object.values(resultado.errors).flat().join('\n- ');
                } else {
                    msg += resultado.error || "Ocurrió un problema.";
                }
                alert(msg);
            }
        } catch (error) {
            alert("No hay conexión con el servidor");
        } finally {
            setCargando(false);
        }
    };

    const siguientePaso = () => {
        if (paso === 1 && validarPaso1()) setPaso(2);
        else if (paso === 2) {
            if (datos.tarifa) setPaso(3);
            else alert("Por favor, selecciona una tarifa");
        }
    };

    const volverPaso = () => setPaso(paso - 1);

    return (
        <div className="bg-white w-full mx-auto overflow-hidden rounded-[20px] shadow-[0_10px_40px_rgba(0,0,0,0.03)] border border-gray-100">

            {/* INDICADOR DE PROGRESO */}
            <div className="px-8 sm:px-14 pt-10 pb-4 text-left">
                <span className="text-[13px] text-[#0A1931] font-bold tracking-wider uppercase">Paso {paso} de 3</span>
                <div className="w-full h-[6px] bg-[#f0f4f8] rounded-full mt-2 overflow-hidden">
                    <div className="h-full bg-[#1A3878] rounded-full transition-all duration-500" style={{ width: `${(paso / 3) * 100}%` }}></div>
                </div>
            </div>

            {/* PASO 1: DATOS PERSONALES */}
            {paso === 1 && (
                <section className="px-8 sm:px-14 pb-8">
                    <h1 className="text-3xl font-extrabold text-[#0A1931] mb-2">Crea tu cuenta</h1>
                    <p className="text-[15px] text-gray-500 mb-8">Datos Personales</p>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
                        {['nombre', 'apellidos', 'dni', 'fecha_nacimiento', 'email', 'password', 'telefono', 'domicilio'].map((campo) => (
                            <div key={campo} className="min-w-0">
                                <label className="block text-[14px] font-semibold text-gray-800 mb-2 capitalize">{campo.replace('_', ' ')}</label>
                                <input
                                    type={campo === 'password' ? 'password' : campo === 'fecha_nacimiento' ? 'date' : 'text'}
                                    className={`w-full p-3.5 border rounded-xl outline-none focus:ring-1 focus:ring-[#1A3878] ${errores[campo] ? 'border-red-500 bg-red-50' : 'border-gray-200 bg-[#fdfdfd]'}`}
                                    value={datos[campo]}
                                    onChange={(e) => handleChange(campo, e.target.value)}
                                    onBlur={(e) => validarCampo(campo, e.target.value)}
                                />
                                {errores[campo] && <p className="text-red-500 text-xs mt-1.5 break-words font-medium">{errores[campo]}</p>}
                            </div>
                        ))}
                    </div>
                </section>
            )}

            {/* PASO 2: SELECCIÓN DE TARIFA */}
            {paso === 2 && (
                <section className="px-8 sm:px-14 pb-8 text-left">
                    <h1 className="text-3xl font-extrabold text-[#0A1931] mb-2">Elige tu plan</h1>
                    <p className="text-[15px] text-gray-500 mb-8">Paso 2: Selección de Tarifa</p>
                    <div className="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        {[
                            { id: 'mensual', nombre: 'Mensual', precio: '29,99€', desc: 'Sin permanencia' },
                            { id: 'trimestral', nombre: 'Trimestral', precio: '75,00€', desc: 'Ahorra un 15%' },
                            { id: 'anual', nombre: 'Anual', precio: '250,00€', desc: 'La mejor opción' }
                        ].map((t) => (
                            <div key={t.id}
                                className={`p-8 rounded-2xl border-2 transition-all cursor-pointer text-center ${datos.tarifa === t.id ? 'border-[#1A3878] bg-[#f0f7ff]' : 'border-gray-200 hover:border-[#1A3878]'}`}
                                onClick={() => setDatos({ ...datos, tarifa: t.id })}>
                                <h3 className="font-bold text-xl text-[#0A1931]">{t.nombre}</h3>
                                <p className="text-3xl font-black text-[#1A3878] my-4">{t.precio}</p>
                                <p className="text-sm text-gray-500">{t.desc}</p>
                            </div>
                        ))}
                    </div>
                </section>
            )}

            {/* PASO 3: MÉTODO DE PAGO */}
            {paso === 3 && (
                <div className="px-8 sm:px-14 pb-8 text-left">
                    <h1 className="text-3xl font-extrabold text-[#0A1931] mb-2">Método de pago</h1>
                    <p className="text-[15px] text-gray-500 mb-8">Total: <strong className="text-[#1A3878]">{datos.tarifa === 'anual' ? '250.00€' : datos.tarifa === 'trimestral' ? '75.00€' : '29.99€'}</strong></p>

                    <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 mb-8">
                        {[
                            { id: 'bizum', nombre: 'Bizum', icono: 'payments' },
                            { id: 'paypal', nombre: 'PayPal', icono: 'payments' },
                            { id: 'visa', nombre: 'Tarjeta de Crédito', icono: 'credit_card' },
                            { id: 'amex', nombre: 'American Express', icono: 'credit_card' }
                        ].map((metodo) => (
                            <label key={metodo.id} className={`flex items-center gap-4 p-5 border-2 rounded-xl cursor-pointer transition-all ${datos.metodo_pago === metodo.id ? 'border-[#1A3878] bg-[#f0f7ff]' : 'border-gray-200'}`}>
                                <input type="radio" className="hidden" name="pay" checked={datos.metodo_pago === metodo.id} onChange={() => handleChange('metodo_pago', metodo.id)} />
                                <span className="material-symbols-outlined text-[#1A3878]">{metodo.icono}</span>
                                <span className="font-bold text-[#0A1931]">{metodo.nombre}</span>
                            </label>
                        ))}
                    </div>

                    {/* INSTRUCCIONES BIZUM */}
                    {datos.metodo_pago === 'bizum' && (
                        <div className="mb-8 p-5 border border-green-200 rounded-xl bg-green-50 shadow-sm">
                            <p className="text-sm text-green-700 m-0 leading-relaxed">
                                Para finalizar, realiza un Bizum al: <strong className="text-[#0A1931]">600 000 000</strong><br />
                                Concepto: <strong className="text-[#0A1931]">{datos.dni} - SeaFit</strong>
                            </p>
                        </div>
                    )}

                    {/* INSTRUCCIONES PAYPAL */}
                    {datos.metodo_pago === 'paypal' && (
                        <div className="mb-8 p-5 border border-blue-200 rounded-xl bg-blue-50 shadow-sm">
                            <div className="flex items-center gap-2 mb-2 text-blue-800 font-bold">
                                <span className="material-symbols-outlined">info</span>
                                Pago vía PayPal
                            </div>
                            <p className="text-sm text-blue-700 m-0 leading-relaxed">
                                Al hacer clic en "Finalizar Registro", procesaremos tu solicitud. <br />
                                <strong>Recibirás un enlace de pago seguro en tu correo electrónico</strong> ({datos.email}) para completar la suscripción de {datos.tarifa === 'anual' ? '250.00€' : datos.tarifa === 'trimestral' ? '75.00€' : '29.99€'}.
                            </p>
                        </div>
                    )}

                    {/* CAJA DE TARJETA (STRIPE) */}
                    {(datos.metodo_pago === 'visa' || datos.metodo_pago === 'amex') && (
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
                        {cargando ? 'Procesando...' : `Finalizar Registro`}
                    </button>
                </div>
            )}

            {/* BOTONES DE NAVEGACIÓN */}
            <div className="flex justify-between items-center bg-[#f8fafc] px-8 sm:px-14 py-6 border-t">
                {paso > 1 && <button onClick={volverPaso} className="text-gray-500 font-bold hover:underline">Atrás</button>}
                {paso < 3 && <button onClick={siguientePaso} className="ml-auto bg-[#1A3878] text-white py-3 px-10 rounded-xl font-bold hover:bg-[#0A1931]">Siguiente</button>}
            </div>
        </div>
    );
};

export default FormularioRegistro;