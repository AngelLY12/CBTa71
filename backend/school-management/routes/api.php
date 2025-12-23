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
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Students\WebhookController;
use App\Http\Controllers\UpdateUserController;
use App\Http\Controllers\NotificationController;

//Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post('/stripe/webhook', [WebhookController::class, 'handle']);

Route::prefix('v1')->middleware('throttle:5,1')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/register', [LoginController::class, 'register']);
    Route::post('/refresh-token', [RefreshTokenController::class, 'store']);

});

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function (){
    Route::post('/logout',[RefreshTokenController::class,'logout']);

    Route::prefix('notifications')->middleware(['throttle:30,1'])->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread', [NotificationController::class, 'unread']);
        Route::post('/mark-as-read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-as-read/{id}', [NotificationController::class, 'markAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    Route::prefix('parents')->middleware(['throttle:5,1', 'log.action', 'user.status'])->group(function(){
        Route::middleware(['role:student|admin',])->post('/invite',[ParentsController::class, 'sendInvitation']);
        Route::middleware(['role:parent'])->post('/invite/accept',[ParentsController::class, 'acceptInvitation']);
        Route::middleware(['role:parent'])->get('/get-children/{id}',[ParentsController::class,'getParetChildren']);
    });

    Route::prefix('dashboard')->middleware(['role:student|parent', 'throttle:global', 'log.action', 'user.status'])->group(function (){
        Route::middleware('permission:view own pending concepts summary')->get('/pending/{id}',[DashboardController::class,'pending']);
        Route::middleware('permission:view own paid concepts summary')->get('/paid/{id}',[DashboardController::class,'paid']);
        Route::middleware('permission:view own overdue concepts summary')->get('/overdue/{id}',[DashboardController::class,'overdue']);
        Route::middleware('permission:view payments history')->get('/history/{id}',[DashboardController::class,'history']);
        Route::middleware('permission:refresh all dashboard')->post('/refresh',[DashboardController::class,'refreshDashboard']);
    });
    Route::prefix('cards')->middleware(['role:student|parent', 'log.action', 'user.status'])->group(function(){
        Route::middleware(['permission:view cards','throttle:global'])->get('/{id}',[CardsController::class,'index']);
        Route::middleware(['permission:create setup', 'throttle:10,1'])->post('/',[CardsController::class,'store']);
        Route::middleware(['permission:delete card', 'throttle:10,1'])->delete('/{paymentMethodId}',[CardsController::class,'destroy']);
    });
    Route::prefix('history')->middleware(['role:student|parent','throttle:global', 'log.action', 'user.status'])->group(function(){
        Route::middleware('permission:view payment history')->get('/{id}',[PaymentHistoryController::class,'index']);
    });
    Route::prefix('pending-payment')->middleware(['role:student|parent', 'log.action', 'user.status'])->group(function(){
        Route::middleware(['permission:view pending concepts','throttle:global'])->get('/{id}',[PendingPaymentController::class,'index']);
        Route::middleware(['permission:create payment','throttle:5,1'])->post('/',[PendingPaymentController::class,'store']);
        Route::middleware(['permission:view overdue concepts','throttle:global'])->get('/overdue/{id}',[PendingPaymentController::class,'overdue']);

    });

    Route::prefix('dashboard-staff')->middleware(['role:financial-staff', 'log.action', 'user.status'])->group(function(){
        Route::middleware(['permission:view all pending concepts summary', 'throttle:global'])->get('/pending',[StaffDashboardController::class,'pendingPayments']);
        Route::middleware(['permission:view all students summary', 'throttle:global'])->get('/students',[StaffDashboardController::class,'allStudents']);
        Route::middleware(['permission:view all paid concepts summary', 'throttle:global'])->get('/payments',[StaffDashboardController::class,'paymentsMade']);
        Route::middleware(['permission:view concepts history', 'throttle:global'])->get('/concepts',[StaffDashboardController::class,'allConcepts']);
        Route::middleware(['permission:create payout', 'throttle:5,1'])->post('/payout',[StaffDashboardController::class,'payout']);
        Route::middleware(['permission:refresh all dashboard', 'throttle:5,1'])->post('/refresh',[StaffDashboardController::class,'refreshDashboard']);
    });
    Route::prefix('concepts')->middleware(['role:financial-staff', 'log.action', 'user.status'])->group(function(){
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

    Route::prefix('debts')->middleware(['role:financial-staff', 'log.action', 'user.status'])->group(function(){
        Route::middleware(['permission:view debts', 'throttle:global'])->get('/', [DebtsController::class, 'index']);
        Route::middleware(['permission:validate debt', 'throttle:10,1'])->post('/validate', [DebtsController::class, 'validatePayment']);
        Route::middleware(['permission:view stripe-payments', 'throttle:10,1'])->get('/stripe-payments', [DebtsController::class, 'getStripePayments']);
    });

    Route::prefix('payments')->middleware(['role:financial-staff', 'throttle:global', 'log.action', 'user.status'])->group(function(){
        Route::middleware('permission:view payments')->get('/', [PaymentsController::class, 'index']);
    });

     Route::prefix('students')->middleware(['role:financial-staff','throttle:global','log.action', 'user.status' ])->group(function(){
        Route::middleware('permission:view students')->get('/', [StudentsController::class, 'index']);
    });

    Route::prefix('admin-actions')->middleware(['role:admin|supervisor', 'throttle:global', 'log.action', 'user.status'])->group(function(){
        Route::middleware('permission:attach student')->post('/attach-student',[AdminController::class,'attachStudent']);
        Route::middleware('permission:import users')->post('/import-users', [AdminController::class, 'import']);
        Route::middleware('permission:import users')->post('/import-students', [AdminController::class, 'importStudents']);
        Route::middleware('permission:sync permissions')->post('/update-permissions',[AdminController::class,'updatePermissions']);
        Route::middleware('permission:view users')->get('/showUsers',[AdminController::class,'index']);
        Route::middleware('permission:sync roles')->post('/updated-roles', [AdminController::class, 'syncRoles']);
        Route::middleware('permission:activate users')->post('/activate-users', [AdminController::class, 'activateUsers']);
        Route::middleware('permission:disable users')->post('/disable-users', [AdminController::class, 'disableUsers']);
        Route::middleware('permission:disable users')->post('/temporary-disable-users', [AdminController::class, 'temporaryDisableUsers']);
        Route::middleware('permission:delete users')->post('/delete-users', [AdminController::class, 'deleteUsers']);
        Route::middleware('permission:view permissions')->post('/find-permissions', [AdminController::class, 'findAllPermissions']);
        Route::middleware('permission:view permissions')->get('/permissions/{id}', [AdminController::class, 'findPermissionById']);
        Route::middleware('permission:view roles')->get('/find-roles', [AdminController::class, 'findAllRoles']);
        Route::middleware('permission:view roles')->get('/roles/{id}', [AdminController::class, 'findRoleById']);
        Route::middleware('permission:create user')->post('/register',[AdminController::class,'registerUser']);
        Route::middleware('permission:view student')->get('/get-student/{id}', [AdminController::class, 'findStudentDetail']);
        Route::middleware('permission:update student')->patch('/update-student/{id}',[AdminController::class,'updateStudentDetail']);
        Route::middleware('permission:promote student')->patch('/promote',[AdminController::class,'promotionStudents']);


    });
    Route::prefix('find')->middleware(['role:student|financial-staff|parent','throttle:global', 'log.action', 'user.status'])->group(function(){
        Route::middleware('permission:view concept')->get('/concept/{id}',[FindEntityController::class,'findConcept']);
        Route::middleware('permission:view payment')->get('/payment/{id}',[FindEntityController::class,'findPayment']);
    });

    Route::prefix('careers')->middleware(['role:admin|supervisor', 'log.action', 'user.status'])->group(function(){
        Route::get('/', [CareerController::class, 'index']);
        Route::get('/{id}', [CareerController::class, 'show']);
        Route::post('/', [CareerController::class, 'store']);
        Route::patch('/{id}', [CareerController::class, 'update']);
        Route::delete('/{id}', [CareerController::class, 'destroy']);
    });

    Route::prefix('users')->middleware(['role:admin|student|financial-staff|parent|supervisor','throttle:10,1', 'log.action', 'user.status'])->group(function () {
        Route::patch('/update', [UpdateUserController::class, 'update']);
        Route::patch('/update/password', [UpdateUserController::class, 'updatePassword']);
        Route::get('/user',[FindEntityController::class,'findUser']);
    });


});

Route::fallback(function () {
    return response()->json(['message' => 'Endpoint no encontrado'], 404);
});




