<?php

use App\Http\Controllers\Staff\ConceptsController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\DebtsController;
use App\Http\Controllers\Staff\PaymentsController;
use App\Http\Controllers\Staff\StudentsController;
use App\Http\Controllers\Students\DashboardController;
use App\Http\Controllers\Students\CardsController;
use App\Http\Controllers\Students\PaymentHistoryController;
use App\Http\Controllers\Students\PendingPaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Students\WebhookController;
use App\Models\PaymentConcept;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/test', function() {
    return PaymentConcept::all();
});

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function (){
    Route::prefix('dashboard')->middleware('role:student')->group(function (){
        Route::get('/data',[DashboardController::class,'index']);
        Route::get('/pending',[DashboardController::class,'pending']);
        Route::get('/paid',[DashboardController::class,'paid']);
        Route::get('/overdue',[DashboardController::class,'overdue']);
        Route::get('/history',[DashboardController::class,'history']);

    });
    Route::prefix('cards')->middleware('role:student')->group(function(){
        Route::get('/',[CardsController::class,'index']);
        Route::post('/',[CardsController::class,'store']);
        Route::get('/save', [CardsController::class, 'save']);
        Route::delete('/{paymentMethodId}',[CardsController::class,'destroy']);
    });
    Route::prefix('history')->middleware('role:student')->group(function(){
        Route::get('/',[PaymentHistoryController::class,'index']);
    });
    Route::prefix('pending-payment')->middleware('role:student')->group(function(){
        Route::get('/',[PendingPaymentController::class,'index']);
        Route::post('/',[PendingPaymentController::class,'store']);
    });
    Route::post('/stripe/webhook', [WebhookController::class, 'handle']);

    Route::prefix('dashboard-staff')->middleware('role:financial staff')->group(function(){
        Route::get('/data',[StaffDashboardController::class,'getData']);
        Route::get('/pending',[StaffDashboardController::class,'pendingPayments']);
        Route::get('/students',[StaffDashboardController::class,'allStudents']);
        Route::get('/payments',[StaffDashboardController::class,'paymentsMade']);
        Route::get('/concepts',[StaffDashboardController::class,'allConcepts']);
    });

     Route::prefix('concepts')->middleware('role:financial staff')->group(function(){
        Route::get('/', [ConceptsController::class, 'index']);
        Route::post('/', [ConceptsController::class, 'store']);
        Route::put('/{concept}', [ConceptsController::class, 'update']);
        Route::patch('/{concept}', [ConceptsController::class, 'update']);
        Route::post('/{concept}/finalize', [ConceptsController::class, 'finalize']);
    });

    Route::prefix('debts')->middleware('role:financial staff')->group(function(){
        Route::get('/', [DebtsController::class, 'index']);
        Route::post('/validate', [DebtsController::class, 'validatePayment']);
    });

    Route::prefix('payments')->middleware('role:financial staff')->group(function(){
        Route::get('/', [PaymentsController::class, 'index']);
    });

     Route::prefix('students')->middleware('role:financial staff')->group(function(){
        Route::get('/', [StudentsController::class, 'index']);
    });

});


