<?php

/**
 * Reglas de conocimiento del chat IA de SeaFit.
 * Este archivo permite "entrenar" respuestas sin tocar la logica del controlador.
 */
return [
    'rules' => [
        [
            'intent' => 'saludo',
            'priority' => 1,
            'tags' => ['hola', 'buenas', 'buenos dias', 'buenas tardes', 'buenas noches', 'hey'],
            'answer' => 'Hola. Soy el asistente de SeaFit. Puedo ayudarte con planes, pagos, reservas de clases y dudas del gimnasio.',
        ],

        [
            'intent' => 'planes_diferencias',
            'priority' => 10,
            'tags' => [
                'diferencia planes',
                'diferencia entre planes',
                'que diferencia hay entre planes',
                'que diferencia hay entre mensual y anual',
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
            'tags' => ['precio', 'cuanto cuesta', 'coste', 'tarifa mensual'],
            'answer' => 'El plan mensual cuesta 29,99 EUR.',
        ],
        [
            'intent' => 'plan_trimestral_precio',
            'priority' => 9,
            'must' => ['trimestral'],
            'tags' => ['precio', 'cuanto cuesta', 'coste', 'tarifa trimestral'],
            'answer' => 'El plan trimestral cuesta 75,00 EUR.',
        ],
        [
            'intent' => 'plan_anual_precio',
            'priority' => 9,
            'must' => ['anual'],
            'tags' => ['precio', 'cuanto cuesta', 'coste', 'tarifa anual'],
            'answer' => 'El plan anual cuesta 250,00 EUR.',
        ],
        [
            'intent' => 'plan_recomendacion',
            'priority' => 7,
            'tags' => ['que plan me recomiendas', 'que plan elegir', 'cual plan me conviene', 'plan recomendado'],
            'answer' => 'Si buscas flexibilidad, el mensual. Si vas a entrenar varios meses, el trimestral ahorra mas. Si entrenas todo el ano, el anual es el que mas ahorro ofrece.',
        ],

        [
            'intent' => 'ubicacion_gimnasio',
            'priority' => 10,
            'tags' => ['donde esta el gimnasio', 'direccion gimnasio', 'ubicacion', 'calle sol', 'malaga', 'como llegar'],
            'answer' => 'El gimnasio esta en Calle Sol, 3, Malaga.',
        ],

        [
            'intent' => 'metodos_pago_alta',
            'priority' => 10,
            'tags' => [
                'como puedo pagar',
                'metodos de pago',
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
            'tags' => ['pago en efectivo', 'validar pago', 'aprobacion pago', 'pago pendiente', 'cuenta pendiente de pago'],
            'answer' => 'Si eliges pago manual, la cuenta queda pendiente hasta que el administrador valide el cobro.',
        ],
        [
            'intent' => 'cambio_plan_metodo_pago',
            'priority' => 8,
            'tags' => [
                'cambiar plan',
                'cambiar tarifa',
                'actualizar plan',
                'cambiar metodo de pago',
                'actualizar metodo de pago',
            ],
            'answer' => 'Puedes cambiar el plan y el metodo de pago desde "Gestion de Pago". Si el cambio es manual, puede quedar pendiente de validacion del administrador.',
        ],
        [
            'intent' => 'descuentos_cupones',
            'priority' => 8,
            'tags' => ['codigo descuento', 'cupon', 'descuento', 'aplicar descuento', 'promocion'],
            'answer' => 'Puedes aplicar un codigo de descuento cuando este disponible en el flujo de registro o en cambios de plan compatibles.',
        ],
        [
            'intent' => 'historial_pagos',
            'priority' => 7,
            'tags' => ['historial de pagos', 'historial de facturas', 'facturas', 'mis facturas', 'comprobantes de pago'],
            'answer' => 'Puedes revisar el historial de pagos y facturas desde la seccion "Gestion de Pago".',
        ],

        [
            'intent' => 'cancelar_suscripcion',
            'priority' => 10,
            'tags' => ['cancelar suscripcion', 'como cancelo mi suscripcion', 'cancelo mi suscripcion', 'dar de baja suscripcion', 'baja del plan', 'cancelar renovacion'],
            'answer' => 'Puedes cancelar la suscripcion desde "Gestion de Pago". Se cancela la renovacion automatica, pero mantienes acceso hasta el final del periodo ya pagado.',
        ],
        [
            'intent' => 'reanudar_suscripcion',
            'priority' => 7,
            'tags' => ['reanudar suscripcion', 'reactivar suscripcion', 'volver a activar plan', 'activar plan de nuevo'],
            'answer' => 'Si tu plan estaba cancelado al final de periodo, puedes reactivarlo desde "Gestion de Pago".',
        ],

        [
            'intent' => 'clases_donde_ver',
            'priority' => 9,
            'tags' => ['donde veo las clases', 'horario de clases', 'calendario de clases', 'agenda de clases', 'clases disponibles'],
            'answer' => 'Puedes ver el calendario de clases en la seccion "Servicios", con dia, hora, plazas y boton de reserva.',
        ],
        [
            'intent' => 'reserva_clase_como',
            'priority' => 10,
            'tags' => ['como reservar clase', 'como reservo una clase', 'reservar plaza', 'apuntarme a clase', 'inscribirme en una clase', 'quiero reservar'],
            'answer' => 'Para reservar una clase, entra en "Servicios", elige el dia y pulsa "Reservar" en la clase que quieras.',
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
            'answer' => 'Cada clase muestra sus plazas libres. Si llega a 0, no se permiten mas reservas hasta que alguien cancele.',
        ],
        [
            'intent' => 'mis_reservas',
            'priority' => 8,
            'tags' => ['mis reservas', 'donde veo mis reservas', 'reservas activas', 'clases reservadas'],
            'answer' => 'Tus reservas aparecen en la seccion "Mis Reservas" dentro del panel de socio.',
        ],
        [
            'intent' => 'faltas_clases',
            'priority' => 10,
            'tags' => ['si falto a clase', 'si falto a una clase', 'faltas a clase', 'ausencia clase', 'penalizacion por faltas', 'me banean por faltar'],
            'answer' => 'Si faltas a una clase no pasa nada, pero si faltas a mas de 3 clases seguidas se aplica un baneo de 1 mes sin acceso a clases.',
        ],

        [
            'intent' => 'entrenador_uso',
            'priority' => 10,
            'must' => ['entrenador'],
            'avoid' => ['precio', 'coste', 'cuanto cuesta', 'vale'],
            'tags' => ['para que sirve', 'que hace', 'beneficios', 'en que ayuda', 'entrenador personal'],
            'answer' => 'Un entrenador personal te ayuda con un plan adaptado a tus objetivos, corrige tecnica para evitar lesiones y hace seguimiento de tu progreso.',
        ],
        [
            'intent' => 'entrenador_precio',
            'priority' => 10,
            'must' => ['entrenador'],
            'tags' => ['cuanto cuesta', 'precio', 'coste', 'vale', 'tarifa entrenador'],
            'answer' => 'El entrenador personal tiene un coste variable segun las necesidades del cliente (objetivos, frecuencia y nivel de personalizacion).',
        ],

        [
            'intent' => 'acompanantes',
            'priority' => 9,
            'tags' => ['puedo llevar acompanante', 'se puede llevar acompanante', 'invitado', 'acompanantes', 'amigo'],
            'answer' => 'No se permite traer acompanantes de forma general.',
        ],
        [
            'intent' => 'prueba_gratis',
            'priority' => 9,
            'tags' => ['prueba gratis', 'primera entrada gratis', 'dia de prueba', 'entrada de prueba'],
            'answer' => 'La primera entrada al gimnasio es gratis como prueba.',
        ],

        [
            'intent' => 'registro_login',
            'priority' => 7,
            'tags' => ['como me registro', 'crear cuenta', 'iniciar sesion', 'acceder cuenta', 'registro'],
            'answer' => 'Puedes crear tu cuenta desde "Registro" y luego iniciar sesion con tu email y contrasena.',
        ],
        [
            'intent' => 'recuperar_contrasena',
            'priority' => 8,
            'tags' => ['olvide mi contrasena', 'recuperar contrasena', 'restablecer contrasena', 'no recuerdo mi clave', 'cambiar contrasena'],
            'answer' => 'Si olvidaste la contrasena, usa la opcion de recuperacion en login. Recibiras un correo con el enlace para restablecerla.',
        ],

        [
            'intent' => 'admin_funciones',
            'priority' => 6,
            'tags' => [
                'que puede hacer el administrador',
                'panel de administrador',
                'funciones del admin',
                'admin',
            ],
            'answer' => 'En el panel de administrador se pueden gestionar usuarios, planes, estados de pago, cobros manuales, clases e inscripciones, y codigos de descuento.',
        ],

        [
            'intent' => 'soporte_contacto',
            'priority' => 11,
            'tags' => ['soporte', 'contacto', 'ayuda', 'email', 'correo', 'no responde', 'no funciona'],
            'answer' => 'Si necesitas ayuda directa, contacta con soporte en soporte.seafit@gmail.com.',
        ],
    ],
];
