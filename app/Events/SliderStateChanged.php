<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SliderStateChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $state;
    public string $reason;

    public function __construct(string $state, string $reason = '')
    {
        $this->state = $state;
        $this->reason = $reason;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('autobox-hardware'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'SliderStateChanged';
    }
}
