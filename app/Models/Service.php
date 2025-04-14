<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    public $timestamps = true; // A migráció miatt true-ra kell állítani

    protected $fillable = ['service', 'price'];

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_service');
    }
}
