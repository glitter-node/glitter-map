<?php

namespace App\Events;

use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RestaurantCreated implements ShouldBroadcast, ShouldDispatchAfterCommit, ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use SerializesModels;

    public function __construct(public Restaurant $restaurant)
    {
        $this->onQueue('broadcasts');
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('restaurants.map'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'restaurant.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->restaurant->id,
            'name' => $this->restaurant->name,
            'latitude' => $this->restaurant->latitude,
            'longitude' => $this->restaurant->longitude,
        ];
    }
}
