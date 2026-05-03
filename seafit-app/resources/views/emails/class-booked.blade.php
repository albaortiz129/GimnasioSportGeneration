{{-- Plantilla de correo para confirmar una reserva de clase. --}}
<!DOCTYPE html>
<html lang="es">

<head>
    {{-- Título del correo. --}}
    <title>Reserva confirmada - SeaFit</title>
</head>

<body style="font-family: Arial, sans-serif; padding: 20px; color: #0A1931;">
    {{-- Cuerpo. --}}
    <h2>¡Reserva confirmada!</h2>
    <p>Hola {{ $nombre }}, tu plaza ha quedado reservada correctamente.</p>

    <div style="margin: 20px 0; padding: 16px; border: 1px solid #dbe4f0; border-radius: 8px; background: #f8fbff;">
        <p style="margin: 0 0 8px 0;"><strong>Clase:</strong> {{ $claseNombre }}</p>
        <p style="margin: 0 0 8px 0;"><strong>Día:</strong> {{ $diaSemana }}</p>
        <p style="margin: 0 0 8px 0;">
            <strong>Horario:</strong>
            {{ $horaInicio }}@if(!empty($horaFin)) - {{ $horaFin }}@endif
        </p>
        <p style="margin: 0 0 8px 0;"><strong>Sala:</strong> {{ $sala }}</p>
        <p style="margin: 0;"><strong>Instructor:</strong> {{ $instructor }}</p>
    </div>

    {{-- Cierre. --}}
    <p>Si no reconoces esta reserva, por favor, contacta con el equipo de SeaFit.</p>
    <p>Un saludo,<br>Equipo SeaFit</p>
</body>

</html>