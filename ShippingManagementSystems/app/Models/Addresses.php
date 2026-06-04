<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addresses extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'user_id',
        'recipient_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipient()
    {
        return $this->belongsTo(Recipient::class);
    }

}
