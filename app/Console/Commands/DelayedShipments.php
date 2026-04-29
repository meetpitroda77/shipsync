<?php

namespace App\Console\Commands;

use App\Models\Shipment;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\ShipmentStatusNotification;
class DelayedShipments extends Command implements ShouldQueue
{
       protected $signature = 'shipments:update-delayed';

    
    protected $description = 'Update shipments to delayed';

 
    public function handle()
    {
        $today = Carbon::today();

        $shipments = Shipment::where('status', '!=', 'delivered')
            ->whereDate('estimated_delivery_date', '<', $today)
            ->get();

        foreach ($shipments as $shipment) {

            $logExists = $shipment->logs()
                ->where('status', 'delayed')
                ->exists();

            $isFirstTimeDelayed = $shipment->status !== 'delayed';

            if ($isFirstTimeDelayed) {
                $shipment->status = 'delayed';
                $shipment->estimated_delivery_date = Carbon::parse($shipment->estimated_delivery_date)->addDays(3);
                $shipment->save();
            }

            if (!$logExists) {
                $shipment->logs()->create([
                    'status' => 'delayed',
                    'location' => null,
                    'description' => 'Shipment delayed due to exceeding estimated delivery date',
                ]);
            }

            if ($isFirstTimeDelayed) {
                $creator = $shipment->creator;
                if ($creator) {
                    $creator->notify(new ShipmentStatusNotification($shipment));
                }
            }
        }
    }
}
