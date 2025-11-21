<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderNegotiation extends Model
{
    protected $fillable = ['place_order_id', 'customer_id', 'delivery_id','proposed_price', 'status'];

    public function placeOrder()
    {
        return $this->belongsTo(PlaceOrder::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function delivery()
    {
        return $this->belongsTo(Customer::class, 'delivery_id');
    }
}
