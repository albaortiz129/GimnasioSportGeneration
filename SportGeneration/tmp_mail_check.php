<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$to = App\Models\AppSetting::getValue('unpaid_notification_email', App\Support\MailAddresses::DEFAULT_ADMIN_NOTIFICATION_EMAIL);

Illuminate\Support\Facades\Mail::raw('Prueba de correo SMTP de Sport Generation.', function ($message) use ($to) {
    $message->from(App\Support\MailAddresses::SUPPORT_ADDRESS, App\Support\MailAddresses::SUPPORT_NAME);
    $message->to($to);
    $message->subject('Prueba SMTP - Sport Generation');
});

echo "OK: correo de prueba enviado a {$to}" . PHP_EOL;
