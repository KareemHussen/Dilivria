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

    protected $placeOrderId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $placeOrderId)
    {
        $this->placeOrderId = $placeOrderId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $placeOrder = PlaceOrder::find($this->placeOrderId);
            
            if (!$placeOrder || $placeOrder->status !== 'pending') {
                return;
            }

            $setting = Setting::first();
            if (!$setting) {
                return;
            }
            
            $distanceLimit = $setting->delivery_coverage;
            $earthRadius = 6371;

            $nearbyDrivers = Customer::where('delivery', true)
                ->where('verified', true)
                ->where('delivery_status', 'online')
                ->where('id', '!=', $placeOrder->customer_id)
                ->whereNotNull('fcm_token')
                ->whereNotNull('lat')
                ->whereNotNull('lng')
                ->selectRaw("*, ($earthRadius * ACOS(
                    COS(RADIANS(?)) * COS(RADIANS(lat)) *
                    COS(RADIANS(lng) - RADIANS(?)) +
                    SIN(RADIANS(?)) * SIN(RADIANS(lat))
                )) AS distance", [$placeOrder->lat_from, $placeOrder->lng_from, $placeOrder->lat_from])
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

            Log::info('Notified ' . $nearbyDrivers->count() . ' nearby drivers for order #' . $this->placeOrderId);
            
        } catch (\Exception $e) {
            Log::error('Error notifying nearby drivers: ' . $e->getMessage());
        }
    }
}
