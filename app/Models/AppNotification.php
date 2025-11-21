<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $fillable = [
        'customer_id',
        'title',
        'body',
        'opened'
    ];

    public function customer(){
        return $this->belongsTo(Customer::class);
    }
}
