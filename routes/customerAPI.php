<?php

use App\Http\Controllers\Customer\ActivitiesController;
use App\Http\Controllers\Customer\FavoriteController;
use App\Http\Controllers\Customer\PlaceOrderController;
use App\Http\Controllers\Customer\NegotiateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\CancelOrderController;
use App\Http\Controllers\Customer\ChangeModeController;
use App\Http\Controllers\Customer\MessageController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\ProfileController;

Route::prefix('customer')->group(function () {
    //Auth
    Route::post('/register', [AuthController::class, 'register']);

    Route::get("/cost-per-km", [PlaceOrderController::class, 'getCostPerKM']);

    Route::middleware('auth:sanctum,customer')->group(function () {

    //Favorite
    Route::post("/favorite/add", [FavoriteController::class, 'add']);
    Route::post("/favorite/edit", [FavoriteController::class, 'edit']);
    Route::post("/favorite/delete", [FavoriteController::class, 'delete']);
    Route::get("/favorite/get", [FavoriteController::class, 'get']);
    Route::get("/favorite/get-all", [FavoriteController::class, 'getAll']);

    //Place Order
    Route::post('/place-order', [PlaceOrderController::class, 'placeOrder']);
    Route::get('/place-order/active', [PlaceOrderController::class, 'getActive']);
    Route::post('/place-order/active-cancel', [PlaceOrderController::class, 'cancel']);

    //Order
    Route::get('/order/active', [OrderController::class, 'getActive'])->middleware("auth:sanctum,customer");
    Route::get('/order/get/last-completed', [OrderController::class, 'getLast']);

    //Negotiate
    Route::post('/order/propose', [NegotiateController::class, 'proposePrice']);
    Route::post('/order/respond-propose', [NegotiateController::class, 'respondToProposal']);
    Route::get('/order/get-proposals', [NegotiateController::class, 'getProposals']);
    Route::get('/order/get-proposal/single', [NegotiateController::class, 'get']);

    //Cancel Order
    Route::post("/order/cancel-request", [CancelOrderController::class, "sendRequest"]);
    Route::get('/order/cancel-requests/get', [CancelOrderController::class, 'getRequests']);
    Route::post('/order/cancel/respond', [CancelOrderController::class, 'respond']);

    //Messages
    Route::post('/order/messages/send', [MessageController::class, 'send']);
    Route::get('/order/messages/get', [MessageController::class, 'get']);

    //Rate Order
    Route::post('/order/rate', [OrderController::class, 'rate']);
    
    //Profile
    Route::get('/profile/get', [ProfileController::class, 'get']);
    Route::post('/profile/edit', [ProfileController::class, 'edit']);

    //Activities
    Route::get('/activities/completed-all', [ActivitiesController::class, 'getCompletedAll']);
    Route::get('/activities/completed-paginate', [ActivitiesController::class, 'getCompletedPaginate']);
    Route::get('/activities/cancelled-all', [ActivitiesController::class, 'getCancelledAll']);
    Route::get('/activities/cancelled-paginate', [ActivitiesController::class, 'getCancelledPaginate']);

    //Change Mode
    Route::post('/change/delivery-docs', [ChangeModeController::class, 'setDocs']);
    });
});
