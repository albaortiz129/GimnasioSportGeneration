<x-mail::message>
    {{-- Titulo del correo.
    Si el sistema trae un saludo personalizado, se usa ese.
    Si no, se muestra uno por defecto (normal o de error).--}}
    @if (!empty($greeting))
        # {{ $greeting }}
    @else
        @if ($level === 'error')
            # @lang('Whoops!')
        @else
            # @lang('Hello!')
        @endif
    @endif

    {{-- Texto principal del correo.
    Aqui se pintan los mensajes iniciales (una o varias lineas).--}}
    @foreach ($introLines as $line)
        {{ $line }}

    @endforeach

    {{-- Boton de accion .
    Solo aparece cuando la notificacion trae texto de accion.
    El color cambia segun el tipo de mensaje. --}}
    @isset($actionText)
        <?php
        $color = match ($level) {
            'success', 'error' => $level,
            default => 'primary',
        };
                        ?>
        <x-mail::button :url="$actionUrl" :color="$color">
            {{ $actionText }}
        </x-mail::button>
    @endisset

    {{-- Texto final del correo.
    Aqui se pintan las lineas de cierre antes de la firma.--}}
    @foreach ($outroLines as $line)
        {{ $line }}

    @endforeach

    {{-- Firma.
    Si llega una firma personalizada, se usa.
    Si no, se usa la firma por defecto con el nombre de la app.--}}
    @if (!empty($salutation))
        {{ $salutation }}
    @else
        @lang('Regards,')<br>
        {{ config('app.name') }}
    @endif

    {{-- Enlace en texto plano (opcional).
    Se muestra como apoyo por si el boton no funciona en el cliente de correo.--}}
    @isset($actionText)
        <x-slot:subcopy>
            @lang(
                "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\n" .
                'into your web browser:',
                [
                    'actionText' => $actionText,
                ]
            ) <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
        </x-slot:subcopy>
    @endisset
</x-mail::message>