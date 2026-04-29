<?php

// namespace App\Notifications;

// use App\Models\Shipment;
// use Illuminate\Bus\Queueable;
// use Illuminate\Notifications\Notification;
// use Illuminate\Contracts\Queue\ShouldQueue;
// class ShipmentAssignedNotification extends Notification implements ShouldQueue
// {
//     use Queueable;

//     protected $shipment;

//     public function __construct(Shipment $shipment)
//     {
//         $this->shipment = $shipment;
//     }

//     public function via($notifiable)
//     {
//         return ['database']; 
//     }

//     public function toDatabase($notifiable)
//     {
//         return [
//             'shipment_id' => $this->shipment->id,
//             'tracking_id' => $this->shipment->tracking_id,
//             'message' => "New shipment assigned to you: Shipment #{$this->shipment->tracking_id}",
//         ];
//     }
// }



namespace App\Notifications;

use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\PrivateChannel;

class ShipmentAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $shipment;

    public function __construct(Shipment $shipment)
    {
        $this->shipment = $shipment;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->shipment->assigned_to),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'shipment_id' => $this->shipment->id,
            'tracking_id' => $this->shipment->tracking_id,
            'message' => "New shipment assigned to you: Shipment #{$this->shipment->tracking_id}",
            'time' => now()->format('Y-m-d\TH:i:s\Z'),
        ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'shipment_id' => $this->shipment->id,
            'tracking_id' => $this->shipment->tracking_id,
            'message' => "New shipment assigned to you: Shipment #{$this->shipment->tracking_id}",
        ];
    }
}