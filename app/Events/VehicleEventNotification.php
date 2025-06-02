<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class VehicleEventNotification implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $eventData;
    public $userId;

    public function __construct($eventData, $userId)
    {
        $this->eventData = $eventData;
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
        // Diffuser sur le canal privé de l'utilisateur concerné
        return new PrivateChannel('user.' . $this->userId);
    }

    public function broadcastAs()
    {
        // Nom de l'événement côté client
        return 'vehicle.event';
    }
}
