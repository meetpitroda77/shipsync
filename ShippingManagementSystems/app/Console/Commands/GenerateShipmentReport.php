<?php

namespace App\Console\Commands;

use App\Models\ShipmentLog;
use Illuminate\Console\Command;
use App\Models\Shipment;
use App\Models\Payment;
use App\Models\ShipmentReport;
use Carbon\Carbon;

class GenerateShipmentReport extends Command
{
    protected $signature = 'shipments:daily-report';
    protected $description = 'Generate daily shipment report and store in DB';

    public function handle()
    {
        $today = Carbon::today();

        if (ShipmentReport::where('report_date', $today)->exists()) {
            return Command::SUCCESS;
        }

        $total = Shipment::whereDate('created_at', $today)->count();

        $revenue = Payment::whereDate('paid_at', $today)->sum('amount');

        $statuses = [
            'pending_assigned',
            'pending_payment',
            'assigned',
            'picked_up',
            'in_transit',
            'out_for_delivery',
            'delivered',
            'failed_delivery',
            'delayed',
            'canceled'
        ];

        $counts = ShipmentLog::whereDate('created_at', $today)
            ->whereIn('status', $statuses)
            ->selectRaw('status, COUNT(DISTINCT shipment_id) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $data =array_merge(
            array_fill_keys($statuses, 0),
            $counts
        ); 

        ShipmentReport::create(array_merge([
            'report_date' => $today,
            'total_shipments' => $total,
            'total_revenue' => $revenue,
        ], $data));


        return Command::SUCCESS;
    }
}