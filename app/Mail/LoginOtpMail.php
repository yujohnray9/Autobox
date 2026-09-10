<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $otp;
    public string $expiresInMinutes;
    public string $ipAddress;
    public string $timestamp;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $otp, string $ipAddress = '127.0.0.1', int $expiresInMinutes = 10)
    {
        $this->user = $user;
        $this->otp = $otp;
        $this->expiresInMinutes = (string) $expiresInMinutes;
        $this->ipAddress = $ipAddress;
        $this->timestamp = now()->format('F j, Y · h:i A');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your AUTOBOX Login Verification Code: {$this->otp}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.login_otp',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
