<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiscPages extends Model
{
    protected $table = 'misc_pages';

    protected $fillable = [
        "about",
        "privacy_terms",
        "faq",
        "contact_us"
    ];

    protected $casts = [
        'faq' => 'array',
    ];
}
