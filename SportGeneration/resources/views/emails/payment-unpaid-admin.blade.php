{{-- Aviso interno para administración cuando un socio queda impagado. --}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Aviso interno de impago - Sport Generation</title>
</head>

<body style="font-family: Arial, sans-serif; padding: 20px; color: #265e1f;">
    <h2>Aviso interno de impago</h2>
    <p>Se ha marcado un cliente como impagado en Sport Generation.</p>

    <p><strong>Cliente:</strong> {{ $nombre }}</p>
    <p><strong>Email:</strong> {{ $email }}</p>
    <p><strong>DNI:</strong> {{ $dni }}</p>
    <p><strong>Plan:</strong> {{ $tarifa }}</p>
    <p><strong>Método de pago:</strong> {{ $metodo }}</p>
    <p><strong>Próximo cobro:</strong> {{ $proximoCobro }}</p>

    @if(!empty($origen))
        <p><strong>Detalle:</strong> {{ $origen }}</p>
    @endif

    <p>Revisa el panel de administración para gestionar este caso.</p>
    @include('emails.partials.no-reply-notice')
</body>

</html>
