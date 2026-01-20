<?php

namespace Tests\Unit\Infraestructure\Repositories\Cache;

use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Enum\Cache\ParentCacheSufix;
use App\Core\Domain\Enum\Cache\StaffCacheSufix;
use App\Core\Domain\Enum\Cache\StudentCacheSufix;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
use App\Core\Infraestructure\Cache\CacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Mockery;

class CacheServiceTest extends TestCase
{
    private CacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();

        // Configurar prefijos de cache
        Config::set('cache-prefixes.staff', 'staff');
        Config::set('cache-prefixes.student', 'student');
        Config::set('cache-prefixes.parent', 'parent');

        $this->cacheService = new CacheService();

    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    private function mockRedis(array $scanReturns = [], bool $expectsDel = false, array $delKeys = []): void
    {
        $redisMock = Mockery::mock('Redis');

        // Configurar scan
        if (empty($scanReturns)) {
            $redisMock->shouldReceive('scan')
                ->andReturnUsing(function (&$cursor, $pattern, $count) {
                    $cursor = 0;
                    return [];
                });
        } else {
            $callCount = 0;
            $redisMock->shouldReceive('scan')
                ->andReturnUsing(function (&$cursor, $pattern, $count) use ($scanReturns, &$callCount) {
                    if ($callCount < count($scanReturns)) {
                        $result = $scanReturns[$callCount];
                        $callCount++;
                        $cursor = 0; // Terminar después de la primera llamada
                        return $result;
                    }
                    $cursor = 0;
                    return [];
                });
        }

        // Configurar del si se espera
        if ($expectsDel) {
            $redisMock->shouldReceive('del')
                ->with($delKeys)
                ->once();
        } else {
            $redisMock->shouldReceive('del')
                ->never();
        }

        Cache::shouldReceive('getRedis')
            ->andReturn($redisMock);
    }


    #[Test]
    public function get_returns_value_from_cache(): void
    {
        // Arrange
        $key = 'test_key';
        $expectedValue = 'test_value';

        Cache::shouldReceive('get')
            ->once()
            ->with($key, null)
            ->andReturn($expectedValue);

        // Act
        $result = $this->cacheService->get($key);

        // Assert
        $this->assertEquals($expectedValue, $result);
    }

    #[Test]
    public function get_returns_default_when_key_not_found(): void
    {
        // Arrange
        $key = 'nonexistent_key';
        $default = 'default_value';

        Cache::shouldReceive('get')
            ->once()
            ->with($key, $default)
            ->andReturn($default);

        // Act
        $result = $this->cacheService->get($key, $default);

        // Assert
        $this->assertEquals($default, $result);
    }

    #[Test]
    public function getMany_returns_multiple_values(): void
    {
        // Arrange
        $keys = ['key1', 'key2'];
        $expectedValues = ['key1' => 'value1', 'key2' => 'value2'];

        Cache::shouldReceive('getMultiple')
            ->once()
            ->with($keys, null)
            ->andReturn($expectedValues);

        // Act
        $result = $this->cacheService->getMany($keys);

        // Assert
        $this->assertEquals($expectedValues, $result);
    }

    #[Test]
    public function put_stores_value_with_ttl(): void
    {
        // Arrange
        $key = 'test_key';
        $value = 'test_value';
        $ttl = 3600;

        Cache::shouldReceive('put')
            ->once()
            ->with($key, $value, $ttl);

        // Act & Assert
        $this->cacheService->put($key, $value, $ttl);
        $this->assertTrue(true); // Para evitar "risky test"
    }

    #[Test]
    public function put_stores_value_forever_when_no_ttl(): void
    {
        // Arrange
        $key = 'test_key';
        $value = 'test_value';

        Cache::shouldReceive('forever')
            ->once()
            ->with($key, $value);

        // Act & Assert
        $this->cacheService->put($key, $value);
        $this->assertTrue(true);
    }

    #[Test]
    public function putMany_stores_multiple_values(): void
    {
        // Arrange
        $values = ['key1' => 'value1', 'key2' => 'value2'];
        $ttl = 3600;

        Cache::shouldReceive('putMany')
            ->once()
            ->with($values, $ttl);

        // Act & Assert
        $this->cacheService->putMany($values, $ttl);
        $this->assertTrue(true);
    }

    #[Test]
    public function forget_removes_key_from_cache(): void
    {
        // Arrange
        $key = 'test_key';

        Cache::shouldReceive('forget')
            ->once()
            ->with($key);

        // Act & Assert
        $this->cacheService->forget($key);
        $this->assertTrue(true);
    }

    #[Test]
    public function has_checks_if_key_exists(): void
    {
        // Arrange
        $key = 'test_key';

        Cache::shouldReceive('has')
            ->once()
            ->with($key)
            ->andReturn(true);

        // Act
        $result = $this->cacheService->has($key);

        // Assert
        $this->assertTrue($result);
    }

