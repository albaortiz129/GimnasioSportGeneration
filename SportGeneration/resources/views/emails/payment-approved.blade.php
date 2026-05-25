{{-- Plantilla de correo para confirmar un pago aprobado al socio. --}}
<!DOCTYPE html>
<html lang="es">

<head>
    {{-- Título. --}}
    <meta charset="UTF-8">
    <title>Pago aprobado - Sport Generation</title>
</head>

<body style="font-family: Arial, sans-serif; padding: 20px; color: #265e1f;">
    {{-- Cuerpo. --}}
    <h2>Hola, {{ $nombre }}</h2>
    <p>Tu pago ha sido aprobado correctamente en Sport Generation.</p>

    <p><strong>Método de pago:</strong> {{ $metodo }}</p>
    <p><strong>Plan activo:</strong> {{ $tarifa }}</p>
    <p><strong>Próximo cobro:</strong> {{ $proximoCobro }}</p>

    @if(!empty($origen))
        <p><strong>Detalle:</strong> {{ $origen }}</p>
    @endif

    {{-- Cierre. --}}
    <p>Si no has solicitado esta acción, por favor, contacta con el equipo de Sport Generation.</p>
    <p>Un saludo,<br>El equipo de Sport Generation</p>
</body>

</html>