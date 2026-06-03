<?php
use App\Services\BillingStatusService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Comando de ejemplo de Laravel.
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('billing:mark-overdue', function (BillingStatusService $billingStatusService) {
    $result = $billingStatusService->markOverduePayments();

    $this->info("Clientes marcados como impagados: {$result['updated']}");

    if ($result['emails_failed'] > 0) {
        $this->warn("Clientes con algún correo fallido: {$result['emails_failed']}");
    }
})->purpose('Marca automáticamente como impagados los cobros vencidos');

Schedule::command('billing:mark-overdue')->dailyAt('08:00');
