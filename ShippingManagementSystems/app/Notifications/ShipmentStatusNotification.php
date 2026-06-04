<?php
namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ShipmentStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $trackingId;
    protected $status;
    protected $shipmentId;
    protected $receiverName;
    protected $role;

    public function __construct($shipment, $status = null)
    {
        $this->trackingId = $shipment->tracking_id;

        $this->status = $status ?? $shipment->status;

        $this->shipmentId = $shipment->id;

        $this->receiverName = $shipment->receiver_name;

        $user = User::find($shipment->created_by);

        $this->role = $user ? $user->role : 'customer';
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $shipmentLink = config('app.frontend_url', 'http://localhost:5173')
            . '/customer/shipments/'
            . $this->shipmentId;

        return (new MailMessage)
            ->subject("Shipment #{$this->trackingId} Status Update")
            ->markdown('emails.status_update', [
                'tracking_id'=>$this->trackingId,
                'status'=>$this->status,
                'receiver_name'=>$this->receiverName,
                'link'=>$shipmentLink
            ]);
    }
}