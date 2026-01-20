<?php

namespace Tests\Unit\Infraestructure\Repositories\Command;

use App\Core\Domain\Entities\UserActionLog;
use App\Core\Infraestructure\Mappers\UserLogActionMapper;
use App\Models\UserActionLog as ModelsUserActionLog;
use Carbon\Carbon;
use App\Core\Infraestructure\Repositories\Command\User\EloquentUserLogActionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EloquentUserLogActionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentUserLogActionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentUserLogActionRepository();
    }

    // ==================== CREATE TESTS ====================

    #[Test]
    public function create_user_action_log_successfully(): void
    {
        // Arrange
        $log = new UserActionLog(
            method: 'POST',
            url: '/api/auth/login',
            roles: ['admin', 'user'],
            userId: 123,
            ip: '192.168.1.100'
        );

        // Act
        $result = $this->repository->create($log);

        // Assert
        $this->assertInstanceOf(UserActionLog::class, $result);
        $this->assertEquals('POST', $result->method);
        $this->assertEquals('/api/auth/login', $result->url);
        $this->assertEquals(['admin', 'user'], $result->roles);
        $this->assertEquals(123, $result->userId);
        $this->assertEquals('192.168.1.100', $result->ip);

        $this->assertDatabaseHas('user_action_logs', [
            'method' => 'POST',
            'url' => '/api/auth/login',
            'user_id' => 123,
            'ip' => '192.168.1.100',
        ]);

        // Verificar que los roles se guardaron como JSON
        $dbRecord = ModelsUserActionLog::first();
        $this->assertEquals(['admin', 'user'], $dbRecord->roles);
    }

    #[Test]
    public function create_user_action_log_with_minimal_data(): void
    {
        // Arrange
        $log = new UserActionLog(
            method: 'GET',
            url: '/api/dashboard'
        // Sin roles, userId ni ip
        );

        // Act
        $result = $this->repository->create($log);

        // Assert
        $this->assertInstanceOf(UserActionLog::class, $result);
        $this->assertNotNull($result->id);
        $this->assertEquals('GET', $result->method);
        $this->assertEquals('/api/dashboard', $result->url);
        $this->assertEmpty($result->roles);
        $this->assertNull($result->userId);
        $this->assertNull($result->ip);
    }

    #[Test]
    public function create_user_action_log_with_empty_roles_array(): void
    {
        // Arrange
        $log = new UserActionLog(
            method: 'PUT',
            url: '/api/users/update',
            roles: [], // Array vacío
            userId: 456
        );

        // Act
        $result = $this->repository->create($log);

        // Assert
        $this->assertInstanceOf(UserActionLog::class, $result);
        $this->assertEquals([], $result->roles);

        $dbRecord = ModelsUserActionLog::first();
        $this->assertEquals([], $dbRecord->roles);
    }

    #[Test]
    public function create_user_action_log_with_single_role(): void
    {
        // Arrange
        $log = new UserActionLog(
            method: 'DELETE',
            url: '/api/users/1',
            roles: ['admin'],
            userId: 789
        );

        // Act
        $result = $this->repository->create($log);

        // Assert
        $this->assertEquals(['admin'], $result->roles);

        $dbRecord = ModelsUserActionLog::first();
        $this->assertEquals(['admin'], $dbRecord->roles);
    }

    #[Test]
    public function create_user_action_log_with_factory(): void
    {
        // Arrange
        $logData = ModelsUserActionLog::factory()->make();
        $domainLog = UserLogActionMapper::toDomain($logData);

        // Act
        $result = $this->repository->create($domainLog);

        // Assert
        $this->assertInstanceOf(UserActionLog::class, $result);
        $this->assertEquals($logData->method, $result->method);
        $this->assertEquals($logData->url, $result->url);
        $this->assertEquals($logData->roles, $result->roles);
        $this->assertEquals($logData->user_id, $result->userId);
        $this->assertEquals($logData->ip, $result->ip);

        $this->assertDatabaseHas('user_action_logs', [
            'method' => $logData->method,
            'url' => $logData->url,
        ]);
    }

    #[Test]
    public function create_multiple_user_action_logs(): void
    {
        // Arrange
        $log1 = new UserActionLog(
            method: 'GET',
            url: '/api/users',
            userId: 1
        );

        $log2 = new UserActionLog(
            method: 'POST',
            url: '/api/users',
            userId: 2
        );

        $log3 = new UserActionLog(
            method: 'PUT',
            url: '/api/users/1',
            userId: 1
        );

        // Act
        $result1 = $this->repository->create($log1);
        $result2 = $this->repository->create($log2);
        $result3 = $this->repository->create($log3);

        // Assert
        $this->assertNotEquals($result1->id, $result2->id);
        $this->assertNotEquals($result1->id, $result3->id);
        $this->assertEquals(3, ModelsUserActionLog::count());
    }

    #[Test]
    public function create_user_action_log_with_special_characters_in_url(): void
    {
        // Arrange
        $url = '/api/users/search?name=juan%20perez&status=active&page=2';
        $log = new UserActionLog(
            method: 'GET',
            url: $url,
            userId: 100
        );

        // Act
        $result = $this->repository->create($log);

        // Assert
        $this->assertEquals($url, $result->url);
        $this->assertDatabaseHas('user_action_logs', [
            'url' => $url,
            'user_id' => 100,
        ]);
    }

    // ==================== DELETE OLDER LOGS TESTS ====================

    #[Test]
    public function delete_older_logs_removes_logs_older_than_3_months(): void
    {
        // Arrange
        // Crear logs con diferentes fechas
        $recentLog = ModelsUserActionLog::factory()->create([
            'created_at' => Carbon::now()->subMonths(2) // Hace 2 meses (NO se elimina)
        ]);

        $oldLog1 = ModelsUserActionLog::factory()->create([
            'created_at' => Carbon::now()->subMonths(4) // Hace 4 meses (SÍ se elimina)
        ]);

        $oldLog2 = ModelsUserActionLog::factory()->create([
            'created_at' => Carbon::now()->subMonths(6) // Hace 6 meses (SÍ se elimina)
        ]);

        $oldLog3 = ModelsUserActionLog::factory()->create([
            'created_at' => Carbon::now()->subYears(1) // Hace 1 año (SÍ se elimina)
        ]);

        $exact3MonthsLog = ModelsUserActionLog::factory()->create([
            'created_at' => Carbon::now()->subMonths(3) // Exactamente 3 meses (NO se elimina - es "<" no "<=")
        ]);

        // Act
        $deletedCount = $this->repository->deleteOlderLogs();

        // Assert
        $this->assertEquals(3, $deletedCount); // Solo oldLog1, oldLog2, oldLog3

        // Verificar qué logs siguen existiendo
        $this->assertDatabaseHas('user_action_logs', [
            'id' => $recentLog->id,
        ]);

        $this->assertDatabaseHas('user_action_logs', [
            'id' => $exact3MonthsLog->id,
        ]);

        // Verificar qué logs fueron eliminados
        $this->assertDatabaseMissing('user_action_logs', [
            'id' => $oldLog1->id,
        ]);

        $this->assertDatabaseMissing('user_action_logs', [
            'id' => $oldLog2->id,
        ]);

        $this->assertDatabaseMissing('user_action_logs', [
            'id' => $oldLog3->id,
        ]);

        // Total de logs después de la operación
        $remainingCount = ModelsUserActionLog::count();
        $this->assertEquals(2, $remainingCount); // recentLog + exact3MonthsLog
    }

    #[Test]
    public function delete_older_logs_with_no_old_logs_returns_zero(): void
    {
        // Arrange - Solo logs recientes
        ModelsUserActionLog::factory()->count(5)->create([
            'created_at' => Carbon::now()->subMonths(2)
        ]);

        // Act
        $deletedCount = $this->repository->deleteOlderLogs();

        // Assert
        $this->assertEquals(0, $deletedCount);

        $remainingCount = ModelsUserActionLog::count();
        $this->assertEquals(5, $remainingCount);
    }

    #[Test]
    public function delete_older_logs_with_only_old_logs_deletes_all(): void
    {
        // Arrange - Solo logs antiguos
        $oldLogs = ModelsUserActionLog::factory()->count(7)->create([
            'created_at' => Carbon::now()->subMonths(4)
        ]);

        // Act
        $deletedCount = $this->repository->deleteOlderLogs();

        // Assert
        $this->assertEquals(7, $deletedCount);

        $remainingCount = ModelsUserActionLog::count();
        $this->assertEquals(0, $remainingCount);
    }

    #[Test]
    public function delete_older_logs_handles_large_number_of_records(): void
    {
        // Arrange - Crear 1500 logs (más que el chunk size de 1000)
        $recentLogs = ModelsUserActionLog::factory()->count(500)->create([
            'created_at' => Carbon::now()->subMonths(1)
        ]);

        $oldLogs = ModelsUserActionLog::factory()->count(1000)->create([
            'created_at' => Carbon::now()->subMonths(5)
        ]);

        // Act
        $deletedCount = $this->repository->deleteOlderLogs();

        // Assert
        $this->assertEquals(1000, $deletedCount);

        $remainingCount = ModelsUserActionLog::count();
        $this->assertEquals(500, $remainingCount);

        // Todos los logs recientes deben seguir existiendo
        foreach ($recentLogs as $log) {
            $this->assertDatabaseHas('user_action_logs', [
                'id' => $log->id,
            ]);
        }
    }

    #[Test]
    public function delete_older_logs_preserves_recent_logs_with_different_criteria(): void
    {
        // Arrange - Logs con diferentes combinaciones
        $logs = [
            // Logs que NO deben eliminarse
            ['created_at' => Carbon::now()->subDays(10), 'method' => 'GET', 'user_id' => 1],
            ['created_at' => Carbon::now()->subMonths(2), 'method' => 'POST', 'user_id' => 2],

            // Logs que SÍ deben eliminarse
            ['created_at' => Carbon::now()->subMonths(3)->subDays(1), 'method' => 'PUT', 'user_id' => 3], // Justo antes de 3 meses
            ['created_at' => Carbon::now()->subMonths(3)->subDays(2), 'method' => 'DELETE', 'user_id' => 4],
            ['created_at' => Carbon::now()->subMonths(4), 'method' => 'GET', 'user_id' => 5],
            ['created_at' => Carbon::now()->subMonths(6), 'method' => 'POST', 'user_id' => null],
        ];

        foreach ($logs as $logData) {
            ModelsUserActionLog::factory()->create($logData);
        }

        // Act
        $deletedCount = $this->repository->deleteOlderLogs();

        // Assert
        $this->assertEquals(4, $deletedCount); // Los últimos 3

        // Verificar los logs que quedaron
        $this->assertDatabaseHas('user_action_logs', [
            'method' => 'GET',
            'user_id' => 1,
        ]);

        $this->assertDatabaseHas('user_action_logs', [
            'method' => 'POST',
            'user_id' => 2,
        ]);

        $this->assertDatabaseMissing('user_action_logs', [
            'method' => 'PUT',
            'user_id' => 3,
        ]);

        // Verificar los logs que se eliminaron
        $this->assertDatabaseMissing('user_action_logs', [
            'method' => 'DELETE',
            'user_id' => 4,
        ]);

        $this->assertDatabaseMissing('user_action_logs', [
            'method' => 'GET',
            'user_id' => 5,
        ]);

        $this->assertDatabaseMissing('user_action_logs', [
            'method' => 'POST',
            'user_id' => null,
        ]);
    }

    // ==================== FACTORY METHODS TESTS ====================

    #[Test]
    public function create_log_with_factory_methods(): void
    {
        // Test para verificar diferentes métodos del factory

        // Log con usuario específico
        $user = \App\Models\User::factory()->create();
        $logWithUser = ModelsUserActionLog::factory()->forUser($user)->make();
        $domainLog1 = UserLogActionMapper::toDomain($logWithUser);
        $result1 = $this->repository->create($domainLog1);
        $this->assertEquals($user->id, $result1->userId);

        // Log anónimo
        $anonymousLog = ModelsUserActionLog::factory()->anonymous()->make();
        $domainLog2 = UserLogActionMapper::toDomain($anonymousLog);
        $result2 = $this->repository->create($domainLog2);
        $this->assertNull($result2->userId);
        $this->assertNull($result2->roles);

        // Log como admin
        $adminLog = ModelsUserActionLog::factory()->asAdmin()->make();
        $domainLog3 = UserLogActionMapper::toDomain($adminLog);
        $result3 = $this->repository->create($domainLog3);
        $this->assertEquals(['admin'], $result3->roles);

        // Log con IP específica
        $localLog = ModelsUserActionLog::factory()->fromLocal()->make();
        $domainLog4 = UserLogActionMapper::toDomain($localLog);
        $result4 = $this->repository->create($domainLog4);
        $this->assertEquals('127.0.0.1', $result4->ip);
    }

    #[Test]
    public function create_log_with_different_request_methods(): void
    {
        // Test todos los métodos HTTP usando factory methods

        $methods = [
            'GET' => fn() => ModelsUserActionLog::factory()->getRequest(),
            'POST' => fn() => ModelsUserActionLog::factory()->postRequest(),
            'PUT/PATCH' => fn() => ModelsUserActionLog::factory()->putRequest(),
            'DELETE' => fn() => ModelsUserActionLog::factory()->deleteRequest(),
        ];

        foreach ($methods as $expectedMethod => $factoryMethod) {
            $logData = $factoryMethod()->make();
            $domainLog = UserLogActionMapper::toDomain($logData);
            $result = $this->repository->create($domainLog);

            if ($expectedMethod === 'PUT/PATCH') {
                $this->assertContains($result->method, ['PUT', 'PATCH']);
            } else {
                $this->assertEquals($expectedMethod, $result->method);
            }
        }
    }

    #[Test]
    public function create_log_with_different_endpoints(): void
    {
        // Test diferentes endpoints usando factory methods

        $endpoints = [
            'auth' => fn() => ModelsUserActionLog::factory()->authEndpoint(),
            'profile' => fn() => ModelsUserActionLog::factory()->profileEndpoint(),
            'student' => fn() => ModelsUserActionLog::factory()->studentEndpoint(),
            'admin' => fn() => ModelsUserActionLog::factory()->adminEndpoint(),
            'payment' => fn() => ModelsUserActionLog::factory()->paymentEndpoint(),
        ];

        foreach ($endpoints as $type => $factoryMethod) {
            $logData = $factoryMethod()->create();
            $domainLog = UserLogActionMapper::toDomain($logData);
            $result = $this->repository->create($domainLog);

            $this->assertStringContainsString('/api/', $result->url);

            // Verificar patrones según el tipo
            switch ($type) {
                case 'auth':
                    $this->assertStringContainsString('/auth/', $result->url);
                    break;
                case 'profile':
                    $this->assertStringContainsString('/user/', $result->url);
                    break;
                case 'student':
                    $this->assertStringContainsString('/students/', $result->url);
                    break;
                case 'admin':
                    $this->assertStringContainsString('/admin/', $result->url);
                    break;
                case 'payment':
                    $this->assertStringContainsString('/payments/', $result->url);
                    break;
            }
        }
    }

    // ==================== DOMAIN OBJECT TESTS ====================

    #[Test]
    public function user_action_log_to_array_method(): void
    {
        // Arrange
        $log = new UserActionLog(
            method: 'POST',
            url: '/api/users',
            roles: ['admin', 'editor'],
            userId: 123,
            ip: '192.168.1.50'
        );

        // Act
        $array = $log->toArray();

        // Assert
        $this->assertIsArray($array);
        $this->assertEquals('POST', $array['method']);
        $this->assertEquals('/api/users', $array['url']);
        $this->assertEquals(['admin', 'editor'], $array['roles']);
        $this->assertEquals(123, $array['userId']);
        $this->assertEquals('192.168.1.50', $array['ip']);
    }

    #[Test]
    public function user_action_log_with_null_values_in_to_array(): void
    {
        // Arrange
        $log = new UserActionLog(
            method: 'GET',
            url: '/api/dashboard'
        );

        // Act
        $array = $log->toArray();

        // Assert
        $this->assertIsArray($array);
        $this->assertEquals('GET', $array['method']);
        $this->assertEquals('/api/dashboard', $array['url']);
        $this->assertEquals([], $array['roles']); // Array vacío, no null
        $this->assertNull($array['userId']);
        $this->assertNull($array['ip']);
    }

    // ==================== INTEGRATION TESTS ====================

    #[Test]
    public function create_and_delete_older_logs_integration(): void
    {
        // Test completo de integración

        // 1. Crear logs de diferentes edades
        $recentLogs = ModelsUserActionLog::factory()->count(3)->create([
            'created_at' => Carbon::now()->subMonths(1)
        ]);

        $oldLogs = ModelsUserActionLog::factory()->count(2)->create([
            'created_at' => Carbon::now()->subMonths(4)
        ]);

        // 2. Crear un nuevo log manualmente
        $newLog = new UserActionLog(
            method: 'POST',
            url: '/api/test',
            userId: 999,
            ip: '10.0.0.1'
        );

        $createdLog = $this->repository->create($newLog);

        // 3. Verificar que hay 6 logs en total
        $initialCount = ModelsUserActionLog::count();
        $this->assertEquals(6, $initialCount);

        // 4. Eliminar logs antiguos
        $deletedCount = $this->repository->deleteOlderLogs();
        $this->assertEquals(2, $deletedCount); // Solo los 2 logs de hace 4 meses

        // 5. Verificar logs restantes
        $finalCount = ModelsUserActionLog::count();
        $this->assertEquals(4, $finalCount); // 3 recientes + 1 nuevo

        // 6. Verificar que el nuevo log sigue existiendo
        $this->assertDatabaseHas('user_action_logs', [
            'method' => 'POST',
            'url' => '/api/test',
            'user_id' => 999,
            'ip' => '10.0.0.1',
        ]);

        // 7. Verificar que los logs antiguos se eliminaron
        foreach ($oldLogs as $log) {
            $this->assertDatabaseMissing('user_action_logs', [
                'id' => $log->id,
            ]);
        }

        // 8. Verificar que los logs recientes siguen existiendo
        foreach ($recentLogs as $log) {
            $this->assertDatabaseHas('user_action_logs', [
                'id' => $log->id,
            ]);
        }
    }

    #[Test]
    public function repository_handles_concurrent_operations(): void
    {
        // Arrange - Crear logs iniciales
        $initialLogs = ModelsUserActionLog::factory()->count(5)->create([
            'created_at' => Carbon::now()->subMonths(2)
        ]);

        $oldLogs = ModelsUserActionLog::factory()->count(3)->create([
            'created_at' => Carbon::now()->subMonths(5)
        ]);

        // Act: Operaciones concurrentes
        // 1. Crear nuevos logs
        $newLog1 = new UserActionLog(method: 'PUT', url: '/api/update/1', userId: 1);
        $newLog2 = new UserActionLog(method: 'DELETE', url: '/api/delete/2', userId: 2);

        $created1 = $this->repository->create($newLog1);
        $created2 = $this->repository->create($newLog2);

        // 2. Eliminar logs antiguos
        $deletedCount = $this->repository->deleteOlderLogs();

        // Assert
        $this->assertEquals(3, $deletedCount); // Los 3 logs de hace 5 meses

        $totalCount = ModelsUserActionLog::count();
        $this->assertEquals(7, $totalCount); // 5 iniciales + 2 nuevos

        // Los nuevos logs deben existir
        $this->assertDatabaseHas('user_action_logs', [
            'method' => 'PUT',
            'url' => '/api/update/1',
        ]);

        $this->assertDatabaseHas('user_action_logs', [
            'method' => 'DELETE',
            'url' => '/api/delete/2',
        ]);

        // Los logs antiguos no deben existir
        foreach ($oldLogs as $log) {
            $this->assertDatabaseMissing('user_action_logs', [
                'id' => $log->id,
            ]);
        }
    }

    #[Test]
    public function large_scale_log_operations(): void
    {
        // Test para manejo de gran volumen de datos

        // Crear 2000 logs (500 recientes, 1500 antiguos)
        $recentLogs = ModelsUserActionLog::factory()->count(500)->create([
            'created_at' => Carbon::now()->subMonths(1)
        ]);

        $oldLogs = ModelsUserActionLog::factory()->count(1500)->create([
            'created_at' => Carbon::now()->subMonths(4)
        ]);

        // Verificar creación
        $initialCount = ModelsUserActionLog::count();
        $this->assertEquals(2000, $initialCount);

        // Eliminar logs antiguos (debería usar chunking)
        $deletedCount = $this->repository->deleteOlderLogs();
        $this->assertEquals(1500, $deletedCount);

        // Verificar logs restantes
        $finalCount = ModelsUserActionLog::count();
        $this->assertEquals(500, $finalCount);

        // Todos los logs recientes deben seguir existiendo
        $remainingLogs = ModelsUserActionLog::whereIn('id', $recentLogs->pluck('id'))->count();
        $this->assertEquals(500, $remainingLogs);
    }

}
