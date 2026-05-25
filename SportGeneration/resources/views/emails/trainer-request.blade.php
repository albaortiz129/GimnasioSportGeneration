{{-- Plantilla del correo para nuevas solicitudes de entrenador personal. --}}
<div style="font-family: Arial, sans-serif; color: #265e1f; line-height: 1.5;">
    {{-- Título. --}}
    <h2>Nueva solicitud de entrenador personal - Sport Generation</h2>

    {{-- Cuerpo. --}}
    <p style="margin: 0 0 8px 0;"><strong>Nombre:</strong> {{ $data['nombre'] }}</p>
    <p style="margin: 0 0 8px 0;"><strong>Correo:</strong> {{ $data['email'] }}</p>
    <p style="margin: 0 0 8px 0;"><strong>Objetivo:</strong> {{ $data['objetivo'] }}</p>

    <p style="margin: 12px 0 6px 0;"><strong>Mensaje:</strong></p>
    <div style="background: #f5f7fb; border: 1px solid #dbe2ee; border-radius: 8px; padding: 10px;">
        {{ $data['mensaje'] !== '' ? $data['mensaje'] : 'Sin mensaje adicional.' }}
    </div>

    {{-- Cierre. --}}
    <p style="margin: 14px 0 0 0; color: #5f6b85; font-size: 13px;">
        Este correo ha sido enviado desde el formulario de Sport Generation.
    </p>
</div>