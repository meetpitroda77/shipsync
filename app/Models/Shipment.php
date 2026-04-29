<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipment extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'tracking_id',
        'sender_name',
        'sender_phone',
        'sender_address_id',
        'receiver_name',
        'receiver_phone',
        'receiver_address_id',
        'package_type',
        'notes',
        'delivery_method',
        'status',
        'estimated_delivery_date',
        'actual_delivery_date',
        'courier_company',
        'shipping_mode',
        'created_by',
        'assigned_to'
    ];



    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function senderAddress()
    {
        return $this->belongsTo(Addresses::class, 'sender_address_id');
    }

    public function receiverAddress()
    {
        return $this->belongsTo(Addresses::class, 'receiver_address_id');
    }

    public function logs()
    {
        return $this->hasMany(ShipmentLog::class);
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    public function images()
    {
        return $this->hasMany(ShipmentImage::class);
    }
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
