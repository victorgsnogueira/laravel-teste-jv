<?php

namespace App\Events;

use App\Models\Pix;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PixStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public array $stats
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('pix-dashboard')
        ];
    }

    public function broadcastAs(): string
    {
        return 'pix.updated';
    }
}
