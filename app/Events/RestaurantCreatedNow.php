<?php

namespace App\Events;

use App\Models\Restaurant;
use App\Support\BroadcastTrace;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RestaurantCreatedNow implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Restaurant $restaurant)
    {
        BroadcastTrace::log('event.constructor', [
            'event' => static::class,
            'mode' => 'sync',
            'restaurant_id' => $this->restaurant->id,
        ]);
    }

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('restaurants.map'),
        ];

        BroadcastTrace::log('event.broadcastOn', [
            'event' => static::class,
            'channels' => array_map(fn (Channel $channel) => $channel->name, $channels),
        ]);

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'restaurant.created.now';
    }

    public function broadcastWith(): array
    {
        $payload = [
            'id' => $this->restaurant->id,
            'name' => $this->restaurant->name,
            'latitude' => $this->restaurant->latitude,
            'longitude' => $this->restaurant->longitude,
        ];

        BroadcastTrace::log('event.broadcastWith', [
            'event' => static::class,
            'payload' => $payload,
        ]);

        return $payload;
    }
}
