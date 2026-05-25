{{-- Plantilla de correo para confirmar una reserva de clase. --}}
<!DOCTYPE html>
<html lang="es">

<head>
    {{-- TÃ­tulo del correo. --}}
    <title>Reserva confirmada - Sport Generation</title>
</head>

<body style="font-family: Arial, sans-serif; padding: 20px; color: #265E1F;">
    {{-- Cuerpo. --}}
    <h2>Â¡Reserva confirmada!</h2>
    <p>Hola {{ $nombre }}, tu plaza ha quedado reservada correctamente.</p>

    <div style="margin: 20px 0; padding: 16px; border: 1px solid #EAF7DB; border-radius: 8px; background: #EAF7DB;">
        <p style="margin: 0 0 8px 0;"><strong>Clase:</strong> {{ $claseNombre }}</p>
        <p style="margin: 0 0 8px 0;"><strong>DÃ­a:</strong> {{ $diaSemana }}</p>
        <p style="margin: 0 0 8px 0;">
            <strong>Horario:</strong>
            {{ $horaInicio }}@if(!empty($horaFin)) - {{ $horaFin }}@endif
        </p>
        <p style="margin: 0 0 8px 0;"><strong>Sala:</strong> {{ $sala }}</p>
        <p style="margin: 0;"><strong>Instructor:</strong> {{ $instructor }}</p>
    </div>

    {{-- Cierre. --}}
    <p>Si no reconoces esta reserva, por favor, contacta con el equipo de Sport Generation.</p>
    <p>Un saludo,<br>Equipo Sport Generation</p>
</body>

</html>
