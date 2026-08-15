<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccessLogged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ?string $userName;
    public string $action;
    public string $result;
    public string $reason;
    public ?string $keyName;
    public ?string $roomName;
    public string $createdAtFormatted;

    public function __construct(?string $userName, string $action, string $result, string $reason, ?string $keyName = null, ?string $roomName = null)
    {
        $this->userName = $userName ?? 'System User';
        $this->action = $action;
        $this->result = $result;
        $this->reason = $reason;
        $this->keyName = $keyName;
        $this->roomName = $roomName;
        $this->createdAtFormatted = now()->diffForHumans();
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('autobox-hardware'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'AccessLogged';
    }
}
