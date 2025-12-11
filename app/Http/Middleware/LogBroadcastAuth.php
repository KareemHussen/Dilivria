<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogBroadcastAuth
{
    /**
     * Handle an incoming request - logs all details for debugging 403 errors
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::channel('daily')->info('=== BROADCAST AUTH DEBUG START ===');
        
        // Log request details
        Log::channel('daily')->info('Request Path: ' . $request->path());
        Log::channel('daily')->info('Request Method: ' . $request->method());
        Log::channel('daily')->info('Request URL: ' . $request->fullUrl());
        
        // Log all headers
        Log::channel('daily')->info('Headers:', $request->headers->all());
        
        // Log Authorization header specifically
        $authHeader = $request->header('Authorization');
        Log::channel('daily')->info('Authorization Header: ' . ($authHeader ? substr($authHeader, 0, 30) . '...' : 'NOT SET'));
        
        // Log bearer token
        $bearerToken = $request->bearerToken();
        Log::channel('daily')->info('Bearer Token: ' . ($bearerToken ? substr($bearerToken, 0, 20) . '...' : 'NOT SET'));
        
        // Log request body
        Log::channel('daily')->info('Request Body:', $request->all());
        
        // Check if user is authenticated before middleware
        $user = auth('sanctum')->user();
        Log::channel('daily')->info('Auth User (sanctum): ' . ($user ? 'ID: ' . $user->id . ', Type: ' . get_class($user) : 'NULL'));
        
        $customerUser = auth('customer')->user();
        Log::channel('daily')->info('Auth User (customer): ' . ($customerUser ? 'ID: ' . $customerUser->id : 'NULL'));
        
        // Try to resolve the user from token
        if ($bearerToken) {
            $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($bearerToken);
            if ($tokenModel) {
                Log::channel('daily')->info('Token found in DB - Owner: ' . get_class($tokenModel->tokenable) . ' ID: ' . $tokenModel->tokenable_id);
            } else {
                Log::channel('daily')->info('Token NOT found in database');
            }
        }
        
        // Process the request
        $response = $next($request);
        
        // Log response status
        Log::channel('daily')->info('Response Status: ' . $response->getStatusCode());
        
        if ($response->getStatusCode() === 403) {
            Log::channel('daily')->warning('403 Forbidden returned!');
            Log::channel('daily')->info('Response Content: ' . $response->getContent());
        }
        
        Log::channel('daily')->info('=== BROADCAST AUTH DEBUG END ===');
        
        return $response;
    }
}
