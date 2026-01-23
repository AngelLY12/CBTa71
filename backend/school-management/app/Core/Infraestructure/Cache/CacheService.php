<?php

namespace App\Core\Infraestructure\Cache;

use App\Core\Domain\Enum\Cache\CachePrefix;
use App\Core\Domain\Enum\Cache\ParentCacheSufix;
use App\Core\Domain\Enum\Cache\StaffCacheSufix;
use App\Core\Domain\Enum\Cache\StudentCacheSufix;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
use Closure;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    public function get(string $key, $default = null)
    {
        return Cache::get($key, $default);
    }

    public function getMany(array $keys, $default = null)
    {
        return Cache::getMultiple($keys, $default);
    }

    public function put(string $key, $value, $ttl = null): void
    {
        if ($ttl === null) {
            Cache::forever($key, $value);
        } else {
            Cache::put($key, $value, $ttl);
        }
    }

    public function putMany(array $values, $ttl = null): void
    {
        Cache::putMany($values, $ttl);
    }

    public function add(string $key, $value, $ttl = null): bool
    {
        return Cache::add($key, serialize($value), $ttl);
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    public function has(string $key): bool
    {
        return Cache::has($key);
    }

    public function remember(string $key, $ttl, Closure $callback)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    public function increment(string $key, $value = 1)
    {
        return Cache::increment($key, $value);
    }

    public function decrement(string $key, $value = 1)
    {
        return Cache::decrement($key, $value);
    }

    public function rememberForever(string $key, Closure $callback)
    {
        return Cache::rememberForever($key, $callback);
    }

    public function makeKey(string $prefixKey, string $suffix): string
    {
        $prefix = config("cache-prefixes.$prefixKey");
        return "{$prefix}{$suffix}";
    }

    public function clearPrefix(string $prefix): void
    {
        $store = Cache::store('redis');
        $redis = $store->getRedis();
        $pattern = $prefix . '*';

        Log::info('=== CLEAR PREFIX DEBUG ===', [
            'store' => $store,
            'redis' => $redis,
            'input_prefix' => $prefix,
            'pattern_used' => $pattern,
            'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB',
        ]);

        $allKeysBefore = $redis->keys('*');

        $keys = $redis->keys($pattern);


        if (empty($keys)) {
            $keys = $redis->keys($prefix . ':*');
        }

        if (empty($keys)) {
            $allKeys = $redis->keys('*');
            $keys = array_filter($allKeys, fn($k) => str_contains($k, $prefix));
        }

        Log::info('📊 ESTADO ANTES de eliminar', [
            'total_keys_redis' => count($allKeysBefore),
            'keys_con_patron' => count($keys),
            'matching_keys' => $keys,
        ]);
        if (!empty($keys)) {
            $deletedCount = $redis->del($keys);
            Log::info('✅ ELIMINACIÓN EJECUTADA', [
                'keys_intentadas' => count($keys),
                'keys_eliminadas' => $deletedCount,
                'keys_especificas' => $keys,
            ]);

            // 3. Verificar INMEDIATAMENTE después
            usleep(100000); // 100ms
            $matchingKeysImmediatelyAfter = $redis->keys($pattern);

            Log::info('🔍 VERIFICACIÓN INMEDIATA (100ms después)', [
                'keys_encontradas' => count($matchingKeysImmediatelyAfter),
                'keys' => $matchingKeysImmediatelyAfter,
                'regeneracion_inmediata' => !empty($matchingKeysImmediatelyAfter),
            ]);

            // 4. Verificar después de 1 segundo
            sleep(1);
            $matchingKeys1SecondAfter = $redis->keys($pattern);
            $allKeysAfter = $redis->keys('*');

            Log::info('⏰ VERIFICACIÓN 1 SEGUNDO DESPUÉS', [
                'keys_con_patron' => count($matchingKeys1SecondAfter),
                'total_keys_redis' => count($allKeysAfter),
                'keys_regeneradas' => $matchingKeys1SecondAfter,
                'comparacion_total' => [
                    'antes' => count($allKeysBefore),
                    'despues' => count($allKeysAfter),
                    'diferencia' => count($allKeysAfter) - count($allKeysBefore),
                ],
            ]);

            // 5. Si se regeneró, investigar QUIÉN
            if (!empty($matchingKeys1SecondAfter)) {
                Log::warning('⚠️ ¡CACHE REGENERADO AUTOMÁTICAMENTE!', [
                    'keys_regeneradas' => $matchingKeys1SecondAfter,
                    'posibles_causas' => [
                        '1. Middleware de cache',
                        '2. Evento/Listener después de la request',
                        '3. Job en queue',
                        '4. Scheduled task',
                        '5. PHP OPcache manteniendo valores',
                    ],
                ]);
            }

                // 6. Debug adicional
                $this->debugRegenerationSource($matchingKeys1SecondAfter[0]);

            } else {
                Log::warning('No keys found with pattern: ' . $prefix . '*');

                $allKeys = $redis->keys('*');
                Log::info('All keys in Redis', [
                    'total' => count($allKeys),
                    'keys' => $allKeys,
                ]);

            }
            Log::info('🏁 === FIN CLEAR PREFIX ===', [
                'timestamp' => now()->toISOString(),
                'duration' => microtime(true) - LARAVEL_START,
            ]);
    }

    private function debugRegenerationSource(string $regeneratedKey): void
    {
        Log::info('🔬 INVESTIGANDO REGENERACIÓN DE CACHE', [
            'key_regenerada' => $regeneratedKey,
            'investigacion_pasos' => [
                '1. Buscar en código "rememberForever" con esta key',
                '2. Revisar controllers que usen esta ruta',
                '3. Verificar eventos del modelo User',
                '4. Revisar que no haya middleware global',
            ],
        ]);

        // Intentar identificar de dónde viene
        $keyParts = explode(':', $regeneratedKey);
        $possibleCacheKey = end($keyParts); // Última parte

        Log::info('🧩 ANÁLISIS DE KEY', [
            'key_completa' => $regeneratedKey,
            'partes' => $keyParts,
            'posible_cache_key' => $possibleCacheKey,
            'parece_ser' => str_contains($regeneratedKey, 'admin:users') ? 'Cache de usuarios admin' : 'Desconocido',
        ]);

        // Sugerir búsquedas en código
        $searchTerms = [
            'admin:users',
            'users:all',
            'page:1:15',
            'rememberForever',
            'Cache::',
        ];

        Log::info('🔍 TÉRMINOS PARA BUSCAR EN CÓDIGO', [
            'terms' => $searchTerms,
            'suggestion' => 'Busca estos términos en tu IDE o con grep',
        ]);
    }


    public function clearKey(string $prefixKey, string $suffix): void
    {
        $prefix = config("cache-prefixes.$prefixKey");

        $fullPattern = rtrim($prefix, ':') . ':' . ltrim($suffix, ':');

        // Y asegúrate que TERMINA con ':' para el patrón
        if (!str_ends_with($fullPattern, ':')) {
            $fullPattern = $fullPattern . ':';
        }

        Log::info('clearKey pattern', [
            'full_pattern' => $fullPattern,
            'will_search' => $fullPattern . '*',
            'expected_to_match' => 'laravel-database-laravel-cache-admin:users:all:page:1:15:all',
        ]);

        $this->clearPrefix($fullPattern);
    }

    public function clearStaffCache(): void
    {

        $suffixes = [
            StaffCacheSufix::DASHBOARD->value . ":*",
            StaffCacheSufix::DEBTS->value . ":*",
            StaffCacheSufix::PAYMENTS->value . ":*",
            StaffCacheSufix::STUDENTS->value . ":*",
        ];

        foreach ($suffixes as $suffix) {
            $this->clearKey(CachePrefix::STAFF->value, $suffix);
        }
    }

    public function clearStudentCache(int $userId):void
    {
        $suffixes = [
            StudentCacheSufix::DASHBOARD_USER->value . ":*:$userId",
            StudentCacheSufix::PENDING->value . ":*:$userId",
            StudentCacheSufix::HISTORY->value . ":$userId"
        ];

        foreach ($suffixes as $suffix) {
            $this->clearKey(CachePrefix::STAFF->value, $suffix);
        }
    }

    public function clearParentCache(int $parentId):void
    {
        $suffixes=[
            ParentCacheSufix::CHILDREN->value . ":$parentId",
        ];
        foreach($suffixes as $suffix)
        {
            $this->clearKey(CachePrefix::PARENT->value, $suffix);
        }
    }

    public function clearCacheWhileConceptChangeStatus(int $userId, PaymentConceptStatus $conceptStatus): void
    {
        $studentSuffixes = match($conceptStatus) {
            PaymentConceptStatus::ACTIVO => [
                StudentCacheSufix::PENDING->value . ":*:$userId",
                StudentCacheSufix::DASHBOARD_USER->value . ":pending:$userId",
            ],
            PaymentConceptStatus::FINALIZADO => [
                StudentCacheSufix::PENDING->value . ":*:$userId",
                StudentCacheSufix::DASHBOARD_USER->value . ":overdue:$userId",
            ],
            PaymentConceptStatus::ELIMINADO,
            PaymentConceptStatus::DESACTIVADO => [
                StudentCacheSufix::PENDING->value . ":*:$userId",
                StudentCacheSufix::DASHBOARD_USER->value . ":*:$userId",
            ],
        };

        foreach ($studentSuffixes as $suffix) {
            $this->clearKey(CachePrefix::STUDENT->value, $suffix);
        }


        $staffSuffixes = [
            StaffCacheSufix::DASHBOARD->value . ":pending",
            StaffCacheSufix::DASHBOARD->value . ":concepts",
            StaffCacheSufix::DEBTS->value . ":pending",
            StaffCacheSufix::STUDENTS->value . ":*",
        ];

        foreach ($staffSuffixes as $suffix) {
            $this->clearKey(CachePrefix::STAFF->value, $suffix);
        }
    }
}
