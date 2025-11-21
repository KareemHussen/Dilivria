<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeliveryStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        switch($user->delivery_status){
            case "waiting":
                return response()->json([
                    'message' => 'Access denied. Admin is reviewing your docs.'
                ], 403);
            case "hold":
                return response()->json([
                    'message' => 'Access denied. You got holded by admin, contact support.'
                ], 403);
            case "block":
                return response()->json([
                    'message' => 'Access denied. You got blocked by admin, contact support.'
                ], 403);
        }
        return $next($request);
    }
}
