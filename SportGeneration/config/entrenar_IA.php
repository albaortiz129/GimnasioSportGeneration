<?php

/**
 * Reglas de conocimiento del chat IA de Sport Generation.
 * Este archivo permite "entrenar" respuestas sin tocar la lógica del controlador.
 */
return [
    'rules' => [
        [
            'intent' => 'saludo',
            'priority' => 1,
            'tags' => ['hola', 'buenas', 'buenos días', 'buenas tardes', 'buenas noches', 'hey'],
            'answer' => 'Hola. Soy el asistente de Sport Generation. Puedo ayudarte con planes, pagos, reservas de clases y dudas del gimnasio.',
        ],

        [
            'intent' => 'planes_diferencias',
            'priority' => 10,
            'tags' => [
                'diferencia planes',
                'diferencia entre planes',
                'qué diferencia hay entre planes',
                'qué diferencia hay entre mensual y anual',
                'diferencia mensual anual',
                'comparar planes',
                'planes mensual trimestral anual',
                'tarifas',
                'precios planes',
            ],
            'answer' => 'La diferencia principal entre los planes es el ahorro: mensual 29,99 EUR, trimestral 75,00 EUR (ahorras 14,97 EUR frente a pagar 3 meses sueltos) y anual 250,00 EUR (ahorras 109,88 EUR frente a pagar 12 meses sueltos).',
        ],
        [
            'intent' => 'plan_mensual_precio',
            'priority' => 9,
            'must' => ['mensual'],
            'tags' => ['precio', 'cuánto cuesta', 'coste', 'tarifa mensual'],
            'answer' => 'El plan mensual cuesta 29,99 EUR.',
        ],
        [
            'intent' => 'plan_trimestral_precio',
            'priority' => 9,
            'must' => ['trimestral'],
            'tags' => ['precio', 'cuánto cuesta', 'coste', 'tarifa trimestral'],
            'answer' => 'El plan trimestral cuesta 75,00 EUR.',
        ],
        [
            'intent' => 'plan_anual_precio',
            'priority' => 9,
            'must' => ['anual'],
            'tags' => ['precio', 'cuánto cuesta', 'coste', 'tarifa anual'],
            'answer' => 'El plan anual cuesta 250,00 EUR.',
        ],
        [
            'intent' => 'plan_recomendacion',
            'priority' => 7,
            'tags' => ['qué plan me recomiendas', 'qué plan elegir', 'cuál plan me conviene', 'plan recomendado'],
            'answer' => 'Si buscas flexibilidad, el mensual. Si vas a entrenar varios meses, el trimestral ahorra más. Si entrenas todo el año, el anual es el que más ahorro ofrece.',
        ],

        [
            'intent' => 'ubicacion_gimnasio',
            'priority' => 10,
            'tags' => ['dónde está el gimnasio', 'dirección gimnasio', 'ubicación', 'calle sol', 'málaga', 'cómo llegar'],
            'answer' => 'El gimnasio está en Calle Sol, 3, Málaga.',
        ],

        [
            'intent' => 'metodos_pago_alta',
            'priority' => 10,
            'tags' => [
                'cómo puedo pagar',
                'métodos de pago',
                'formas de pago',
                'se puede pagar en efectivo',
                'pagar con tarjeta',
                'pago del registro',
            ],
            'answer' => 'En el registro puedes pagar con tarjeta o efectivo.',
        ],
        [
            'intent' => 'pago_manual_validacion',
            'priority' => 9,
            'tags' => ['pago en efectivo', 'validar pago', 'aprobación pago', 'pago pendiente', 'cuenta pendiente de pago'],
            'answer' => 'Si eliges pago manual, la cuenta queda pendiente hasta que el administrador valide el cobro.',
        ],
        [
            'intent' => 'cambio_plan_metodo_pago',
            'priority' => 8,
            'tags' => [
                'cambiar plan',
                'cambiar tarifa',
                'actualizar plan',
                'cambiar método de pago',
                'actualizar método de pago',
            ],
            'answer' => 'Puedes cambiar el plan y el método de pago desde "Gestión de Pago". Si el cambio es manual, puede quedar pendiente de validación del administrador.',
        ],
        [
            'intent' => 'descuentos_cupones',
            'priority' => 8,
            'tags' => ['código descuento', 'cupón', 'descuento', 'aplicar descuento', 'promoción'],
            'answer' => 'Puedes aplicar un código de descuento cuando esté disponible en el flujo de registro o en cambios de plan compatibles.',
        ],
        [
            'intent' => 'historial_pagos',
            'priority' => 7,
            'tags' => ['historial de pagos', 'historial de facturas', 'facturas', 'mis facturas', 'comprobantes de pago'],
            'answer' => 'Puedes revisar el historial de pagos y facturas desde la sección "Gestión de Pago".',
        ],

        [
            'intent' => 'cancelar_suscripcion',
            'priority' => 10,
            'tags' => ['cancelar suscripción', 'cómo cancelo mi suscripción', 'cancelo mi suscripción', 'dar de baja suscripción', 'baja del plan', 'cancelar renovación'],
            'answer' => 'Puedes cancelar la suscripción desde "Gestión de Pago". Se cancela la renovación automática, pero mantienes acceso hasta el final del período ya pagado.',
        ],
        [
            'intent' => 'reanudar_suscripcion',
            'priority' => 7,
            'tags' => ['reanudar suscripción', 'reactivar suscripción', 'volver a activar plan', 'activar plan de nuevo'],
            'answer' => 'Si tu plan estaba cancelado al final de período, puedes reactivarlo desde "Gestión de Pago".',
        ],

        [
            'intent' => 'clases_donde_ver',
            'priority' => 9,
            'tags' => ['dónde veo las clases', 'horario de clases', 'calendario de clases', 'agenda de clases', 'clases disponibles'],
            'answer' => 'Puedes ver el calendario de clases en la sección "Servicios", con día, hora, plazas y botón de reserva.',
        ],
        [
            'intent' => 'reserva_clase_como',
            'priority' => 10,
            'tags' => ['cómo reservar clase', 'cómo reservo una clase', 'reservar plaza', 'apuntarme a clase', 'inscribirme en una clase', 'quiero reservar'],
            'answer' => 'Para reservar una clase, entra en "Servicios", elige el día y pulsa "Reservar" en la clase que quieras.',
        ],
        [
            'intent' => 'cancelar_reserva',
            'priority' => 9,
            'tags' => ['cancelar reserva', 'quitar reserva', 'dar de baja clase', 'anular reserva'],
            'answer' => 'Puedes cancelar tu reserva desde el propio calendario o desde "Mis Reservas". Al cancelar, la plaza vuelve a estar disponible.',
        ],
        [
            'intent' => 'aforo_clases',
            'priority' => 9,
            'tags' => ['plazas libres', 'aforo', 'cupo', 'quedan plazas', 'clase llena'],
            'answer' => 'Cada clase muestra sus plazas libres. Si llega a 0, no se permiten más reservas hasta que alguien cancele.',
        ],
        [
            'intent' => 'mis_reservas',
            'priority' => 8,
            'tags' => ['mis reservas', 'dónde veo mis reservas', 'reservas activas', 'clases reservadas'],
            'answer' => 'Tus reservas aparecen en la sección "Mis Reservas" dentro del panel de socio.',
        ],
        [
            'intent' => 'faltas_clases',
            'priority' => 10,
            'tags' => ['si falto a clase', 'si falto a una clase', 'faltas a clase', 'ausencia clase', 'penalización por faltas', 'me banean por faltar'],
            'answer' => 'Si faltas a una clase no pasa nada, pero si faltas a más de 3 clases seguidas se aplica un baneo de 1 mes sin acceso a clases.',
        ],

        [
            'intent' => 'entrenador_uso',
            'priority' => 10,
            'must' => ['entrenador'],
            'avoid' => ['precio', 'coste', 'cuánto cuesta', 'vale'],
            'tags' => ['para qué sirve', 'qué hace', 'beneficios', 'en qué ayuda', 'entrenador personal'],
            'answer' => 'Un entrenador personal te ayuda con un plan adaptado a tus objetivos, corrige técnica para evitar lesiones y hace seguimiento de tu progreso.',
        ],
        [
            'intent' => 'entrenador_precio',
            'priority' => 10,
            'must' => ['entrenador'],
            'tags' => ['cuánto cuesta', 'precio', 'coste', 'vale', 'tarifa entrenador'],
            'answer' => 'El entrenador personal tiene un coste variable según las necesidades del cliente (objetivos, frecuencia y nivel de personalización).',
        ],

        [
            'intent' => 'acompanantes',
            'priority' => 9,
            'tags' => ['puedo llevar acompañante', 'se puede llevar acompañante', 'invitado', 'acompañantes', 'amigo'],
            'answer' => 'No se permite traer acompañantes de forma general.',
        ],
        [
            'intent' => 'prueba_gratis',
            'priority' => 9,
            'tags' => ['prueba gratis', 'primera entrada gratis', 'día de prueba', 'entrada de prueba'],
            'answer' => 'La primera entrada al gimnasio es gratis como prueba.',
        ],

        [
            'intent' => 'registro_login',
            'priority' => 7,
            'tags' => ['cómo me registro', 'crear cuenta', 'iniciar sesión', 'acceder cuenta', 'registro'],
            'answer' => 'Puedes crear tu cuenta desde "Registro" y luego iniciar sesión con tu email y contraseña.',
        ],
        [
            'intent' => 'recuperar_contrasena',
            'priority' => 8,
            'tags' => ['olvidé mi contraseña', 'recuperar contraseña', 'restablecer contraseña', 'no recuerdo mi clave', 'cambiar contraseña'],
            'answer' => 'Si olvidaste la contraseña, usa la opción de recuperación en login. Recibirás un correo con el enlace para restablecerla.',
        ],

        [
            'intent' => 'admin_funciones',
            'priority' => 6,
            'tags' => [
                'qué puede hacer el administrador',
                'panel de administrador',
                'funciones del admin',
                'admin',
            ],
            'answer' => 'En el panel de administrador se pueden gestionar usuarios, planes, estados de pago, cobros manuales, clases e inscripciones, y códigos de descuento.',
        ],

        [
            'intent' => 'soporte_contacto',
            'priority' => 11,
            'tags' => ['soporte', 'contacto', 'ayuda', 'email', 'correo', 'no responde', 'no funciona'],
            'answer' => 'Si necesitas ayuda directa, contacta con soporte en soporte.seafit@gmail.com.',
        ],
    ],
];
