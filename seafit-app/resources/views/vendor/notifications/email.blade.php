{{-- Plantilla base de notificaciones por correo de Laravel. --}}
<x-mail::message>
    {{-- Titulo del correo.--}}
    @if (!empty($greeting))
        # {{ $greeting }}
    @else
        @if ($level === 'error')
            # @lang('Whoops!')
        @else
            # @lang('Hello!')
        @endif
    @endif

    {{-- Texto principal del correo. --}}
    @foreach ($introLines as $line)
        {{ $line }}

    @endforeach

    {{-- Boton de accion. --}}
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

    {{-- Texto final del correo. --}}
    @foreach ($outroLines as $line)
        {{ $line }}

    @endforeach

    {{-- Firma. --}}
    @if (!empty($salutation))
        {{ $salutation }}
    @else
        @lang('Regards,')<br>
        {{ config('app.name') }}
    @endif

    {{-- Enlace en texto plano (opcional). --}}
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