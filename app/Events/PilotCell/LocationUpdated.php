<?php

namespace App\Events\PilotCell;

use App\Models\PilotCell\PcTrip;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $trip;
    public $location;

    public function __construct(PcTrip $trip, array $location)
    {
        $this->trip = $trip;
        $this->location = $location;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('pilotcell.trip.' . $this->trip->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'location.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'lat' => $this->location['lat'],
            'lng' => $this->location['lng'],
            'accuracy' => $this->location['accuracy'],
            'speed' => $this->location['speed'] ?? 0,
            'heading' => $this->location['heading'] ?? 0,
            'recorded_at' => $this->location['recorded_at'],
            'vehicle_id' => $this->trip->vehicle_id,
            'driver_id' => $this->trip->driver_id,
        ];
    }
}
