<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PopularPlace extends Model
{
    protected $fillable = [
        "title",
        "description",
        "images",
        "address",
        "lng",
        "lat"
    ];

    protected $casts = [
        'images' => 'array', // Cast it as an array to simplify encoding/decoding.
    ];
    
    protected $appends = [
        'image_urls'
    ];
    
    /**
     * Get the URLs for all images
     *
     * @return array
     */
    public function getImageUrlsAttribute()
    {
        if (!$this->images) {
            return [];
        }
        
        $urls = [];
        foreach ($this->images as $image) {
            // Check if the path already contains storage/ at the beginning
            if (is_string($image)) {
                if (str_starts_with($image, '/storage/')) {
                    $urls[] = asset(substr($image, 1)); // Remove the leading slash
                } else {
                    $urls[] = asset('storage/' . $image);
                }
            }
        }
        
        return $urls;
    }
}
