<?php

use App\Http\Controllers\AdminController;
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
        ->name('api.password.email');

    Route::post('/reset-password', [\App\Http\Controllers\Auth\NewPasswordController::class, 'store'])
        ->name('api.password.store');

});

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function (){
    Route::post('/logout',[RefreshTokenController::class,'logout']);
    Route::get('/verify-email/{id}/{hash}', \App\Http\Controllers\Auth\VerifyEmailController::class)
        ->middleware(['throttle:6,1'])
        ->name('api.verification.verify');

    Route::post('/email/verification-notification', [\App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])
        ->middleware(['throttle:6,1'])
        ->name('api.verification.send');

    Route::prefix('notifications')->middleware(['throttle:30,1'])->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread', [NotificationController::class, 'unread']);
        Route::post('/mark-as-read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-as-read/{id}', [NotificationController::class, 'markAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    Route::prefix('parents')->middleware(['throttle:5,1', 'log.action', 'user.status'])->group(function(){
        Route::middleware(['role:student',])->post('/invite',[ParentsController::class, 'sendInvitation']);
        Route::middleware(['role:parent'])->post('/invite/accept',[ParentsController::class, 'acceptInvitation']);
        Route::middleware(['role:parent'])->get('/get-children',[ParentsController::class,'getParetChildren']);
        Route::middleware(['role:student'])->get('/get-parents',[ParentsController::class,'getStudentParents']);
        Route::middleware(['role:student'])->delete('/delete-parent/{parentId}',[ParentsController::class,'delete']);
    });

    Route::prefix('dashboard')->middleware(['role:student|parent', 'throttle:global', 'log.action', 'user.status'])->group(function (){
        Route::middleware('permission:view.own.pending.concepts.summary')->get('/pending/{id}',[DashboardController::class,'pending']);
        Route::middleware('permission:view.own.paid.concepts.summary')->get('/paid/{id}',[DashboardController::class,'paid']);
        Route::middleware('permission:view.own.overdue.concepts.summary')->get('/overdue/{id}',[DashboardController::class,'overdue']);
        Route::middleware('permission:view.payments.history')->get('/history/{id}',[DashboardController::class,'history']);
        Route::middleware('permission:refresh.all.dashboard')->post('/refresh',[DashboardController::class,'refreshDashboard']);
    });
    Route::prefix('cards')->middleware(['role:student|parent', 'log.action', 'user.status'])->group(function(){
        Route::middleware(['permission:view.cards','throttle:global'])->get('/{id}',[CardsController::class,'index']);
        Route::middleware(['permission:create.setup', 'throttle:10,1440'])->post('/',[CardsController::class,'store']);
        Route::middleware(['permission:delete.card', 'throttle:10,1'])->delete('/{paymentMethodId}',[CardsController::class,'destroy']);
    });
    Route::prefix('history')->middleware(['role:student|parent','throttle:global', 'log.action', 'user.status'])->group(function(){
        Route::middleware('permission:view.payment.history')->get('/{id}',[PaymentHistoryController::class,'index']);
        Route::middleware('permission:view.payment')->get('/payment/{id}',[PaymentHistoryController::class,'findPayment']);

    });
    Route::prefix('pending-payment')->middleware(['role:student|parent', 'log.action', 'user.status'])->group(function(){
        Route::middleware(['permission:view.pending.concepts','throttle:global'])->get('/{id}',[PendingPaymentController::class,'index']);
        Route::middleware(['permission:create.payment','throttle:10,1440'])->post('/',[PendingPaymentController::class,'store']);
        Route::middleware(['permission:view.overdue.concepts','throttle:global'])->get('/overdue/{id}',[PendingPaymentController::class,'overdue']);

    });

    Route::prefix('dashboard-staff')->middleware(['role:financial-staff', 'log.action', 'user.status'])->group(function(){
        Route::middleware(['permission:view.all.pending.concepts.summary', 'throttle:global'])->get('/pending',[StaffDashboardController::class,'pendingPayments']);
        Route::middleware(['permission:view.all.students.summary', 'throttle:global'])->get('/students',[StaffDashboardController::class,'allStudents']);
        Route::middleware(['permission:view.all.paid.concepts.summary', 'throttle:global'])->get('/payments',[StaffDashboardController::class,'paymentsMade']);
        Route::middleware(['permission:view.concepts.history', 'throttle:global'])->get('/concepts',[StaffDashboardController::class,'allConcepts']);
        Route::middleware(['permission:create.payout', 'throttle:5,1'])->post('/payout',[StaffDashboardController::class,'payout']);
        Route::middleware(['permission:refresh.all.dashboard', 'throttle:5,1'])->post('/refresh',[StaffDashboardController::class,'refreshDashboard']);
    });
    Route::prefix('concepts')->middleware(['role:financial-staff', 'log.action', 'user.status'])->group(function(){
        Route::middleware(['permission:view.concepts', 'throttle:global'])->get('/', [ConceptsController::class, 'index']);
        Route::middleware('permission:view.concepts')->get('/{id}',[ConceptsController::class,'findConcept']);
        Route::middleware(['permission:create.concepts', 'throttle:10,1'])->post('/', [ConceptsController::class, 'store']);
        Route::middleware(['permission:update.concepts', 'throttle:10,1'])->put('/{id}', [ConceptsController::class, 'update']);
        Route::middleware(['permission:update.concepts', 'throttle:10,1'])->patch('/{id}', [ConceptsController::class, 'update']);
        Route::middleware(['permission:update.concepts', 'throttle:10,1'])->patch('/update-relations/{id}', [ConceptsController::class, 'updateRelations']);
        Route::middleware(['permission:finalize.concepts', 'throttle:10,1'])->post('/{concept}/finalize', [ConceptsController::class, 'finalize']);
        Route::middleware(['permission:disable.concepts', 'throttle:10,1'])->post('/{concept}/disable', [ConceptsController::class, 'disable']);
        Route::middleware(['permission:eliminate.concepts', 'throttle:10,1'])->delete('/{id}/eliminate', [ConceptsController::class, 'eliminate']);
        Route::middleware(['permission:eliminate.logical.concepts', 'throttle:10,1'])->post('/{concept}/eliminateLogical',[ConceptsController::class,'eliminateLogical']);
        Route::middleware(['permission:activate.concepts', 'throttle:10,1'])->post('/{concept}/activate',[ConceptsController::class,'activate']);
    });

    Route::prefix('debts')->middleware(['role:financial-staff', 'log.action', 'user.status'])->group(function(){
        Route::middleware(['permission:view.debts', 'throttle:global'])->get('/', [DebtsController::class, 'index']);
        Route::middleware(['permission:validate.debt', 'throttle:10,1'])->post('/validate', [DebtsController::class, 'validatePayment']);
        Route::middleware(['permission:view.stripe.payments', 'throttle:10,1'])->get('/stripe-payments', [DebtsController::class, 'getStripePayments']);
    });

    Route::prefix('payments')->middleware(['role:financial-staff', 'throttle:global', 'log.action', 'user.status'])->group(function(){
        Route::middleware('permission:view.payments')->get('/', [PaymentsController::class, 'index']);
        Route::middleware('permission:view.payments')->get('/by-concept',[PaymentsController::class,'showByName']);
    });

     Route::prefix('students')->middleware(['role:financial-staff','throttle:global','log.action', 'user.status' ])->group(function(){
        Route::middleware('permission:view.students')->get('/', [StudentsController::class, 'index']);
    });

    Route::prefix('admin-actions')->middleware(['role:admin|supervisor', 'throttle:global', 'log.action', 'user.status'])->group(function(){
        Route::middleware('permission:attach.student')->post('/attach-student',[AdminController::class,'attachStudent']);
        Route::middleware('permission:import.users')->post('/import-users', [AdminController::class, 'import']);
        Route::middleware('permission:import.users')->post('/import-students', [AdminController::class, 'importStudents']);
        Route::middleware('permission:sync.permissions')->post('/update-permissions',[AdminController::class,'updatePermissions']);
        Route::middleware('permission:view.users')->get('/show-users',[AdminController::class,'index']);
        Route::middleware('permission:view.users')->get('/show-users/{id}',[AdminController::class,'getExtraUserData']);
        Route::middleware('permission:sync.roles')->post('/updated-roles', [AdminController::class, 'syncRoles']);
        Route::middleware('permission:activate.users')->post('/activate-users', [AdminController::class, 'activateUsers']);
        Route::middleware('permission:disable.users')->post('/disable-users', [AdminController::class, 'disableUsers']);
        Route::middleware('permission:disable.users')->post('/temporary-disable-users', [AdminController::class, 'temporaryDisableUsers']);
        Route::middleware('permission:delete.users')->post('/delete-users', [AdminController::class, 'deleteUsers']);
        Route::middleware('permission:view.permissions')->post('/find-permissions', [AdminController::class, 'findAllPermissions']);
        Route::middleware('permission:view.permissions')->get('/permissions/{id}', [AdminController::class, 'findPermissionById']);
        Route::middleware('permission:view.roles')->get('/find-roles', [AdminController::class, 'findAllRoles']);
        Route::middleware('permission:view.roles')->get('/roles/{id}', [AdminController::class, 'findRoleById']);
        Route::middleware('permission:create.user')->post('/register',[AdminController::class,'registerUser']);
        Route::middleware('permission:view.student')->get('/get-student/{id}', [AdminController::class, 'findStudentDetail']);
        Route::middleware('permission:update.student')->patch('/update-student/{id}',[AdminController::class,'updateStudentDetail']);
        Route::middleware('permission:promote.student')->patch('/promote',[AdminController::class,'promotionStudents']);
    });

    Route::prefix('careers')->middleware(['role:admin|supervisor', 'log.action', 'user.status'])->group(function(){
        Route::get('/', [CareerController::class, 'index']);
        Route::get('/{id}', [CareerController::class, 'show']);
        Route::post('/', [CareerController::class, 'store']);
        Route::patch('/{id}', [CareerController::class, 'update']);
        Route::delete('/{id}', [CareerController::class, 'destroy']);
    });

    Route::prefix('users')->middleware(['throttle:10,1', 'log.action', 'user.status'])->group(function () {
        Route::patch('/update', [UserController::class, 'update']);
        Route::patch('/update/password', [UserController::class, 'updatePassword']);
        Route::get('/user',[UserController::class,'findUser']);
    });


});

