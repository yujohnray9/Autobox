<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KeyStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $keyId;
    public int $slotNumber;
    public string $status;
    public string $keyName;
    public string $roomName;
    public ?string $borrowerName;

    public function __construct(int $keyId, int $slotNumber, string $status, string $keyName, string $roomName, ?string $borrowerName = null)
    {
        $this->keyId = $keyId;
        $this->slotNumber = $slotNumber;
        $this->status = $status;
        $this->keyName = $keyName;
        $this->roomName = $roomName;
        $this->borrowerName = $borrowerName;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('autobox-hardware'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'KeyStatusUpdated';
    }
}
