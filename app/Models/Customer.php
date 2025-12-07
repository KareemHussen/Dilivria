<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        "first_name",
        "last_name",
        "username",
        "full_name",
        "phone",
        "email",
        "pin",
        "delivery",
        "lng",
        "lat",
        "delivery_rate",
        "customer_rate",
        "fcm_token",
        "delivery_status",
        "picture",
        "national_id",
        "id_front",
        "id_back",
        "selfie",
        "last_otp",
        "last_otp_expire",
        "verified"
    ];

    protected $hidden = [
        'last_otp',
        'last_otp_expire'
    ];

    protected $casts = [
        'last_otp'=> 'hashed'
    ];
    
    protected $appends = [
        'picture_url'
    ];
    
    /**
     * Get the profile picture URL
     *
     * @return string|null
     */
    public function getPictureUrlAttribute()
    {
        if ($this->picture) {
            // Get the base domain from config or use localhost as fallback
            $baseDomain = config('app.url', 'http://localhost');
            
            // Remove any trailing slash from base domain
            $baseDomain = rtrim($baseDomain, '/');
            
            // Format with specific URL structure (remove /app/public/)
            if (str_starts_with($this->picture, '/storage/')) {
                // If path already starts with /storage/, use it directly
                return $baseDomain . $this->picture;
            } else {
                // Otherwise prefix with /storage/
                return $baseDomain . '/storage/' . $this->picture;
            }
        }
        
        return null;
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function placeOrders()
    {
        return $this->hasMany(PlaceOrder::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, "delivery_id");
    }

    public function notifications()
    {
        return $this->hasMany(AppNotification::class);
    }

    // Access transactions where the customer is the sender
    public function sentTransactions()
    {
        return $this->hasManyThrough(
            Transaction::class, // Final model (transactions)
            Wallet::class,      // Intermediate model (wallets)
            'customer_id',      // Foreign key on wallets (to customer table)
            'sender',           // Foreign key on transactions (to wallets table)
            'id',               // Local key on customers table
            'id'                // Local key on wallets table
        );
    }

    // Access transactions where the customer is the receiver
    public function receivedTransactions()
    {
        return $this->hasManyThrough(
            Transaction::class, // Final model (transactions)
            Wallet::class,      // Intermediate model (wallets)
            'customer_id',      // Foreign key on wallets (to customer table)
            'receiver',         // Foreign key on transactions (to wallets table)
            'id',               // Local key on customers table
            'id'                // Local key on wallets table
        );
    }
    
    public function walletWithdrawals()
    {
        return $this->hasManyThrough(
            WalletWithdrawal::class, // Final model (wallet_withdrawals)
            Wallet::class,          // Intermediate model (wallets)
            'customer_id',          // Foreign key on wallets (to customer table)
            'wallet_id',            // Foreign key on withdrawals (to wallets table)
            'id',                   // Local key on customers table
            'id'                    // Local key on wallets table
        );
    }
}
