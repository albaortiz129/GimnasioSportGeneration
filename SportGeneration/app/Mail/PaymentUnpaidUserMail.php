<?php

namespace App\Mail;

use App\Models\User;
use App\Support\MailAddresses;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentUnpaidUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $metodo,
        public string $proximoCobro,
        public string $origen
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(MailAddresses::SUPPORT_ADDRESS, MailAddresses::SUPPORT_NAME),
            subject: 'Aviso de impago - Sport Generation',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-unpaid',
            with: [
                'nombre' => $this->user->nombre,
                'tarifa' => ucfirst((string) $this->user->tarifa),
                'metodo' => $this->metodo,
                'proximoCobro' => $this->proximoCobro,
                'origen' => $this->origen,
            ],
        );
    }
}
