<?php

use App\Http\Controllers\Global\AuthController;
use App\Http\Controllers\Global\MiscController;
use App\Http\Controllers\Global\NegotiateController;
use App\Http\Controllers\Global\NotificationController;
use App\Http\Controllers\Global\PopularPlaceController;
use App\Http\Controllers\Global\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//Auth
Route::get('/ask-email-verification-code', [AuthController::class, "askEmailCode"])->middleware('auth:sanctum,customer');
Route::post('/verify-email', [AuthController::class, "verifyEmail"])->middleware('auth:sanctum,customer');
Route::post('/change-password', [AuthController::class, "changePassword"])->middleware('auth:sanctum,customer');
Route::get('/forgot-password', [AuthController::class, "sendForgetPassword"]);
Route::post('/forgot-password-check-code', [AuthController::class, "forgetPasswordCheckCode"]);
Route::post('/forgot-password-set', [AuthController::class,'forgetPassword']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-login-otp', [AuthController::class, 'verifyLoginOtp']);
Route::post('/ask-otp', [AuthController::class, 'askOtp']);
Route::post('/logout', [AuthController::class, "logout"])->middleware('auth:sanctum,customer');
Route::post('/update-fcm', [AuthController::class, 'updateFCM'])->middleware('auth:sanctum,customer');

//Wallet 
Route::get('/wallet/get', [WalletController::class, 'get'])->middleware('auth:sanctum,customer');
Route::get('/wallet/get-recharges/all', [WalletController::class, 'getRechargesAll'])->middleware('auth:sanctum,customer');
Route::get('/wallet/get-recharges/paginate', [WalletController::class, 'getRechargesPaginate'])->middleware('auth:sanctum,customer');
Route::get('/wallet/get-withdrawals/all', [WalletController::class, 'getWithdrawalsAll'])->middleware('auth:sanctum,customer');
Route::get('/wallet/get-withdrawals/paginate', [WalletController::class, 'getWithdrawalsPaginate'])->middleware('auth:sanctum,customer');
Route::get('/wallet/get-sent/all', [WalletController::class, 'getSenderAll'])->middleware('auth:sanctum,customer');
Route::get('/wallet/get-sent/paginate', [WalletController::class, 'getSenderPaginate'])->middleware('auth:sanctum,customer');
Route::get('/wallet/get-received/all', [WalletController::class, 'getReceiverAll'])->middleware('auth:sanctum,customer');
Route::get('/wallet/get-received/paginate', [WalletController::class, 'getReceiverPaginate'])->middleware('auth:sanctum,customer');
Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->middleware('auth:sanctum,customer');
Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->middleware('auth:sanctum,customer');
Route::get('/wallet/transfer/get-customer', [WalletController::class, 'getCustomerByPhone']);
Route::post('/wallet/transfer/send', [WalletController::class, 'transfer'])->middleware('auth:sanctum,customer');
Route::post("/wallet/set-pin", [WalletController::class, 'setPIN'])->middleware('auth:sanctum,customer');
Route::get("/wallet/send-code", [WalletController::class, 'sendCode'])->middleware('auth:sanctum,customer');
Route::post("/wallet/check-code", [WalletController::class, 'checkCode'])->middleware('auth:sanctum,customer');


//MISC pages
Route::get('/pages/about', [MiscController::class, 'about']);
Route::get('/pages/privacy-and-terms', [MiscController::class, 'privacyTerms']);
Route::get('/pages/faq', [MiscController::class, 'faq']);
Route::get('/pages/contact-us', [MiscController::class, 'contact']);
Route::get('/payment-info', [MiscController::class, 'paymentInfo']);

//Popular Places
Route::get('/popular-places/all', [PopularPlaceController::class, 'getAll']);
Route::get('/popular-places/paginate', [PopularPlaceController::class, 'getPaginate']);
Route::get('/popular-places/get', [PopularPlaceController::class, 'get']);

//Notifications
Route::middleware('auth:sanctum,customer')->group(function (){
    Route::prefix('notifications')->group(function(){
        Route::get('/', [NotificationController::class, 'index']);
        Route::put('/{notification}/mark-as-read', [NotificationController::class, 'markAsRead']);
        Route::put('/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{notification}', [NotificationController::class, 'destroy']);
        Route::delete('/', [NotificationController::class, 'destroyAll']);
    });
});