<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        "place_order_id",
        "delivery_id",
        "price",
        "rate_delivery",
        "rate_customer",
        "status",
        "delivery_time"
    ];

    public function placeOrder(){
        return $this->belongsTo(PlaceOrder::class, 'place_order_id');
    }

    public function delivery(){
        return $this->belongsTo(Customer::class, 'delivery_id');
    }

    public function cancel(){
        return $this->hasOne(OrderCancel::class);
    }
    public function getDeliveryTimeAttribute($value) {
        if ($value) {
            return date('Y-m-d h:i A', strtotime($value)); // Formats the date as YYYY-MM-DD and time as HH:MM AM/PM
        }
        else if (str_starts_with($this->status, 'cancelled_')) {
            return __('Cancelled');
        }
         else {
            return __('In Progress');
        }
    }


    public function messages(){
        return $this->hasMany(Message::class);
    }
}
