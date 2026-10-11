<?php

namespace App\Services;

use App\Events\RealtimeUpdate;
use App\Models\Trip;
use Illuminate\Support\Facades\Log;

final class RealtimePublisher
{
    public function trip(Trip $trip, string $type, array $data = []): void
    {
        $this->send(new RealtimeUpdate($type, $trip->id, $data));
    }

    public function user(int $userId, string $type, ?int $tripId = null, array $data = []): void
    {
        $this->send(new RealtimeUpdate($type, $tripId, $data, [$userId]));
    }

    private function send(RealtimeUpdate $event): void
    {
        try { event($event); }
        catch (\Throwable $e) { Log::warning('Realtime update could not be queued.', ['type'=>$event->type,'trip_id'=>$event->tripId,'error'=>$e->getMessage()]); }
    }
}