<?php

namespace App\Support;

class MailAddresses
{
    public static function supportAddress(): string
    {
        return (string) config('mail.from.address', 'noreply@example.com');
    }

    public static function supportName(): string
    {
        return (string) config('mail.from.name', 'Sport Generation');
    }

    public static function trainerRequestRecipient(): string
    {
        return (string) config('services.sport_generation.trainer_request_email', self::supportAddress());
    }

    public static function defaultAdminNotificationEmail(): ?string
    {
        $email = trim((string) config('services.sport_generation.unpaid_notification_email', ''));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }
}
