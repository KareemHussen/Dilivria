<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCancel extends Model
{
    protected $fillable = [
        "order_id",
        "delivery_id",
        "customer_id",
        "reason",
        "status"
    ];

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function customer(){
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function delivery(){
        return $this->belongsTo(Customer::class, 'delivery_id');
    }

    public function getRequestedByAttribute()
    {
        if ($this->customer_id) {
            return $this->customer; // Lazy loads the customer relation
        }

        if ($this->delivery_id) {
            return $this->delivery; // Lazy loads the delivery relation
        }


        return null;
    }
    public function getRequestedByTypeAttribute()
    {
        if ($this->customer_id) {
            return 'Customer'; // Lazy loads the customer relation
        }

        if ($this->delivery_id) {
            return 'Delivary'; // Lazy loads the delivery relation
        }


        return null;
    }

}
