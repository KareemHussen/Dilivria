<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        "sender",
        "receiver",
        "amount",
        "type",
        "status"
    ];

    public function senderWallet(){
        return $this->belongsTo(Wallet::class, 'sender');
    }

    public function receiverWallet(){
        return $this->belongsTo(Wallet::class, 'receiver');
    }
}
