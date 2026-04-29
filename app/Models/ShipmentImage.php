<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentImage extends Model
{
        use HasFactory;

    protected $fillable = [
        'shipment_id',
        'image_path',
        'uploaded_by',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
