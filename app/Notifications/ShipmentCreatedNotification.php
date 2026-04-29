<?php

// namespace App\Notifications;

// use Illuminate\Bus\Queueable;
// use Illuminate\Notifications\Notification;
// use Illuminate\Contracts\Queue\ShouldQueue;

// class ShipmentCreatedNotification extends Notification implements ShouldQueue
// {
//     use Queueable;

//     protected $shipment;

//     public function __construct($shipment)
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
//             'message' => "New shipment created:  #{$this->shipment->tracking_id}",
//         ];
//     }

// }



namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ShipmentCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $shipment;

    public function __construct($shipment)
    {
        $this->shipment = $shipment;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }



    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'shipment_id' => $this->shipment->id,
            'tracking_id' => $this->shipment->tracking_id,
            'message' => "New shipment created: #{$this->shipment->tracking_id}",
        ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'shipment_id' => $this->shipment->id,
            'tracking_id' => $this->shipment->tracking_id,
            'message' => "New shipment created: #{$this->shipment->tracking_id}",
        ];
    }
}