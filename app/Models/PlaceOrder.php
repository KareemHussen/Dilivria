<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaceOrder extends Model
{
    protected $fillable = [
        "customer_id",
        "address_from",
        "lng_from",
        "lat_from",
        "address_to",
        "lng_to",
        "lat_to",
        "price",
        "details",
        "payment_method",
        "paid",
        "status"
    ];

    public function customer(){
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function order(){
        return $this->hasOne(Order::class, "place_order_id");
    }

    public function negotiations()
    {
        return $this->hasMany(OrderNegotiation::class);
    }
}
