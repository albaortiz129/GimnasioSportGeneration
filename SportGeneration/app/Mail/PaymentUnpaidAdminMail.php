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

class PaymentUnpaidAdminMail extends Mailable
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
            from: new Address(MailAddresses::supportAddress(), MailAddresses::supportName()),
            subject: 'Aviso interno de impago - Sport Generation',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-unpaid-admin',
            with: [
                'nombre' => $this->user->nombre . ' ' . $this->user->apellidos,
                'email' => $this->user->email,
                'dni' => $this->user->dni,
                'tarifa' => ucfirst((string) $this->user->tarifa),
                'metodo' => $this->metodo,
                'proximoCobro' => $this->proximoCobro,
                'origen' => $this->origen,
            ],
        );
    }
}
