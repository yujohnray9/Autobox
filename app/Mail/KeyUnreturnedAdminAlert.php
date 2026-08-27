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

class KeyUnreturnedAdminAlert extends Mailable
{
    use Queueable, SerializesModels;

    public User $admin;
    public ?User $borrower;
    public Key $key;
    public ?Transaction $transaction;
    public string $timestamp;
    public string $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(User $admin, ?User $borrower, Key $key, ?Transaction $transaction = null, string $reason = 'Key has not been returned to the AUTOBOX terminal')
    {
        $this->admin = $admin;
        $this->borrower = $borrower;
        $this->key = $key;
        $this->transaction = $transaction;
        $this->reason = $reason;
        $this->timestamp = now()->format('F j, Y - h:i:s A');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $borrowerName = $this->borrower ? $this->borrower->name : 'Unknown User';
        return new Envelope(
            subject: "[AUTOBOX ALERT] Key is MISSING / Unreturned: {$this->key->key_name} (Slot #{$this->key->slot_number}) - Last Retrieved by {$borrowerName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.key_unreturned_admin',
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
