<?php

namespace App\Events;

use App\Models\Shipment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ShipmentStatusUpdated implements ShouldBroadcastNow
{
    use SerializesModels;

    public $shipment;

    public function __construct(Shipment $shipment)
    {
        $this->shipment=$shipment;
    }

    public function broadcastOn()
    {
        return new Channel('shipment.'.$this->shipment->tracking_id);
    }

    public function broadcastWith()
    {
        return [

            'tracking_id'=>$this->shipment->tracking_id,

            'status'=>$this->shipment->status,

        ];
    }


    public function broadcastAs()
    {

        return 'ShipmentStatusUpdated';

    }

}