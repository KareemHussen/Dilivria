<?php

namespace App\Traits;

trait CalculateDistanceTrait
{
    public function calcDistance($lat1, $lng1, $lat2, $lng2, $earthRadius = 6371){
        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($lng1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($lng2);
    
        $dlat = $lat2 - $lat1;
        $dlng = $lng2 - $lng1;
    
        $a = sin($dlat/2) ** 2 + cos($lat1) * cos($lat2) * sin($dlng/2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
        return $earthRadius * $c;
    }
}