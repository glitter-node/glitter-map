<?php

namespace App\Events;

use App\Support\BroadcastTrace;
use Illuminate\Bus\Queueable;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlaceDeleted implements ShouldBroadcast, ShouldDispatchAfterCommit, ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $placeId,
    ) {
        $this->onQueue('broadcasts');

        BroadcastTrace::log('event.constructor', [
            'event' => static::class,
            'mode' => 'queued',
            'place_id' => $this->placeId,
        ]);
    }

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('places.map'),
        ];

        BroadcastTrace::log('event.broadcastOn', [
            'event' => static::class,
            'channels' => array_map(fn (Channel $channel) => $channel->name, $channels),
        ]);

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'place.deleted';
    }

    public function broadcastWith(): array
    {
        $payload = [
            'id' => $this->placeId,
        ];

        BroadcastTrace::log('event.broadcastWith', [
            'event' => static::class,
            'payload' => $payload,
        ]);

        return $payload;
    }
}
