<?php

namespace App\Events;

use App\Models\Place;
use Illuminate\Bus\Queueable;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlaceCreated implements ShouldBroadcast, ShouldDispatchAfterCommit, ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use SerializesModels;

    public function __construct(public Place $place)
    {
        $this->onQueue('broadcasts');
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('places.map'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'place.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->place->id,
            'name' => $this->place->name,
            'latitude' => $this->place->latitude,
            'longitude' => $this->place->longitude,
        ];
    }
}
