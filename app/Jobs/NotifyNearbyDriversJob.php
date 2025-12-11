<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\PlaceOrder;
use App\Models\Setting;
use App\Traits\FcmNotificationTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyNearbyDriversJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, FcmNotificationTrait;

    protected $placeOrder;

    /**
     * Create a new job instance.
     */
    public function __construct(PlaceOrder $placeOrder)
    {
        $this->placeOrder = $placeOrder;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            
            if (!$this->placeOrder) {
                return;
            }

            $setting = Setting::first();
            if (!$setting) {
                return;
            }
            
            $distanceLimit = $setting->delivery_coverage;
            $earthRadius = 6371;


            
            $nearbyDrivers = Customer::where('delivery', true)
                ->whereNotNull('fcm_token')
                ->whereNotNull('lat')
                ->whereNotNull('lng')
                ->selectRaw("*, ($earthRadius * ACOS(
                    COS(RADIANS(?)) * COS(RADIANS(lat)) *
                    COS(RADIANS(lng) - RADIANS(?)) +
                    SIN(RADIANS(?)) * SIN(RADIANS(lat))
                )) AS distance", [$this->placeOrder->lat_from, $this->placeOrder->lng_from, $this->placeOrder->lat_from])
                ->having('distance', '<=', $distanceLimit)
                ->get();

            foreach ($nearbyDrivers as $driver) {
                $this->sendNotification(
                    $driver->fcm_token,
                    "طلب جديد بالقرب منك!",
                    "يوجد طلب جديد على بعد " . round($driver->distance, 1) . " كم",
                    $driver->id
                );
            }

            Log::info('Notified ' . $nearbyDrivers->count() . ' nearby drivers for order #' . $this->placeOrder->id);
            
        } catch (\Exception $e) {
            Log::error('Error notifying nearby drivers: ' . $e->getMessage());
        }
    }
}
