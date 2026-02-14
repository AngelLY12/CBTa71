<?php

use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\Admin\AdminRolesPermissionsController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\CareerController;
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
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Response;
use App\Core\Domain\Enum\Exceptions\ErrorCode;
use Illuminate\Support\Facades\Cache;

//Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::get('/health', function () {
    return Response::success(null, 'Endpoint health success');

});
Route::post('/stripe/webhook', [WebhookController::class, 'handle']);

Route::prefix('v1')->middleware(['throttle:5,1'])->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/register', [LoginController::class, 'register']);
    Route::post('/refresh-token', [RefreshTokenController::class, 'store']);
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])
        ->name('password.email');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\NewPasswordController::class, 'store'])
        ->name('password.store');
    Route::get('/verify-email/{id}/{hash}', \App\Http\Controllers\Auth\VerifyEmailController::class)
        ->middleware(['signed','throttle:6,1'])
        ->name('verification.verify');

});

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function (){
    Route::post('/logout',[RefreshTokenController::class,'logout']);
    Route::post('/email/verification-notification', [\App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');
    Route::prefix('notifications')->middleware(['throttle:30,1'])->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread', [NotificationController::class, 'unread']);
        Route::post('/mark-as-read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-as-read/{id}', [NotificationController::class, 'markAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    Route::prefix('parents')->middleware(['throttle:5,1'])->group(function(){
        Route::middleware(['role:student',])->post('/invite',[ParentsController::class, 'sendInvitation']);
        Route::middleware(['role:parent'])->post('/invite/accept',[ParentsController::class, 'acceptInvitation']);
        Route::middleware(['role:parent'])->get('/get-children',[ParentsController::class,'getParetChildren']);
        Route::middleware(['role:student'])->get('/get-parents',[ParentsController::class,'getStudentParents']);
        Route::middleware(['role:student'])->delete('/delete-parent/{parentId}',[ParentsController::class,'delete']);
    });

    Route::prefix('dashboard')->middleware(['role:student|parent', 'throttle:global'])->group(function (){
        Route::middleware('permission:view.own.pending.concepts.summary')->get('/pending/{studentId?}',[DashboardController::class,'pending']);
        Route::middleware('permission:view.own.paid.concepts.summary')->get('/paid/{studentId?}',[DashboardController::class,'paid']);
        Route::middleware('permission:view.own.overdue.concepts.summary')->get('/overdue/{studentId?}',[DashboardController::class,'overdue']);
        Route::middleware('permission:view.payments.summary')->get('/history/{studentId?}',[DashboardController::class,'history']);
        Route::middleware('permission:refresh.all.dashboard')->post('/refresh/{studentId?}',[DashboardController::class,'refreshDashboard']);
    });
    Route::prefix('cards')->middleware(['role:student|parent'])->group(function(){
        Route::middleware(['permission:view.cards','throttle:global'])->get('/',[CardsController::class,'index']);
        Route::middleware(['permission:view.cards','throttle:global'])->get('/{studentId?}',[CardsController::class,'index']);
        Route::middleware(['permission:create.setup', 'throttle:10,1440'])->post('/',[CardsController::class,'store']);
        Route::middleware(['permission:delete.card', 'throttle:10,1'])->delete('/{paymentMethodId}',[CardsController::class,'destroy']);
    });
    Route::prefix('payments/history')->middleware(['role:student|parent','throttle:global'])->group(function(){
        Route::middleware('permission:view.payments.history')->get('/payment/{id}',[PaymentHistoryController::class,'findPayment']);
        Route::middleware('permission:view.receipt')->get('/receipt/{paymentId}',[PaymentHistoryController::class,'receiptPDF']);
        Route::middleware('permission:view.payments.history')->get('/{studentId?}',[PaymentHistoryController::class,'index']);

    });
    Route::prefix('pending-payments')->middleware(['role:student|parent'])->group(function(){
        Route::middleware(['permission:view.overdue.concepts','throttle:global'])->get('/overdue/{studentId?}',[PendingPaymentController::class,'overdue']);
        Route::middleware(['permission:create.payment','throttle:10,1440'])->post('/',[PendingPaymentController::class,'store']);
        Route::middleware(['permission:view.pending.concepts','throttle:global'])->get('/{studentId?}',[PendingPaymentController::class,'index']);

    });

    Route::prefix('dashboard-staff')->middleware(['role:financial-staff'])->group(function(){
        Route::middleware(['permission:view.all.pending.concepts.summary', 'throttle:global'])->get('/pending',[StaffDashboardController::class,'pendingPayments']);
        Route::middleware(['permission:view.all.students.summary', 'throttle:global'])->get('/students',[StaffDashboardController::class,'allStudents']);
        Route::middleware(['permission:view.all.paid.concepts.summary', 'throttle:global'])->get('/payments',[StaffDashboardController::class,'paymentsMade']);
        Route::middleware(['permission:view.concepts.summary', 'throttle:global'])->get('/concepts',[StaffDashboardController::class,'allConcepts']);
        Route::middleware(['permission:create.payout', 'throttle:5,1'])->post('/payout',[StaffDashboardController::class,'payout']);
        Route::middleware(['permission:refresh.all.dashboard', 'throttle:5,1'])->post('/refresh',[StaffDashboardController::class,'refreshDashboard']);
    });
    Route::prefix('concepts')->middleware(['role:financial-staff'])->group(function(){
        Route::middleware(['permission:view.concepts','throttle:10,1'])->get('/relations/{id}',[ConceptsController::class,'findRelations']);
        Route::get('/search/controls',[ConceptsController::class,'findNumberControlsBySearch']);
        Route::middleware(['permission:update.concepts', 'throttle:10,1'])->patch('/update-relations/{id}', [ConceptsController::class, 'updateRelations']);
        Route::middleware(['permission:finalize.concepts', 'throttle:10,1'])->post('/{concept}/finalize', [ConceptsController::class, 'finalize']);
        Route::middleware(['permission:disable.concepts', 'throttle:10,1'])->post('/{concept}/disable', [ConceptsController::class, 'disable']);
        Route::middleware(['permission:eliminate.concepts', 'throttle:10,1'])->delete('/{id}/eliminate', [ConceptsController::class, 'eliminate']);
        Route::middleware(['permission:eliminate.concepts', 'throttle:10,1'])->post('/{concept}/eliminateLogical',[ConceptsController::class,'eliminateLogical']);
        Route::middleware(['permission:activate.concepts', 'throttle:10,1'])->post('/{concept}/activate',[ConceptsController::class,'activate']);
        Route::middleware(['permission:view.concepts', 'throttle:global'])->get('/', [ConceptsController::class, 'index']);
        Route::middleware(['permission:create.concepts', 'throttle:10,1'])->post('/', [ConceptsController::class, 'store']);
        Route::middleware(['permission:view.concepts', 'throttle:10,1'])->get('/{id}',[ConceptsController::class,'findConcept']);
        Route::middleware(['permission:update.concepts', 'throttle:10,1'])->put('/{id}', [ConceptsController::class, 'update']);
        Route::middleware(['permission:update.concepts', 'throttle:10,1'])->patch('/{id}', [ConceptsController::class, 'update']);

    });

    Route::prefix('debts')->middleware(['role:financial-staff'])->group(function(){
        Route::middleware(['permission:view.debts', 'throttle:global'])->get('/', [DebtsController::class, 'index']);
        Route::middleware(['permission:validate.debt', 'throttle:10,1'])->post('/validate', [DebtsController::class, 'validatePayment']);
        Route::middleware(['permission:view.stripe.payments', 'throttle:10,1'])->get('/stripe-payments', [DebtsController::class, 'getStripePayments']);
    });

    Route::prefix('payments')->middleware(['role:financial-staff', 'throttle:global'])->group(function(){
        Route::middleware('permission:view.payments')->get('/', [PaymentsController::class, 'index']);
        Route::middleware('permission:view.payments')->get('/by-concept',[PaymentsController::class,'showByName']);
        Route::middleware('permission:view.payments.student.summary')->get('/students', [PaymentsController::class, 'showByStudents']);

    });

    Route::prefix('admin-actions')->middleware(['role:admin|supervisor', 'throttle:global'])->group(function(){

        Route::controller(AdminStudentController::class)->group(function(){
            Route::middleware('permission:attach.student')->post('/attach-student','attachStudent');
            Route::middleware('permission:import.users')->post('/import-students','importStudents');
            Route::middleware('permission:view.student')->get('/get-student/{id}','findStudentDetail');
            Route::middleware('permission:promote.student')->patch('/promote','promotionStudents');
            Route::middleware('permission:update.student')->patch('/update-student/{id}','updateStudentDetail');
        });
        Route::controller(AdminUsersController::class)->group(function(){
            Route::middleware('permission:import.users')->post('/import-users', 'import');
            Route::middleware('permission:view.users')->get('/users-summary','getSummary');
            Route::middleware('permission:view.users')->get('/show-users','index');
            Route::middleware('permission:view.users')->get('/show-users/{id}','getExtraUserData');
            Route::middleware('permission:disable.users')->post('/disable-users', 'disableUsers');
            Route::middleware('permission:disable.users')->post('/temporary-disable-users', 'temporaryDisableUsers');
            Route::middleware('permission:delete.users')->post('/delete-users','deleteUsers');
            Route::middleware('permission:activate.users')->post('/activate-users', 'activateUsers');
            Route::middleware('permission:create.user')->post('/register','registerUser');
        });
        Route::controller(AdminRolesPermissionsController::class)->group(function(){
            Route::middleware('permission:sync.permissions')->group(function(){
                Route::post('/update-permissions','updatePermissions');
                Route::post('/update-permissions/{userId}','updatePermissionsToUser');
            });
            Route::middleware('permission:sync.roles')->group(function(){
                Route::post('/updated-roles', 'syncRoles');
                Route::post('/updated-roles/{userId}','updateRolesToUser');
            });
            Route::middleware('permission:view.permissions')->group(function () {
                Route::post('/permissions/by-user/{userId}','findPermissionsToUser');
                Route::post('/permissions/by-curps','findAllPermissionsByCurps');
                Route::post('/permissions/by-role', 'findAllPermissionsByRole');
                Route::get('/permissions/{id}', 'findPermissionById');
            });
            Route::middleware('permission:view.roles')->group(function () {
                Route::get('/find-roles', 'findAllRoles');
                Route::get('/roles/{id}', 'findRoleById');
            });

        });
    });

    Route::prefix('careers')->group(function(){
        Route::middleware(['role:admin|supervisor|financial-staff'])->group(function () {
            Route::get('/', [CareerController::class, 'index']);
            Route::get('/{id}', [CareerController::class, 'show']);
        });

        Route::middleware(['role:admin|supervisor'])->group(function () {
            Route::post('/', [CareerController::class, 'store']);
            Route::patch('/{id}', [CareerController::class, 'update']);
            Route::delete('/{id}', [CareerController::class, 'destroy']);
        });
    });

    Route::prefix('users')->middleware(['throttle:10,1'])->group(function () {
        Route::patch('/update', [UserController::class, 'update']);
        Route::patch('/update/password', [UserController::class, 'updatePassword']);
        Route::get('/user',[UserController::class,'findUser']);
        Route::get('/student-details', [UserController::class,'findStudentDetails']);
    });


});

Route::fallback(function () {
    return Response::error('Método no existente', 400, null, ErrorCode::BAD_REQUEST->value);
});

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

Route::post('/gcs', function (Request $request) {
    try {
        $disk = Storage::disk('gcs');

        // Verificar que el bucket existe y es accesible
        try {
            $files = $disk->files(); // Intenta listar archivos
            \Log::info('Bucket files listing successful', ['count' => count($files)]);
        } catch (\Exception $e) {
            \Log::error('Error listing bucket files', ['error' => $e->getMessage()]);
            throw $e;
        }

        // 1. Probar escritura con más detalles
        $testFileName = 'test-' . time() . '.txt';
        $content = 'Prueba de conexión GCS - ' . now();

        \Log::info('Attempting to write file', [
            'filename' => $testFileName,
            'content_length' => strlen($content),
            'disk' => 'gcs'
        ]);

        $result = $disk->put($testFileName, $content);

        \Log::info('Put operation result', ['result' => $result]);

        // 2. Verificar que existe
        $exists = $disk->exists($testFileName);
        \Log::info('File exists check', ['exists' => $exists]);

        return response()->json([
            'success' => true,
            'message' => 'Conexión a GCS exitosa',
            'data' => [
                'file_created' => $testFileName,
                'exists' => $exists,
                'write_result' => $result,
                // ... resto de datos
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('GCS Error Details', [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error al conectar con GCS',
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::delete('/gcs/clean', function () {
    try {
        $disk = Storage::disk('gcs');
        $files = $disk->files('/');

        // Eliminar solo archivos de prueba (los que empiezan con 'test-')
        $deleted = [];
        foreach ($files as $file) {
            if (str_starts_with($file, 'test-')) {
                $disk->delete($file);
                $deleted[] = $file;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Archivos de prueba eliminados',
            'deleted_files' => $deleted
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/gcs-diagnostico', function () {
    $result = [
        'config' => [
            'bucket' => config('filesystems.disks.gcs.bucket'),
            'project_id' => config('filesystems.disks.gcs.project_id'),
            'client_email' => config('filesystems.disks.gcs.key_file.client_email'),
            'has_private_key' => !empty(config('filesystems.disks.gcs.key_file.private_key')),
        ],
        'tests' => []
    ];

    // Test 1: Listar buckets (requiere permisos adicionales)
    try {
        $storage = new \Google\Cloud\Storage\StorageClient([
            'projectId' => config('filesystems.disks.gcs.project_id'),
            'keyFile' => config('filesystems.disks.gcs.key_file')
        ]);

        $buckets = $storage->buckets();
        $result['tests']['list_buckets'] = '✅ Conexión exitosa';
    } catch (\Exception $e) {
        $result['tests']['list_buckets'] = '❌ Error: ' . $e->getMessage();
    }

    // Test 2: Verificar bucket específico
    try {
        $disk = Storage::disk('gcs');
        $files = $disk->files('/');
        $result['tests']['list_files'] = '✅ Bucket accesible, archivos: ' . count($files);
    } catch (\Exception $e) {
        $result['tests']['list_files'] = '❌ Error: ' . $e->getMessage();
    }

    return response()->json($result);
});


Route::post('/gcs-direct', function (Request $request) {
    try {
        // Usar el cliente directamente para mejor debugging
        $storage = new \Google\Cloud\Storage\StorageClient([
            'projectId' => config('filesystems.disks.gcs.project_id'),
            'keyFilePath' => config('filesystems.disks.gcs.key_file')
        ]);

        $bucket = $storage->bucket(config('filesystems.disks.gcs.bucket'));

        // Intentar escribir directamente
        $testFileName = 'test-direct-' . time() . '.txt';
        $object = $bucket->upload('Prueba directa - ' . now(), [
            'name' => $testFileName
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Escritura directa exitosa',
            'data' => [
                'file' => $testFileName,
                'exists' => $object->exists(),
                'info' => $object->info()
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ], 500);
    }
});