Route::fallback(function () {
    return Response::error('No autenticado', 400, null, ErrorCode::BAD_REQUEST->value);
});

Route::get('/test-redis-connection', function() {
    try {
        // Test conexión cache
        Cache::put('test', 'works', 10);
        $value = Cache::get('test');

        // Test conexión default
        $redis = Cache::getRedis();
        $info = $redis->info();

        return [
            'cache_test' => $value === 'works' ? '✅' : '❌',
            'redis_connection' => '✅ CONECTADO',
            'redis_db' => $redis->getDbNum(),
            'host' => config('database.redis.cache.host'),
            'database_used' => config('database.redis.cache.database'),
        ];
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
});

Route::get('/test-cache-service-complete', function() {
    $cacheService = app(\App\Core\Infraestructure\Cache\CacheService::class);
    $results = [];
    $timestamp = time();

    // 1. Test put() y get() con TTL
    $key1 = "test_put_get_{$timestamp}";
    $cacheService->put($key1, ['test' => 'data'], 60);
    $results['test_1_put_get_ttl'] = [
        'method' => 'put() + get() with TTL',
        'expected' => ['test' => 'data'],
        'actual' => $cacheService->get($key1),
        'passed' => $cacheService->get($key1) === ['test' => 'data'] ? '✅' : '❌',
    ];

    // 2. Test put() forever y get()
    $key2 = "test_forever_{$timestamp}";
    $cacheService->put($key2, 'forever_value', null);
    $results['test_2_put_forever'] = [
        'method' => 'put() forever',
        'expected' => 'forever_value',
        'actual' => $cacheService->get($key2),
        'passed' => $cacheService->get($key2) === 'forever_value' ? '✅' : '❌',
    ];

    // 3. Test putMany() y getMany()
    $keys = ["test_many_1_{$timestamp}", "test_many_2_{$timestamp}"];
    $values = ['value1', 'value2'];
    $cacheService->putMany(array_combine($keys, $values), 60);
    $results['test_3_putmany_getmany'] = [
        'method' => 'putMany() + getMany()',
        'expected' => $values,
        'actual' => array_values($cacheService->getMany($keys)),
        'passed' => array_values($cacheService->getMany($keys)) === $values ? '✅' : '❌',
    ];

    // 4. Test add() (solo agrega si no existe)
    $key4 = "test_add_{$timestamp}";
    $firstAdd = $cacheService->add($key4, 'first_value', 60);
    $secondAdd = $cacheService->add($key4, 'second_value', 60);
    $results['test_4_add'] = [
        'method' => 'add()',
        'expected_first' => true,
        'actual_first' => $firstAdd,
        'expected_second' => false,
        'actual_second' => $secondAdd,
        'value_stored' => unserialize($cacheService->get($key4)),
        'passed' => ($firstAdd === true && $secondAdd === false) ? '✅' : '❌',
    ];

    // 5. Test has()
    $key5 = "test_has_{$timestamp}";
    $cacheService->put($key5, 'test', 60);
    $results['test_5_has'] = [
        'method' => 'has()',
        'key_exists' => $cacheService->has($key5),
        'key_nonexistent' => $cacheService->has("nonexistent_key_{$timestamp}"),
        'passed' => ($cacheService->has($key5) && !$cacheService->has("nonexistent_key_{$timestamp}")) ? '✅' : '❌',
    ];

    // 6. Test forget()
    $key6 = "test_forget_{$timestamp}";
    $cacheService->put($key6, 'to_delete', 60);
    $beforeForget = $cacheService->has($key6);
    $cacheService->forget($key6);
    $afterForget = $cacheService->has($key6);
    $results['test_6_forget'] = [
        'method' => 'forget()',
        'before_forget' => $beforeForget,
        'after_forget' => $afterForget,
        'passed' => ($beforeForget && !$afterForget) ? '✅' : '❌',
    ];

    // 7. Test remember()
    $key7 = "test_remember_{$timestamp}";
    $callbackExecuted = false;
    $result = $cacheService->remember($key7, 60, function() use (&$callbackExecuted) {
        $callbackExecuted = true;
        return 'callback_result';
    });
    $cachedResult = $cacheService->get($key7);
    $results['test_7_remember'] = [
        'method' => 'remember()',
        'callback_executed' => $callbackExecuted,
        'result' => $result,
        'cached_value' => $cachedResult,
        'passed' => ($callbackExecuted && $result === 'callback_result' && $cachedResult === 'callback_result') ? '✅' : '❌',
    ];

    // 8. Test rememberForever() (con logging)
    $key8 = "test_rememberforever_{$timestamp}";
    $foreverCallbackExecuted = false;
    $foreverResult = $cacheService->rememberForever($key8, function() use (&$foreverCallbackExecuted) {
        $foreverCallbackExecuted = true;
        return ['forever' => 'data', 'time' => now()];
    });
    $results['test_8_rememberforever'] = [
        'method' => 'rememberForever()',
        'callback_executed' => $foreverCallbackExecuted,
        'result_type' => gettype($foreverResult),
        'has_forever_key' => $foreverResult['forever'] ?? null,
        'passed' => ($foreverCallbackExecuted && isset($foreverResult['forever'])) ? '✅' : '❌',
    ];

    // 9. Test increment() y decrement()
    $key9 = "test_counter_{$timestamp}";
    $cacheService->put($key9, 10, 60);
    $afterIncrement = $cacheService->increment($key9, 5);
    $afterDecrement = $cacheService->decrement($key9, 3);
    $results['test_9_increment_decrement'] = [
        'method' => 'increment() + decrement()',
        'initial' => 10,
        'after_increment_5' => $afterIncrement,
        'after_decrement_3' => $afterDecrement,
        'final_value' => $cacheService->get($key9),
        'passed' => ($afterIncrement === 15 && $afterDecrement === 12 && $cacheService->get($key9) === 12) ? '✅' : '❌',
    ];

    // 10. Test makeKey()
    $testKey = $cacheService->makeKey('admin', 'test:suffix');
    $results['test_10_makekey'] = [
        'method' => 'makeKey()',
        'prefix_config' => config('cache-prefixes.admin'),
        'generated_key' => $testKey,
        'expected_format' => config('cache-prefixes.admin') . ':test:suffix',
        'passed' => ($testKey === config('cache-prefixes.admin') . ':test:suffix') ? '✅' : '❌',
    ];

    // 11. Test clearPrefix()
    $prefix = "test_prefix_{$timestamp}";
    $keysToClear = [
        "{$prefix}:key1",
        "{$prefix}:key2",
        "other_prefix:key3"
    ];

    foreach ($keysToClear as $key) {
        $cacheService->put($key, 'value', 60);
    }

    $beforeClear = array_filter($keysToClear, fn($k) => $cacheService->has($k));
    $cacheService->clearPrefix($prefix);
    $afterClear = array_filter($keysToClear, fn($k) => $cacheService->has($k));

    $results['test_11_clearprefix'] = [
        'method' => 'clearPrefix()',
        'keys_before' => $beforeClear,
        'keys_after' => $afterClear,
        'passed' => (count($beforeClear) === 3 && count($afterClear) === 1) ? '✅' : '❌',
    ];

    // 12. Test clearKey()
    $prefixKey = 'admin';
    $suffix = "clear_test_{$timestamp}";
    $fullKey = $cacheService->makeKey($prefixKey, $suffix);
    $cacheService->put($fullKey, 'to_clear', 60);

    $beforeClearKey = $cacheService->has($fullKey);
    $cacheService->clearKey($prefixKey, $suffix);
    $afterClearKey = $cacheService->has($fullKey);

    $results['test_12_clearkey'] = [
        'method' => 'clearKey()',
        'full_key' => $fullKey,
        'before_clear' => $beforeClearKey,
        'after_clear' => $afterClearKey,
        'passed' => ($beforeClearKey && !$afterClearKey) ? '✅' : '❌',
    ];

    // 13. Test métodos de limpieza específicos (sin ejecutar, solo verificar que existen)
    $results['test_13_specific_clear_methods'] = [
        'method' => 'Specific clear methods (existence check)',
        'clearStaffCache_exists' => method_exists($cacheService, 'clearStaffCache') ? '✅' : '❌',
        'clearStudentCache_exists' => method_exists($cacheService, 'clearStudentCache') ? '✅' : '❌',
        'clearParentCache_exists' => method_exists($cacheService, 'clearParentCache') ? '✅' : '❌',
        'clearCacheWhileConceptChangeStatus_exists' => method_exists($cacheService, 'clearCacheWhileConceptChangeStatus') ? '✅' : '❌',
    ];

    // Resumen
    $passedTests = count(array_filter($results, function($test) {
        return strpos($test['passed'] ?? '', '✅') !== false;
    }));

    $totalTests = count($results);

    return [
        'test_summary' => [
            'total_tests' => $totalTests,
            'passed_tests' => $passedTests,
            'failed_tests' => $totalTests - $passedTests,
            'success_rate' => round(($passedTests / $totalTests) * 100, 2) . '%',
            'timestamp' => now()->toDateTimeString(),
            'redis_connection' => Cache::getRedis()->isConnected() ? '✅ CONECTADO' : '❌ DESCONECTADO',
            'cache_prefix' => Cache::getPrefix(),
            'cache_driver' => config('cache.default'),
        ],
        'detailed_results' => $results,
        'notes' => [
            'Los métodos de limpieza específicos (test 13) solo verifican existencia',
            'Para testear clearStaffCache() y otros necesitarías datos reales en cache',
            'Los logs de rememberForever aparecerán en storage/logs/laravel.log',
        ]
    ];
});

Route::get('/debug-clearprefix', function() {
    $cacheService = app(\App\Core\Infraestructure\Cache\CacheService::class);
    $redis = Cache::getRedis();

    // Key de test
    $testKey = "test_debug:key1";
    $cacheService->put($testKey, 'value', 60);

    // Ver prefijos
    $laravelPrefix = Cache::getPrefix();
    $fullKey = $laravelPrefix . $testKey;

    return [
        'test_key' => $testKey,
        'laravel_prefix' => $laravelPrefix,
        'full_key_in_redis' => $fullKey,
        'exists_in_redis' => (bool) $redis->get($fullKey),
        'cache_service_has' => $cacheService->has($testKey),
        'keys_in_redis_matching' => $redis->keys("*test_debug*"),
    ];
});

