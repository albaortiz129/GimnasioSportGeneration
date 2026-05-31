<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

App\Models\AppSetting::setValue('unpaid_notification_email', 'alba.ortiz129@gmail.com');

echo App\Models\AppSetting::getValue('unpaid_notification_email') . PHP_EOL;
