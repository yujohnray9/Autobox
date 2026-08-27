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

class KeyUnreturnedUserNotice extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Key $key;
    public ?Transaction $transaction;
    public string $timestamp;
    public string $expiredReason;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Key $key, ?Transaction $transaction = null, string $expiredReason = 'Your scheduled time has expired, but the key has not yet been returned to the AUTOBOX.')
    {
        $this->user = $user;
        $this->key = $key;
        $this->transaction = $transaction;
        $this->expiredReason = $expiredReason;
        $this->timestamp = now()->format('F j, Y - h:i:s A');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[AUTOBOX Urgent] Unreturned Key Alert: {$this->key->key_name} (Slot #{$this->key->slot_number}) - Please Return Key or Report to Admin",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.key_unreturned_user',
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
