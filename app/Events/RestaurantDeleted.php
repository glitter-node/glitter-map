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

class RestaurantDeleted implements ShouldBroadcast, ShouldDispatchAfterCommit, ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $restaurantId,
    ) {
        $this->onQueue('broadcasts');

        BroadcastTrace::log('event.constructor', [
            'event' => static::class,
            'mode' => 'queued',
            'restaurant_id' => $this->restaurantId,
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
        return 'restaurant.deleted';
    }

    public function broadcastWith(): array
    {
        $payload = [
            'id' => $this->restaurantId,
        ];

        BroadcastTrace::log('event.broadcastWith', [
            'event' => static::class,
            'payload' => $payload,
        ]);

        return $payload;
    }
}
