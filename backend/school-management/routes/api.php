<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\FindEntityController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Parents\ParentsController;
use App\Http\Controllers\RefreshTokenController;
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
use App\Http\Controllers\UpdateUserController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/stripe/webhook', [WebhookController::class, 'handle']);

Route::prefix('v1')->middleware('throttle:5,1')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/register', [LoginController::class, 'register']);
    Route::post('/refresh-token', [RefreshTokenController::class, 'store']);

});

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function (){
    Route::post('/logout',[RefreshTokenController::class,'logout']);

    Route::prefix('parents')->middleware(['role:student', 'role:admin', 'throttle:5,1'])->group(function(){
        Route::post('/invite',[ParentsController::class, 'sendInvitation']);
        Route::post('/invite/accept',[ParentsController::class, 'acceptInvitation']);
    });

    Route::prefix('dashboard')->middleware(['role:student', 'role:parent', 'throttle:global'])->group(function (){
        Route::middleware('permission:view own financial overview')->get('/data',[DashboardController::class,'index']);
        Route::middleware('permission:view own pending concepts summary')->get('/pending',[DashboardController::class,'pending']);
        Route::middleware('permission:view own paid concepts summary')->get('/paid',[DashboardController::class,'paid']);
        Route::middleware('permission:view own overdue concepts summary')->get('/overdue',[DashboardController::class,'overdue']);
        Route::middleware('permission:view payments history')->get('/history',[DashboardController::class,'history']);
        Route::middleware('permission:refresh all dashboard')->post('/refresh',[DashboardController::class,'refreshDashboard']);


    });
    Route::prefix('cards')->middleware('role:student')->group(function(){
        Route::middleware(['permission:view cards', 'role:parent','throttle:global'])->get('/',[CardsController::class,'index']);
        Route::middleware(['permission:create setup', 'throttle:10,1'])->post('/',[CardsController::class,'store']);
        Route::middleware(['permission:delete card', 'throttle:10,1'])->delete('/{paymentMethodId}',[CardsController::class,'destroy']);
    });
    Route::prefix('history')->middleware(['role:student', 'role:parent','throttle:global'])->group(function(){
        Route::middleware('permission:view payment history')->get('/',[PaymentHistoryController::class,'index']);
    });
    Route::prefix('pending-payment')->middleware('role:student')->group(function(){
        Route::middleware(['permission:view pending concepts', 'role:parent','throttle:global'])->get('/',[PendingPaymentController::class,'index']);
        Route::middleware(['permission:create payment','throttle:5,1'])->post('/',[PendingPaymentController::class,'store']);
        Route::middleware(['permission:view overdue concepts', 'role:parent','throttle:global'])->get('/overdue',[PendingPaymentController::class,'overdue']);

    });

    Route::prefix('dashboard-staff')->middleware(['role:financial staff', 'throttle:global'])->group(function(){
        Route::middleware('permission:view all financial overview')->get('/data',[StaffDashboardController::class,'getData']);
        Route::middleware('permission:view all pending concepts summary')->get('/pending',[StaffDashboardController::class,'pendingPayments']);
        Route::middleware('permission:view all students summary')->get('/students',[StaffDashboardController::class,'allStudents']);
        Route::middleware('permission:view all paid concepts summary')->get('/payments',[StaffDashboardController::class,'paymentsMade']);
        Route::middleware('permission:view concepts history')->get('/concepts',[StaffDashboardController::class,'allConcepts']);
        Route::middleware('permission:refresh all dashboard')->post('/refresh',[StaffDashboardController::class,'refreshDashboard']);
    });
    Route::prefix('concepts')->middleware('role:financial staff')->group(function(){
        Route::middleware(['permission:view concepts', 'throttle:global'])->get('/', [ConceptsController::class, 'index']);
        Route::middleware(['permission:create concepts', 'throttle:10,1'])->post('/', [ConceptsController::class, 'store']);
        Route::middleware(['permission:update concepts', 'throttle:10,1'])->put('/{concept}', [ConceptsController::class, 'update']);
        Route::middleware(['permission:update concepts', 'throttle:10,1'])->patch('/{concept}', [ConceptsController::class, 'update']);
        Route::middleware(['permission:finalize concepts', 'throttle:10,1'])->post('/{concept}/finalize', [ConceptsController::class, 'finalize']);
        Route::middleware(['permission:disable concepts', 'throttle:10,1'])->post('/{concept}/disable', [ConceptsController::class, 'disable']);
        Route::middleware(['permission:eliminate concepts', 'throttle:10,1'])->delete('/{concept}/eliminate', [ConceptsController::class, 'eliminate']);
        Route::middleware(['permission:eliminate logical concept', 'throttle:10,1'])->post('/{concept}/eliminateLogical',[ConceptsController::class,'eliminateLogical']);
        Route::middleware(['permission:activate concept', 'throttle:10,1'])->post('/{concept}/activate',[ConceptsController::class,'activate']);
    });

    Route::prefix('debts')->middleware('role:financial staff')->group(function(){
        Route::middleware(['permission:view debts', 'throttle:global'])->get('/', [DebtsController::class, 'index']);
        Route::middleware(['permission:validate debt', 'throttle:10,1'])->post('/validate', [DebtsController::class, 'validatePayment']);
        Route::middleware(['permission:view stripe-payments', 'throttle:10,1'])->get('/stripe-payments', [DebtsController::class, 'getStripePayments']);
    });

    Route::prefix('payments')->middleware(['role:financial staff', 'throttle:global'])->group(function(){
        Route::middleware('permission:view payments')->get('/', [PaymentsController::class, 'index']);
    });

     Route::prefix('students')->middleware(['role:financial staff','throttle:global'])->group(function(){
        Route::middleware('permission:view students')->get('/', [StudentsController::class, 'index']);
    });

    Route::prefix('admin-actions')->middleware(['role:admin', 'throttle:global'])->group(function(){
        Route::middleware('permission:attach student')->post('/attach-student',[AdminController::class,'attachStudent']);
        Route::middleware('permission:import users')->post('/import-users', [AdminController::class, 'import']);
        Route::middleware('permission:sync permissions')->post('/update-permissions',[AdminController::class,'updatePermissions']);
        Route::middleware('permission:view users')->get('/showUsers',[AdminController::class,'index']);
        Route::middleware('permission:sync roles')->post('/updated-roles', [AdminController::class, 'syncRoles']);
        Route::middleware('permission:activate users')->post('/activate-users', [AdminController::class, 'activateUsers']);
        Route::middleware('permission:disable users')->post('/disable-users', [AdminController::class, 'disableUsers']);
        Route::middleware('permission:delete users')->post('/delete-users', [AdminController::class, 'deleteUsers']);
        Route::middleware('permission:view permissions')->get('/find-permissions', [AdminController::class, 'findAllPermissions']);
        Route::middleware('permission:view permissions')->get('/permissions/{id}', [AdminController::class, 'findPermissionById']);
        Route::middleware('permission:view roles')->get('/find-roles', [AdminController::class, 'findAllRoles']);
        Route::middleware('permission:view roles')->get('/roles/{id}', [AdminController::class, 'findRoleById']);
        Route::middleware('permission:create user')->post('/register',[AdminController::class,'registerUser']);
    });
    Route::prefix('find')->middleware(['role:admin','role:student','role:financial staff','role:parent','throttle:global'])->group(function(){
        Route::middleware('permission:view concept')->get('/concept/{id}',[FindEntityController::class,'findConcept']);
        Route::middleware('permission:view payment')->get('/payment/{id}',[FindEntityController::class,'findPayment']);
        Route::middleware('permission:view profile')->get('/user',[FindEntityController::class,'findUser']);
    });

    Route::prefix('careers')->middleware('role:admin')->group(function(){
        Route::get('/', [CareerController::class, 'index']);
        Route::get('/{id}', [CareerController::class, 'show']);
        Route::post('/', [CareerController::class, 'store']);
        Route::patch('/{id}', [CareerController::class, 'update']);
        Route::delete('/{id}', [CareerController::class, 'destroy']);
    });

    Route::prefix('users')->middleware(['role:admin','role:student','role:financial staff','throttle:10,1'])->group(function () {
        Route::patch('/{userId}', [UpdateUserController::class, 'update']);
        Route::patch('/{userId}/password', [UpdateUserController::class, 'updatePassword']);
    });


});

Route::fallback(function () {
    return response()->json(['message' => 'Endpoint no encontrado'], 404);
});




