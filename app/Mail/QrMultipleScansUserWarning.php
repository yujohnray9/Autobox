<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QrMultipleScansUserWarning extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public int $scanCount;
    public string $timestamp;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, int $scanCount)
    {
        $this->user = $user;
        $this->scanCount = $scanCount;
        $this->timestamp = now()->format('F j, Y - h:i:s A');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "⚠️ [AUTOBOX Security Notification] Multiple scans detected on your QR Code",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.qr_user_warning',
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
