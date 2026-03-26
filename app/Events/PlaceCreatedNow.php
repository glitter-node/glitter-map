<?php

namespace App\Events;

use App\Models\Place;
use App\Support\BroadcastTrace;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlaceCreatedNow implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Place $place)
    {
        BroadcastTrace::log('event.constructor', [
            'event' => static::class,
            'mode' => 'sync',
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
        return 'place.created.now';
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
