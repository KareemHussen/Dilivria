<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class OTPRateLimiter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $limiterName  The name of the rate limiter to use
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $limiterName)
    {
        $key = $limiterName . ':' . $request->ip();
        
        // Set limits based on the limiter name
        $maxAttempts = 3; // Default
        $decayMinutes = 1; // Default
        
        switch ($limiterName) {
            case 'login':
                $maxAttempts = 5;   // 5 login attempts
                $decayMinutes = 15; // within 15 minutes
                break;
            case 'register':
                $maxAttempts = 3;   // 3 registration attempts
                $decayMinutes = 60; // within 60 minutes
                break;
            case 'ask_otp':
                $maxAttempts = 3;   // 3 OTP requests
                $decayMinutes = 5;  // within 5 minutes
                break;
        }
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);
            
            return response()->json([
                'success' => false,
                'message' => '',
                'errors' => [
                    __('auth.throttle', [
                        'seconds' => $seconds,
                        'minutes' => $minutes
                    ])
                ],
                'data' => [],
                'hints' => [
                    "You've made too many requests. Please try again in {$minutes} " . 
                    Str::plural('minute', $minutes) . "."
                ]
            ], 429);
        }

        // Add a hit to the rate limiter
        RateLimiter::hit($key, $decayMinutes * 60);
        
        return $next($request);
    }
}
