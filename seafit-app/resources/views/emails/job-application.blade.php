{{-- Plantilla de correo interno para "Trabaja con nosotros". Este email lo recibe soporte con los datos del candidato.
--}}
<div style="font-family: Arial, sans-serif; color: #0A1931; line-height: 1.5;">
    {{-- Titulo. --}}
    <h2>Nueva candidatura recibida - SeaFit</h2>

    {{-- Formulario. --}}
    <p style="margin: 0 0 8px 0;"><strong>Nombre:</strong> {{ $data['nombre'] }}</p>
    <p style="margin: 0 0 8px 0;"><strong>Correo:</strong> {{ $data['email'] }}</p>
    <p style="margin: 0 0 8px 0;"><strong>Teléfono:</strong>
        {{ $data['telefono'] !== '' ? $data['telefono'] : 'No indicado' }}</p>
    <p style="margin: 0 0 8px 0;"><strong>Puesto:</strong> {{ $data['puesto'] }}</p>

    {{-- Mensaje libre. --}}
    <p style="margin: 12px 0 6px 0;"><strong>Mensaje:</strong></p>
    <div style="background: #f5f7fb; border: 1px solid #dbe2ee; border-radius: 8px; padding: 10px;">
        {{ $data['mensaje'] !== '' ? $data['mensaje'] : 'Sin mensaje adicional.' }}
    </div>

    {{-- Nota final. --}}
    <p style="margin: 14px 0 0 0; color: #5f6b85; font-size: 13px;">
        El CV se adjunta en este mismo correo.
    </p>
</div>