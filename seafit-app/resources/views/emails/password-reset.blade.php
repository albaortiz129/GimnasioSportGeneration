{{-- Plantilla de correo para enviar enlace de recuperación de contraseña. --}}
<!DOCTYPE html>
<html>

<head>
    {{-- Título del correo. --}}
    <title>Recuperar contraseña - SeaFit</title>
</head>

<body style="font-family: Arial, sans-serif; padding: 20px; color: #0A1931;">
    {{-- Cuerpo. --}}
    <h2>Hola,</h2>
    <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en SeaFit.</p>
    <p>Haz clic en el siguiente enlace para crear una nueva contraseña:</p>

    {{-- Enlace con token único para recuperar la contraseña --}}
    <p style="margin: 30px 0;">
        <a href="{{ route('password.reset', $token) }}"
            style="background-color: #1A3878; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Restablecer contraseña
        </a>
    </p>

    {{-- Cierre. --}}
    <p>Si no has solicitado este cambio, ignora este correo.</p>
    <p>Un saludo,<br>El equipo de SeaFit</p>
</body>

</html>