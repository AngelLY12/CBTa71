<?php

use App\Http\Controllers\LoginController;
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

Route::prefix('v1')->middleware('throttle:10,1')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
});
Route::post('/stripe/webhook', [WebhookController::class, 'handle']);


Route::prefix('v1')->middleware(['auth:sanctum'])->group(function (){
    Route::prefix('dashboard')->middleware('role:student')->group(function (){
        Route::middleware('permission:view own financial overview')->get('/data',[DashboardController::class,'index']);
        Route::middleware('permission:view own pending concepts summary')->get('/pending',[DashboardController::class,'pending']);
        Route::middleware('permission:view own paid concepts summary')->get('/paid',[DashboardController::class,'paid']);
        Route::middleware('permission:view own overdue concepts summary')->get('/overdue',[DashboardController::class,'overdue']);
        Route::middleware('permission:view payments history')->get('/history',[DashboardController::class,'history']);

    });
    Route::prefix('cards')->middleware('role:student')->group(function(){
        Route::middleware('permission:view cards')->get('/',[CardsController::class,'index']);
        Route::middleware('permission:create setup')->post('/',[CardsController::class,'store']);
        Route::middleware('permission:create and view card')->get('/save', [CardsController::class, 'save']);
        Route::middleware('permission:delete card')->delete('/{paymentMethodId}',[CardsController::class,'destroy']);
    });
    Route::prefix('history')->middleware('role:student')->group(function(){
        Route::middleware('permission:view payment history')->get('/',[PaymentHistoryController::class,'index']);
    });
    Route::prefix('pending-payment')->middleware('role:student')->group(function(){
        Route::middleware('permission:view pending concepts')->get('/',[PendingPaymentController::class,'index']);
        Route::middleware('permission:create payment','throttle:5,1')->post('/',[PendingPaymentController::class,'store']);
        Route::middleware('permission:view overdue concepts')->get('/overdue',[PendingPaymentController::class,'overdue']);

    });

    Route::prefix('dashboard-staff')->middleware('role:financial staff')->group(function(){
        Route::middleware('permission:view all financial overview')->get('/data',[StaffDashboardController::class,'getData']);
        Route::middleware('permission:view all pending concepts summary')->get('/pending',[StaffDashboardController::class,'pendingPayments']);
        Route::middleware('permission:view all students summary')->get('/students',[StaffDashboardController::class,'allStudents']);
        Route::middleware('permission:view all paid concepts summary')->get('/payments',[StaffDashboardController::class,'paymentsMade']);
        Route::middleware('permission:view concepts history')->get('/concepts',[StaffDashboardController::class,'allConcepts']);
    });
    Route::prefix('concepts')->middleware('role:financial staff','throttle:60,1')->group(function(){
        Route::middleware('permission:view concepts')->get('/', [ConceptsController::class, 'index']);
        Route::middleware('permission:create concepts')->post('/', [ConceptsController::class, 'store']);
        Route::middleware('permission:update concepts')->put('/{concept}', [ConceptsController::class, 'update']);
        Route::middleware('permission:update concepts')->patch('/{concept}', [ConceptsController::class, 'update']);
        Route::middleware('permission:finalize concepts')->post('/{concept}/finalize', [ConceptsController::class, 'finalize']);
        Route::middleware('permission:disable concepts')->post('/{concept}/disable', [ConceptsController::class, 'disable']);
        Route::middleware('permission:eliminate concepts')->post('/{concept}/eliminate', [ConceptsController::class, 'eliminate']);

    });

    Route::prefix('debts')->middleware('role:financial staff','throttle:60,1')->group(function(){
        Route::middleware('permission:view debts')->get('/', [DebtsController::class, 'index']);
        Route::middleware('permission:validate debt')->post('/validate', [DebtsController::class, 'validatePayment']);
    });

    Route::prefix('payments')->middleware('role:financial staff')->group(function(){
        Route::middleware('permission:view payments')->get('/', [PaymentsController::class, 'index']);
    });

     Route::prefix('students')->middleware('role:financial staff')->group(function(){
        Route::middleware('permission:view students')->get('/', [StudentsController::class, 'index']);
    });

});


