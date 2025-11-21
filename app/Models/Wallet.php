<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        "customer_id",
        "balance"
    ];

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function recharges(){
        return $this->hasMany(WalletRecharge::class, 'wallet_id');
    }

    public function sender(){
        return $this->hasMany(Transaction::class, 'sender');
    }

    public function receiver(){
        return $this->hasMany(Transaction::class, 'receiver');
    }

    public function withdrawals(){
        return $this->hasMany(WalletWithdrawal::class, 'wallet_id');
    }
}
