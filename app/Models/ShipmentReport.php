<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentReport extends Model
{
    
    protected $fillable = [
        'report_date',
        'total_shipments',
        'total_revenue',
        'pending_assigned',
        'pending_payment',
        'assigned',
        'picked_up',
        'in_transit',
        'out_for_delivery',
        'delivered',
        'failed_delivery',
        'delayed',
        'canceled',
    ];
}