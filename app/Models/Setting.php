<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        "delivery_coverage",
        "company_share",
        "cost_per_km",
        "phone",
        "address",
    ];
}
