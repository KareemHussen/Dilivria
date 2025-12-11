<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopularPlaceSuggestion extends Model
{
    protected $fillable = [
        'customer_id',
        'title',
        'description',
        'images',
        'address',
        'lng',
        'lat',
        'type',
        'accepted',
    ];

    protected $casts = [
        'images' => 'array',
        'accepted' => 'boolean',
    ];

    protected $appends = [
        'image_urls',
        'status_label',
    ];

    /**
     * Get the user who submitted the suggestion
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the URLs for all images
     */
    public function getImageUrlsAttribute(): array
    {
        if (!$this->images) {
            return [];
        }

        $urls = [];
        foreach ($this->images as $image) {
            if (is_string($image)) {
                if (str_starts_with($image, '/storage/')) {
                    $urls[] = asset(substr($image, 1));
                } else {
                    $urls[] = asset('storage/' . $image);
                }
            }
        }

        return $urls;
    }

    /**
     * Get human-readable status label
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->accepted === null) {
            return 'Pending';
        }

        return $this->accepted ? 'Accepted' : 'Declined';
    }

    /**
     * Scope for pending suggestions
     */
    public function scopePending($query)
    {
        return $query->whereNull('accepted');
    }

    /**
     * Scope for accepted suggestions
     */
    public function scopeAccepted($query)
    {
        return $query->where('accepted', true);
    }

    /**
     * Scope for declined suggestions
     */
    public function scopeDeclined($query)
    {
        return $query->where('accepted', false);
    }
}
