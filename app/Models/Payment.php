<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;


    protected $fillable = [
        'shipment_id',
        'tracking_id',
        'user_id',
        'subtotal',
        'tax',
        'insurance',
        'amount',
        'payment_method',
        'transaction_id',
        'payment_status',
        'paid_at',

    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
