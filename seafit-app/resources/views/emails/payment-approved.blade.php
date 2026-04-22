<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Pago aprobado - SeaFit</title>
</head>

<body style="font-family: Arial, sans-serif; padding: 20px; color: #0A1931;">
    <h2>Hola, {{ $nombre }}</h2>

    <p>Tu pago ha sido aprobado correctamente en SeaFit.</p>

    <p><strong>Metodo de pago:</strong> {{ $metodo }}</p>
    <p><strong>Plan activo:</strong> {{ $tarifa }}</p>
    <p><strong>Proximo cobro:</strong> {{ $proximoCobro }}</p>

    @if(!empty($origen))
        <p><strong>Detalle:</strong> {{ $origen }}</p>
    @endif

    <p style="margin-top: 24px;">Gracias por confiar en SeaFit.</p>
    <p>Un saludo,<br>El equipo de SeaFit</p>
</body>

</html>
