<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletWithdrawal extends Model
{
    protected $fillable = [
        "wallet_id",
        "amount",
        "payment_type", // 'vodafone_cash' or 'instapay'
        "payment_number", // phone number for Vodafone Cash or address for Instapay
        "status",
        "reject_reason"
    ];

    public function wallet(){
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
}
