<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QrMultipleScansAdminAlert extends Mailable
{
    use Queueable, SerializesModels;

    public ?User $scannedUser;
    public string $qrToken;
    public int $scanCount;
    public string $ipAddress;
    public string $timestamp;

    /**
     * Create a new message instance.
     */
    public function __construct(?User $scannedUser, string $qrToken, int $scanCount, string $ipAddress)
    {
        $this->scannedUser = $scannedUser;
        $this->qrToken = $qrToken;
        $this->scanCount = $scanCount;
        $this->ipAddress = $ipAddress;
        $this->timestamp = now()->format('F j, Y - h:i:s A');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $userName = $this->scannedUser ? $this->scannedUser->name : 'Unknown User';
        return new Envelope(
            subject: "🚨 [AUTOBOX SECURITY ALERT] QR Code Scanned {$this->scanCount} Times ({$userName})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.qr_admin_alert',
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
