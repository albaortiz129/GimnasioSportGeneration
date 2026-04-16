{{-- Correo de bienvenida enviado al crear una cuenta nueva en SeaFit. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido a SeaFit</title>
</head>
<body style="margin:0;padding:0;background:#f3f6fb;font-family:Arial,Helvetica,sans-serif;color:#0A1931;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:24px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:620px;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="background:#0A1931;padding:20px 24px;">
                            <h1 style="margin:0;font-size:24px;line-height:1.2;color:#ffffff;">Bienvenido a SeaFit</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 12px 0;font-size:16px;line-height:1.6;">
                                Hola {{ $user->nombre }} {{ $user->apellidos }},
                            </p>

                            <p style="margin:0 0 12px 0;font-size:15px;line-height:1.7;color:#1f2937;">
                                Tu cuenta se ha creado correctamente y ya formas parte de SeaFit.
                            </p>

                            <p style="margin:0 0 12px 0;font-size:15px;line-height:1.7;color:#1f2937;">
                                Plan seleccionado: <strong>{{ ucfirst($user->tarifa ?? 'sin definir') }}</strong><br>
                                Método de pago: <strong>{{ ucfirst($user->metodo_pago ?? 'sin definir') }}</strong>
                            </p>

                            <p style="margin:0 0 18px 0;font-size:15px;line-height:1.7;color:#1f2937;">
                                Puedes iniciar sesión cuando quieras para gestionar tus clases, reservas y pagos.
                            </p>

                            <a href="{{ url('/login') }}"
                                style="display:inline-block;background:#1A3878;color:#ffffff;text-decoration:none;font-weight:700;padding:12px 18px;border-radius:10px;">
                                Ir a iniciar sesión
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 20px 24px;">
                            <p style="margin:0;font-size:12px;color:#6b7280;line-height:1.6;">
                                Si no has solicitado esta cuenta, responde a este correo y lo revisaremos.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
