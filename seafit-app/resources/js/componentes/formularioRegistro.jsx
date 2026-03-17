import React, { useState } from 'react';

const FormularioRegistro = () => {
    // 1. Estado para el paso actual y los datos
    const [paso, setPaso] = useState(1);
    const [datos, setDatos] = useState({
        nombre: '', apellidos: '', dni: '', fecha_nacimiento: '',
        telefono: '', email: '', password: '',
        domicilio: '', tarifa: '',
        metodo_pago: 'bizum', numero_tarjeta: '', exp_tarjeta: '', cvv: '',
        cupon: ''
    });
    const [errores, setErrores] = useState({});

    // 2. Funciones de Validación
    const validarPaso1 = () => {
        let nuevosErrores = {};
        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!datos.nombre.trim()) nuevosErrores.nombre = 'El nombre es obligatorio';
        if (!datos.apellidos.trim()) nuevosErrores.apellidos = 'Los apellidos son obligatorios';

        const regexDni = /^[0-9]{8}[A-Z]$/i;
        if (!regexDni.test(datos.dni)) nuevosErrores.dni = 'DNI no válido (Ej: 12345678A)';

        if (!datos.fecha_nacimiento) nuevosErrores.fecha_nacimiento = 'La fecha es obligatoria';

        if (!regexEmail.test(datos.email)) nuevosErrores.email = 'Formato de email inválido';

        // Validación de contraseña
        if (datos.password.length < 6) nuevosErrores.password = 'La contraseña debe tener al menos 6 caracteres';

        const regexTel = /^[6789]\d{8}$/;
        if (!regexTel.test(datos.telefono)) nuevosErrores.telefono = 'Teléfono no válido (9 dígitos)';

        if (!datos.domicilio.trim()) nuevosErrores.domicilio = 'El domicilio es obligatorio';

        setErrores(nuevosErrores);
        return Object.keys(nuevosErrores).length === 0;
    };

    // 3. Lógica para avanzar y ENVIAR datos a Laravel
    const siguientePaso = async () => {
        if (paso === 1 && validarPaso1()) {
            setPaso(2);
            setErrores({});
        } else if (paso === 2) {
            if (datos.tarifa) setPaso(3);
            else alert("Por favor, selecciona una tarifa");
        } else if (paso === 3) {
            try {
                const respuesta = await fetch('/api/registro', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(datos)
                });

                const resultado = await respuesta.json();

                if (respuesta.ok) {
                    // Si el controlador devolvió un mensaje de descuento, lo mostramos en el alert
                    const mensajeExito = resultado.descuento
                        ? `${resultado.mensaje} \nStatus: ${resultado.descuento}`
                        : resultado.mensaje;

                    alert(mensajeExito);
                    window.location.href = '/login';
                } else {
                    alert("Error: " + (resultado.error || "No se pudo completar el registro"));
                }
            } catch (error) {
                console.error("Error de conexión:", error);
                alert("No hay conexión con el servidor");
            }
        }
    };

    const volverPaso = () => setPaso(paso - 1);

    return (
        <div className="tarjeta-registro">
            {/* Indicador de Progreso Dinámico */}
            <div className="progreso-registro">
                <span className="paso-texto">Paso {paso} de 3</span>
                <div className="barra-progreso">
                    <div className="progreso-llenado" style={{ width: `${(paso / 3) * 100}%` }}></div>
                </div>
            </div>

            {/* PASO 1: DATOS PERSONALES */}
            {paso === 1 && (
                <section>
                    <h1>Crea tu cuenta</h1>
                    <p className="subtitulo-registro">Paso 1: Datos Personales</p>
                    <div className="cuadricula-campos">
                        <div className="columna-campos">
                            <div className="grupo-campo">
                                <label>Nombre</label>
                                <input type="text" className={errores.nombre ? 'input-error' : ''} placeholder="Ana" onChange={(e) => setDatos({ ...datos, nombre: e.target.value })} value={datos.nombre} />
                                {errores.nombre && <p className="mensaje-error-react">{errores.nombre}</p>}
                            </div>
                            <div className="grupo-campo">
                                <label>Apellidos</label>
                                <input type="text" className={errores.apellidos ? 'input-error' : ''} placeholder="Torres" onChange={(e) => setDatos({ ...datos, apellidos: e.target.value })} value={datos.apellidos} />
                                {errores.apellidos && <p className="mensaje-error-react">{errores.apellidos}</p>}
                            </div>
                            <div className="grupo-campo">
                                <label>DNI</label>
                                <input type="text" className={errores.dni ? 'input-error' : ''} placeholder="12345678A" onChange={(e) => setDatos({ ...datos, dni: e.target.value })} value={datos.dni} />
                                {errores.dni && <p className="mensaje-error-react">{errores.dni}</p>}
                            </div>
                            <div className="grupo-campo">
                                <label>Fecha de Nacimiento</label>
                                <input type="date" className={errores.fecha_nacimiento ? 'input-error' : ''} onChange={(e) => setDatos({ ...datos, fecha_nacimiento: e.target.value })} value={datos.fecha_nacimiento} />
                                {errores.fecha_nacimiento && <p className="mensaje-error-react">{errores.fecha_nacimiento}</p>}
                            </div>
                        </div>
                        <div className="columna-campos">
                            <div className="grupo-campo">
                                <label>Email</label>
                                <input type="email" className={errores.email ? 'input-error' : ''} placeholder="correo@ejemplo.com" onChange={(e) => setDatos({ ...datos, email: e.target.value })} value={datos.email} />
                                {errores.email && <p className="mensaje-error-react">{errores.email}</p>}
                            </div>
                            {/* CAMPO CONTRASEÑA AÑADIDO */}
                            <div className="grupo-campo">
                                <label>Contraseña</label>
                                <input type="password" className={errores.password ? 'input-error' : ''} placeholder="Mínimo 6 caracteres" onChange={(e) => setDatos({ ...datos, password: e.target.value })} value={datos.password} />
                                {errores.password && <p className="mensaje-error-react">{errores.password}</p>}
                            </div>
                            <div className="grupo-campo">
                                <label>Teléfono</label>
                                <input type="tel" className={errores.telefono ? 'input-error' : ''} placeholder="600112233" onChange={(e) => setDatos({ ...datos, telefono: e.target.value })} value={datos.telefono} />
                                {errores.telefono && <p className="mensaje-error-react">{errores.telefono}</p>}
                            </div>
                            <div className="grupo-campo">
                                <label>Domicilio</label>
                                <input type="text" className={errores.domicilio ? 'input-error' : ''} placeholder="Calle Falsa, 123" onChange={(e) => setDatos({ ...datos, domicilio: e.target.value })} value={datos.domicilio} />
                                {errores.domicilio && <p className="mensaje-error-react">{errores.domicilio}</p>}
                            </div>
                        </div>
                    </div>
                </section>
            )}

            {/* PASO 2: TARIFAS */}
            {paso === 2 && (
                <section>
                    <h1>Elige tu plan</h1>
                    <p className="subtitulo-registro">Paso 2: Selección de Tarifa</p>
                    <div className="contenedor-tarifas">
                        {[
                            { id: 'mensual', nombre: 'Mensual', precio: '29,99€', desc: 'Sin permanencia' },
                            { id: 'trimestral', nombre: 'Trimestral', precio: '75,00€', desc: 'Ahorra un 15%' },
                            { id: 'anual', nombre: 'Anual', precio: '250,00€', desc: 'La mejor opción' }
                        ].map((t) => (
                            <div key={t.id} className={`tarjeta-tarifa ${datos.tarifa === t.id ? 'seleccionada' : ''}`} onClick={() => setDatos({ ...datos, tarifa: t.id })}>
                                <h3>{t.nombre}</h3>
                                <p className="precio-tarifa">{t.precio}</p>
                                <p>{t.desc}</p>
                            </div>
                        ))}
                    </div>
                </section>
            )}

            {/* PASO 3: MÉTODO DE PAGO */}
            {paso === 3 && (
                <div className="w-full max-w-2xl mx-auto bg-white dark:bg-secondary-dark p-2">
                    <div className="mb-6 text-center">
                        <h1 className="text-3xl sm:text-4xl font-black leading-tight tracking-tight text-[#0A1931] dark:text-white">
                            Selecciona tu método de pago
                        </h1>
                        <p className="mt-2 text-base font-normal text-gray-600 dark:text-gray-300">
                            Total a pagar: {datos.tarifa === 'anual' ? '250.00€' : datos.tarifa === 'trimestral' ? '75.00€' : '29.99€'}
                        </p>
                    </div>

                    <form onSubmit={(e) => e.preventDefault()}>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            {[
                                { id: 'bizum', nombre: 'Bizum', icono: 'payments' },
                                { id: 'paypal', nombre: 'PayPal', icono: 'payments' },
                                { id: 'visa', nombre: 'Tarjeta de Crédito / ...', icono: 'credit_card' },
                                { id: 'amex', nombre: 'American Express', icono: 'credit_card' }
                            ].map((metodo) => (
                                <div key={metodo.id} className="payment-option relative">
                                    <input
                                        className="payment-option-input peer sr-only"
                                        id={metodo.id}
                                        name="payment_method"
                                        type="radio"
                                        value={metodo.id}
                                        checked={datos.metodo_pago === metodo.id}
                                        onChange={() => setDatos({ ...datos, metodo_pago: metodo.id })}
                                    />
                                    <label
                                        className="flex h-full items-center gap-4 rounded-lg border-2 border-gray-200 bg-gray-50 p-4 transition-all cursor-pointer peer-checked:border-[#1A3878] peer-checked:bg-[#E6F3FF] hover:border-[#1A3878] dark:border-gray-700 dark:bg-secondary-dark/80"
                                        htmlFor={metodo.id}
                                    >
                                        <div className={`absolute top-3 left-3 w-4 h-4 rounded-full border-2 flex items-center justify-center ${datos.metodo_pago === metodo.id ? 'border-[#1A3878]' : 'border-gray-300'}`}>
                                            {datos.metodo_pago === metodo.id && <div className="w-2 h-2 rounded-full bg-[#1A3878]"></div>}
                                        </div>

                                        <span className="material-symbols-outlined text-3xl text-[#1A3878] ml-4">{metodo.icono}</span>
                                        <p className="flex-1 truncate text-lg font-bold text-[#0A1931] dark:text-white">{metodo.nombre}</p>
                                    </label>
                                </div>
                            ))}
                        </div>

                        <div className="mt-6 flex items-center">
                            <input
                                className="h-5 w-5 rounded border-gray-300 text-[#1A3878] focus:ring-2 focus:ring-[#1A3878]"
                                id="save-method"
                                type="checkbox"
                            />
                            <label className="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300" htmlFor="save-method">
                                Guardar este método de pago para futuras compras
                            </label>
                        </div>
                        {/* SECCIÓN DE CUPÓN (AÑADIR ESTO ANTES DEL BOTÓN DE PAGAR) */}
                        <div className="mt-6 p-4 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                            <label className="block text-sm font-bold text-[#0A1931] mb-2">
                                ¿Tienes un código de descuento?
                            </label>
                            <div className="flex gap-2">
                                <input
                                    type="text"
                                    placeholder="Ej: SEAFIT20"
                                    value={datos.cupon}
                                    onChange={(e) => setDatos({ ...datos, cupon: e.target.value.toUpperCase() })}
                                    className="flex-1 p-2 border rounded-md text-sm uppercase"
                                />
                            </div>
                            <p className="text-[10px] text-gray-500 mt-1 italic">
                                *Los descuentos se aplicarán al procesar el pago.
                            </p>
                        </div>
                        <div className="mt-8">
                            <button
                                onClick={siguientePaso}
                                className="flex w-full items-center justify-center rounded-lg bg-[#0A1931] px-5 py-4 text-lg font-bold text-white transition hover:bg-opacity-90 focus:outline-none focus:ring-2"
                                type="button"
                            >
                                Pagar {datos.tarifa === 'anual' ? '250.00€' : datos.tarifa === 'trimestral' ? '75.00€' : '29.99€'}
                            </button>
                        </div>
                    </form>

                    <div className="mt-6 flex items-center justify-center gap-2 text-xs text-gray-500">
                        <span className="material-symbols-outlined text-base">lock</span>
                        <span>Tu pago es seguro y está protegido</span>
                    </div>
                </div>
            )}

            {/* PIE DE FORMULARIO COMÚN */}
            <div className="pie-formulario">
                {paso > 1 ? (
                    <button type="button" onClick={volverPaso} className="boton-volver">Atrás</button>
                ) : (
                    <p className="texto-login-pie">
                        ¿Ya tienes cuenta? <a href="/login" className="enlace-azul-registro">Inicia sesión</a>
                    </p>
                )}

                {paso < 3 && (
                    <button type="button" onClick={siguientePaso} className="boton-siguiente">
                        Siguiente
                    </button>
                )}
            </div>
        </div>
    );
};

export default FormularioRegistro;