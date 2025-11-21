<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: [
            __DIR__.'/../routes/customerAPI.php',
            __DIR__.'/../routes/deliveryAPI.php',
            __DIR__.'/../routes/globalAPI.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo('/unauthorized');
        $middleware->append(SetLocale::class);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
