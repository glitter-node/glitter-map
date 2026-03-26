<?php

namespace App\Events;

use App\Models\Place;
use App\Support\BroadcastTrace;
use Illuminate\Bus\Queueable;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlaceUpdated implements ShouldBroadcast, ShouldDispatchAfterCommit, ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use SerializesModels;

    public function __construct(public Place $place)
    {
        $this->onQueue('broadcasts');

        BroadcastTrace::log('event.constructor', [
            'event' => static::class,
            'mode' => 'queued',
            'place_id' => $this->place->id,
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
        return 'place.updated';
    }

    public function broadcastWith(): array
    {
        $payload = [
            'id' => $this->place->id,
            'name' => $this->place->name,
            'latitude' => $this->place->latitude,
            'longitude' => $this->place->longitude,
        ];

        BroadcastTrace::log('event.broadcastWith', [
            'event' => static::class,
            'payload' => $payload,
        ]);

        return $payload;
    }
}
