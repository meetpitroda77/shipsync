<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'invoice_number',
        'total_amount',
        'due_date',
        'pdf_path',
        'status'
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
