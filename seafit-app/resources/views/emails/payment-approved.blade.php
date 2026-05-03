{{-- Plantilla de correo para confirmar un pago aprobado al socio. --}}
<!DOCTYPE html>
<html lang="es">

<head>
    {{-- Título. --}}
    <meta charset="UTF-8">
    <title>Pago aprobado - SeaFit</title>
</head>

<body style="font-family: Arial, sans-serif; padding: 20px; color: #0A1931;">
    {{-- Cuerpo. --}}
    <h2>Hola, {{ $nombre }}</h2>
    <p>Tu pago ha sido aprobado correctamente en SeaFit.</p>

    <p><strong>Método de pago:</strong> {{ $metodo }}</p>
    <p><strong>Plan activo:</strong> {{ $tarifa }}</p>
    <p><strong>Próximo cobro:</strong> {{ $proximoCobro }}</p>

    @if(!empty($origen))
        <p><strong>Detalle:</strong> {{ $origen }}</p>
    @endif

    {{-- Cierre. --}}
    <p>Si no has solicitado esta acción, por favor, contacta con el equipo de SeaFit.</p>
    <p>Un saludo,<br>El equipo de SeaFit</p>
</body>

</html>