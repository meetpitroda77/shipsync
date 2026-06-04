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

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Str;

class ShipmentCreatedNotification extends Notification
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
            'id' => (string) Str::uuid(),

            'shipment_id' => $this->shipment->id,
            'tracking_id' => $this->shipment->tracking_id,
            'message' => "New shipment created for: Shipment #{$this->shipment->tracking_id}",
            'time' => now()->toISOString(),
            'type' => 'shipment_created',
        ]);
    }

    public function toDatabase($notifiable)
    {
        return [
            'shipment_id' => $this->shipment->id,
            'tracking_id' => $this->shipment->tracking_id,
            'message' => "New shipment created for: Shipment #{$this->shipment->tracking_id}",
            'type' => 'shipment_created'

        ];
    }
}
