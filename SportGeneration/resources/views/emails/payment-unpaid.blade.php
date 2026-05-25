{{-- Plantilla de correo para avisar al socio cuando queda en estado impagado. --}}
<!DOCTYPE html>
<html lang="es">

<head>
    {{-- Título. --}}
    <meta charset="UTF-8">
    <title>Aviso de impago - Sport Generation</title>
</head>

<body style="font-family: Arial, sans-serif; padding: 20px; color: #265e1f;">
    {{-- Cuerpo. --}}
    <h2>Hola, {{ $nombre }}</h2>
    <p>Tu cuenta en Sport Generation ha pasado a estado <strong>impagado</strong>.</p>

    <p><strong>Plan:</strong> {{ $tarifa }}</p>
    <p><strong>Método de pago:</strong> {{ $metodo }}</p>
    <p><strong>Próximo cobro:</strong> {{ $proximoCobro }}</p>

    @if(!empty($origen))
        <p><strong>Detalle:</strong> {{ $origen }}</p>
    @endif

    <p>Para regularizar tu situación, contacta con el equipo del gimnasio o accede a tu panel para revisar el pago.</p>
    <p>Un saludo,<br>El equipo de Sport Generation</p>
</body>

</html>
