<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/unauthorized', function () {
    return response()->json(
        [
            "status" => false,
            "message" => "unauthenticated",
            "errors" => ["Your are not authenticated"],
            "data" => [],
            "notes" => []
        ]
        , 401);
    });


// Broadcast::routes moved to globalAPI.php to avoid CSRF issues with API clients
