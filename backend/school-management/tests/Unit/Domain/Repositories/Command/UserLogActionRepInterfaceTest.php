<?php

namespace Tests\Unit\Domain\Repositories\Command;

use Tests\Stubs\Repositories\Command\UserLogActionRepStub;
use Tests\Unit\Domain\Repositories\BaseRepositoryTestCase;
use App\Core\Domain\Repositories\Command\User\UserLogActionRepInterface;
use App\Core\Domain\Entities\UserActionLog;
use PHPUnit\Framework\Attributes\Test;

class UserLogActionRepInterfaceTest extends BaseRepositoryTestCase
{
    /**
     * The interface class being implemented
     */
    protected string $interfaceClass = UserLogActionRepInterface::class;

    /**
     * Setup the repository instance for testing
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Usamos un stub para probar el contrato
        $this->repository = new UserLogActionRepStub();
    }

    /**
     * Test que el repositorio puede ser instanciado
     */
    #[Test]
    public function it_can_be_instantiated(): void
    {
        $this->assertNotNull($this->repository, 'El repositorio no está inicializado');
        $this->assertImplementsInterface($this->interfaceClass);
    }

    /**
     * Test que todos los métodos requeridos existen
     */
    #[Test]
    public function it_has_all_required_methods(): void
    {
        $this->assertNotNull($this->repository, 'El repositorio no está inicializado');

        $methods = [
            'create',
            'deleteOlderLogs'
        ];

        foreach ($methods as $method) {
            $this->assertMethodExists($method);
        }
    }

    #[Test]
    public function it_can_create_user_action_log(): void
    {
        $log = new UserActionLog(
            method: 'POST',
            url: '/api/users',
            roles: ['admin'],
            userId: 1,
            ip: '192.168.1.1'
        );

        $result = $this->repository->create($log);

        $this->assertInstanceOf(UserActionLog::class, $result);
        $this->assertEquals('POST', $result->method);
        $this->assertEquals('/api/users', $result->url);
        $this->assertEquals(['admin'], $result->roles);
        $this->assertEquals(1, $result->userId);
        $this->assertEquals('192.168.1.1', $result->ip);
    }

    #[Test]
    public function created_log_can_have_null_values(): void
    {
        $log = new UserActionLog(
            method: 'GET',
            url: '/api/status'
        // roles, userId, ip son opcionales
        );

        $result = $this->repository->create($log);

        $this->assertInstanceOf(UserActionLog::class, $result);
        $this->assertEquals('GET', $result->method);
        $this->assertEquals('/api/status', $result->url);
        $this->assertIsArray($result->roles);
        $this->assertEmpty($result->roles);
        $this->assertNull($result->userId);
        $this->assertNull($result->ip);
    }

    #[Test]
    public function it_can_delete_older_logs(): void
    {
        $result = $this->repository->deleteOlderLogs();

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    #[Test]
    public function delete_older_logs_removes_logs_older_than_3_months(): void
    {
        $stub = new UserLogActionRepStub();

        // Agregar logs de diferentes edades
        $stub->addLog(new UserActionLog('GET', '/old', null, 1, 3,'127.0.0.1')); // Viejo
        $stub->addLog(new UserActionLog('POST', '/recent', ['user'], 2, 10,'192.168.1.1')); // Reciente

        $countBefore = $stub->getLogsCount();
        $deleted = $stub->deleteOlderLogs();
        $countAfter = $stub->getLogsCount();

        $this->assertGreaterThan(0, $deleted);
        $this->assertEquals($countBefore - $deleted, $countAfter);
    }

    #[Test]
    public function user_action_log_can_convert_to_array(): void
    {
        $log = new UserActionLog(
            method: 'PUT',
            url: '/api/users/1',
            roles: ['admin', 'editor'],
            userId: 5,
            ip: '10.0.0.1'
        );

        $array = $log->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('PUT', $array['method']);
        $this->assertEquals('/api/users/1', $array['url']);
        $this->assertEquals(['admin', 'editor'], $array['roles']);
        $this->assertEquals(5, $array['userId']);
        $this->assertEquals('10.0.0.1', $array['ip']);
    }

    #[Test]
    public function it_handles_database_errors_gracefully(): void
    {
        $stub = new UserLogActionRepStub();
        $stub->shouldThrowDatabaseError(true);

        $log = new UserActionLog('GET', '/api/test');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Database error');

        $stub->create($log);
    }

    #[Test]
    public function it_can_handle_multiple_operations(): void
    {
        $stub = new UserLogActionRepStub();

        // 1. Crear log
        $log1 = $stub->create(new UserActionLog('GET', '/api/users'));
        $this->assertNotNull($log1);

        // 2. Crear otro log con más detalles
        $log2 = $stub->create(new UserActionLog('POST', '/api/posts', ['admin'], 1, null,'192.168.1.100'));
        $this->assertNotNull($log2);

        // 3. Eliminar logs antiguos
        $deletedCount = $stub->deleteOlderLogs();
        $this->assertIsInt($deletedCount);

        // 4. Verificar que algunos logs permanecen
        $remainingCount = $stub->getLogsCount();
        $this->assertGreaterThanOrEqual(0, $remainingCount);
    }

    #[Test]
    public function delete_older_logs_handles_empty_logs(): void
    {
        $stub = new UserLogActionRepStub();
        $stub->clearLogs(); // Limpiar todos los logs

        $deleted = $stub->deleteOlderLogs();

        $this->assertEquals(0, $deleted);
    }

    #[Test]
    public function logs_can_have_different_http_methods(): void
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];

        foreach ($methods as $method) {
            $log = new UserActionLog($method, "/api/{$method}-test");
            $stub = new UserLogActionRepStub();

            $result = $stub->create($log);
            $this->assertEquals($method, $result->method);
        }
    }
}
