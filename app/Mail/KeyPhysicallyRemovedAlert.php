<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Key;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class KeyPhysicallyRemovedAlert extends Mailable
{
    use Queueable, SerializesModels;

    public User $admin;
    public ?User $lastBorrower;
    public Key $key;
    public ?Transaction $lastTransaction;
    public string $timestamp;

    /**
     * Create a new message instance.
     */
    public function __construct(User $admin, ?User $lastBorrower, Key $key, ?Transaction $lastTransaction = null)
    {
        $this->admin          = $admin;
        $this->lastBorrower   = $lastBorrower;
        $this->key            = $key;
        $this->lastTransaction = $lastTransaction;
        $this->timestamp      = now()->format('F j, Y - h:i:s A');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[AUTOBOX SECURITY] ⚠️ UNAUTHORIZED REMOVAL: {$this->key->key_name} (Slot #{$this->key->slot_number}) was removed WITHOUT QR Scan!",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.key_physically_removed_admin',
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
