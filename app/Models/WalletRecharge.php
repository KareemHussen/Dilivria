<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletRecharge extends Model
{
    protected $fillable = [
        "wallet_id",
        "photo",
        "payment_type", // 'vodafone_cash' or 'instapay'
        "payment_number", // phone number for Vodafone Cash or address for Instapay
        "status",
        "reject_reason"
    ];

    public function wallet(){
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
}
