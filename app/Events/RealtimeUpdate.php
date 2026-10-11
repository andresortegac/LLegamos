<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class RealtimeUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $type,
        public readonly ?int $tripId = null,
        public readonly array $data = [],
        public readonly array $userIds = [],
    ) {}

    public function broadcastOn(): array
    {
        $channels = [];
        if ($this->tripId) $channels[] = new PrivateChannel('trips.'.$this->tripId);
        foreach (array_unique($this->userIds) as $userId) $channels[] = new PrivateChannel('users.'.$userId);
        return $channels;
    }

    public function broadcastAs(): string { return 'realtime.update'; }
    public function broadcastWith(): array { return ['type'=>$this->type,'trip_id'=>$this->tripId,'data'=>$this->data]; }
    public function broadcastQueue(): string { return 'broadcasts'; }
}