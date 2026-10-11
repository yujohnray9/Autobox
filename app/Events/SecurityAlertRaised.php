<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Pushed instantly (via Pusher) to every open admin page whenever an
 * unauthorized / denied access attempt is recorded.
 */
class SecurityAlertRaised implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public array $alert,
        public int $todayCount,
    ) {
    }

    public function broadcastOn(): array
    {
        return [new Channel('autobox-hardware')];
    }

    public function broadcastAs(): string
    {
        return 'SecurityAlertRaised';
    }
}
