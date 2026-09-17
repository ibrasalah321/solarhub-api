<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationOtpMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $code,
        public readonly int $expiresInMinutes
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify your SolarHub email address'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verification-otp'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