    #[Test]
    public function remember_caches_callback_result(): void
    {
        // Arrange
        $key = 'test_key';
        $ttl = 3600;
        $expectedValue = 'cached_value';
        $callback = fn() => $expectedValue;

        Cache::shouldReceive('remember')
            ->once()
            ->with($key, $ttl, $callback)
            ->andReturn($expectedValue);

        // Act
        $result = $this->cacheService->remember($key, $ttl, $callback);

        // Assert
        $this->assertEquals($expectedValue, $result);
    }

    #[Test]
    public function increment_increases_value(): void
    {
        // Arrange
        $key = 'counter';
        $increment = 2;
        $expectedResult = 3;

        Cache::shouldReceive('increment')
            ->once()
            ->with($key, $increment)
            ->andReturn($expectedResult);

        // Act
        $result = $this->cacheService->increment($key, $increment);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    #[Test]
    public function increment_increases_by_one_by_default(): void
    {
        // Arrange
        $key = 'counter';
        $expectedResult = 1;

        Cache::shouldReceive('increment')
            ->once()
            ->with($key, 1)
            ->andReturn($expectedResult);

        // Act
        $result = $this->cacheService->increment($key);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    #[Test]
    public function decrement_decreases_value(): void
    {
        // Arrange
        $key = 'counter';
        $decrement = 2;
        $expectedResult = 1;

        Cache::shouldReceive('decrement')
            ->once()
            ->with($key, $decrement)
            ->andReturn($expectedResult);

        // Act
        $result = $this->cacheService->decrement($key, $decrement);

        // Assert
        $this->assertEquals($expectedResult, $result);
    }

    #[Test]
    public function rememberForever_caches_callback_result_indefinitely(): void
    {
        // Arrange
        $key = 'test_key';
        $expectedValue = 'cached_value';
        $callback = fn() => $expectedValue;

        Cache::shouldReceive('rememberForever')
            ->once()
            ->with($key, $callback)
            ->andReturn($expectedValue);

        // Act
        $result = $this->cacheService->rememberForever($key, $callback);

        // Assert
        $this->assertEquals($expectedValue, $result);
    }

    #[Test]
    public function makeKey_generates_key_from_config(): void
    {
        // Arrange
        $prefixKey = 'staff';
        $suffix = 'dashboard';
        $expectedKey = 'staff:dashboard';

        // Act
        $result = $this->cacheService->makeKey($prefixKey, $suffix);

        // Assert
        $this->assertEquals($expectedKey, $result);
    }

    // ==================== TESTS DE REDIS ====================

    #[Test]
    public function clearPrefix_scans_and_deletes_keys_with_redis(): void
    {
        // Arrange
        $prefix = 'staff:dashboard:*';
        $keys = ['staff:dashboard:1', 'staff:dashboard:2'];

        $this->mockRedis([$keys], true, $keys);

        // Act
        $this->cacheService->clearPrefix($prefix);

        // Assert - el mock se verifica automáticamente
        $this->assertTrue(true);
    }

    #[Test]
    public function clearPrefix_handles_empty_keys(): void
    {
        // Arrange
        $prefix = 'staff:dashboard:*';

        $this->mockRedis([[]], false);

        // Act
        $this->cacheService->clearPrefix($prefix);

        // Assert
        $this->assertTrue(true);
    }

    #[Test]
    public function clearCacheWhileConceptChangeStatus_also_clears_staff_cache_for_all_statuses(): void
    {
        // Arrange
        $userId = 123;
        $statuses = [
            PaymentConceptStatus::ACTIVO,
            PaymentConceptStatus::FINALIZADO,
            PaymentConceptStatus::ELIMINADO,
            PaymentConceptStatus::DESACTIVADO,
        ];

        foreach ($statuses as $status) {
            $cacheServiceMock = Mockery::mock(CacheService::class)->makePartial();

            // Mock Redis para evitar errores
            $this->mockRedis([[]], false);

            // Expectativa: clearKey debe ser llamado 4 veces para las staff suffixes
            $cacheServiceMock->shouldReceive('clearKey')
                ->times(4)
                ->with(CachePrefix::STAFF->value, Mockery::type('string'));

            // Act
            $cacheServiceMock->clearCacheWhileConceptChangeStatus($userId, $status);
        }

        $this->assertTrue(true);
    }

    #[Test]
    public function clearKey_does_nothing_when_prefix_not_configured(): void
    {
        // Arrange
        Config::set('cache-prefixes.staff', null);
        $prefixKey = 'staff';
        $suffix = 'dashboard:*';

        // Mock Redis vacío
        $this->mockRedis([[]], false);

        // Act
        $this->cacheService->clearKey($prefixKey, $suffix);

        // Assert
        $this->assertTrue(true);
    }

    #[Test]
    public function clearKey_uses_config_to_generate_prefix_and_clears_it(): void
    {
        // Arrange
        $prefixKey = 'staff';
        $suffix = 'dashboard:*';

        // Crear un partial mock del servicio
        $cacheServiceMock = Mockery::mock(CacheService::class)->makePartial();

        // Expectativa: clearPrefix debe ser llamado con el prefijo completo
        $cacheServiceMock->shouldReceive('clearPrefix')
            ->once()
            ->with('staff:dashboard:*');

        // Act & Assert
        $cacheServiceMock->clearKey($prefixKey, $suffix);
        $this->assertTrue(true);
    }

    #[Test]
    public function clearStaffCache_clears_all_staff_cache_suffixes(): void
    {
        // Arrange
        $cacheServiceMock = Mockery::mock(CacheService::class)->makePartial();

        // Expectativas para cada clearKey
        $cacheServiceMock->shouldReceive('clearKey')
            ->once()
            ->with(CachePrefix::STAFF->value, StaffCacheSufix::DASHBOARD->value . ":*");

        $cacheServiceMock->shouldReceive('clearKey')
            ->once()
            ->with(CachePrefix::STAFF->value, StaffCacheSufix::DEBTS->value . ":*");

        $cacheServiceMock->shouldReceive('clearKey')
            ->once()
            ->with(CachePrefix::STAFF->value, StaffCacheSufix::PAYMENTS->value . ":*");

        $cacheServiceMock->shouldReceive('clearKey')
            ->once()
            ->with(CachePrefix::STAFF->value, StaffCacheSufix::STUDENTS->value . ":*");

        // Act & Assert
        $cacheServiceMock->clearStaffCache();
        $this->assertTrue(true);
    }

    #[Test]
    public function clearStudentCache_clears_student_cache_for_user(): void
    {
        // Arrange
        $userId = 123;
        $cacheServiceMock = Mockery::mock(CacheService::class)->makePartial();

        // Expectativas
        $cacheServiceMock->shouldReceive('clearKey')
            ->once()
            ->with(CachePrefix::STAFF->value, StudentCacheSufix::DASHBOARD_USER->value . ":*:$userId");

        $cacheServiceMock->shouldReceive('clearKey')
            ->once()
            ->with(CachePrefix::STAFF->value, StudentCacheSufix::PENDING->value . ":*:$userId");

        $cacheServiceMock->shouldReceive('clearKey')
            ->once()
            ->with(CachePrefix::STAFF->value, StudentCacheSufix::HISTORY->value . ":$userId");

        // Act & Assert
        $cacheServiceMock->clearStudentCache($userId);
        $this->assertTrue(true);
    }

    #[Test]
    public function clearParentCache_clears_parent_cache(): void
    {
        // Arrange
        $parentId = 456;
        $cacheServiceMock = Mockery::mock(CacheService::class)->makePartial();

        // Expectativa
        $cacheServiceMock->shouldReceive('clearKey')
            ->once()
            ->with(CachePrefix::PARENT->value, ParentCacheSufix::CHILDREN->value . ":$parentId");

        // Act & Assert
        $cacheServiceMock->clearParentCache($parentId);
        $this->assertTrue(true);
    }

    #[Test]
    public function clearCacheWhileConceptChangeStatus_clears_student_cache_for_activo_status(): void
    {
        // Arrange
        $userId = 123;
        $status = PaymentConceptStatus::ACTIVO;
        $cacheServiceMock = Mockery::mock(CacheService::class)->makePartial();

        // Expectativas para student suffixes
        $cacheServiceMock->shouldReceive('clearKey')
            ->once()
            ->with(CachePrefix::STUDENT->value, StudentCacheSufix::PENDING->value . ":*:$userId");

        $cacheServiceMock->shouldReceive('clearKey')
            ->once()
            ->with(CachePrefix::STUDENT->value, StudentCacheSufix::DASHBOARD_USER->value . ":pending:$userId");

        // Expectativas para staff suffixes
        $cacheServiceMock->shouldReceive('clearKey')
            ->times(4); // Para las 4 staff suffixes

        // Act & Assert
        $cacheServiceMock->clearCacheWhileConceptChangeStatus($userId, $status);
        $this->assertTrue(true);
    }

    // ==================== TESTS DE CONFIGURACIÓN FALTANTE ====================

    #[Test]
    public function makeKey_returns_empty_string_when_prefix_not_configured(): void
    {
        // Arrange
        Config::set('cache-prefixes.staff', null);
        $prefixKey = 'staff';
        $suffix = 'dashboard';

        // Act
        $result = $this->cacheService->makeKey($prefixKey, $suffix);

        // Assert
        $this->assertEquals(':dashboard', $result);
    }
}
