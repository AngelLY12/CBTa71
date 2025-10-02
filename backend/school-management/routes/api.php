<?php

use App\Http\Controllers\Students\DashboardController;
use App\Http\Controllers\Students\CardsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Students\WebhookController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function (){
    Route::prefix('dashboard')->middleware('role:student')->group(function (){
        Route::get('/',[DashboardController::class,'index']);
        Route::get('/pending',[DashboardController::class,'pending']);
        Route::get('/paid',[DashboardController::class,'paid']);
        Route::get('/overdue',[DashboardController::class,'overdue']);
        Route::get('/history',[DashboardController::class,'history']);

    });
    Route::prefix('cards')->middleware('role:student')->group(function(){
        Route::get('/',[CardsController::class,'index']);
        Route::post('/',[CardsController::class,'store']);
        Route::post('/setup-intent',[CardsController::class,'setupIntent']);
        Route::delete('/{paymentMethodId}',[CardsController::class,'destroy']);
    });
    Route::post('/stripe/webhook', [WebhookController::class, 'handle']);

});


