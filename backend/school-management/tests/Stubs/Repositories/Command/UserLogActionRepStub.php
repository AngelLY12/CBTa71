<?php

namespace Tests\Stubs\Repositories\Command;

use App\Core\Domain\Repositories\Command\User\UserLogActionRepInterface;
use App\Core\Domain\Entities\UserActionLog;

class UserLogActionRepStub implements UserLogActionRepInterface
{
    private bool $throwDatabaseError = false;
    private array $logs = [];
    private int $nextId = 1;

    public function __construct()
    {
        $this->initializeTestData();
    }

    private function initializeTestData(): void
    {
        // Logs de prueba iniciales
        $this->logs = [
            $this->createStubLog('GET', '/api/users', ['admin'], 1, '192.168.1.1', '2024-01-01'), // Viejo
            $this->createStubLog('POST', '/api/login', null, 2, '10.0.0.1', '2024-06-01'), // Reciente
            $this->createStubLog('PUT', '/api/users/1', ['user'], 1, '127.0.0.1', '2024-02-15'), // Viejo
            $this->createStubLog('DELETE', '/api/posts/5', ['admin', 'editor'], 3, '172.16.0.1', '2024-07-01'), // Reciente
        ];
        $this->nextId = 5;
    }

    private function createStubLog(string $method, string $url, ?array $roles, ?int $userId, ?string $ip, string $createdAt): array
    {
        return [
            'id' => $this->nextId++,
            'method' => $method,
            'url' => $url,
            'roles' => $roles,
            'user_id' => $userId,
            'ip' => $ip,
            'created_at' => $createdAt,
            'log_object' => new UserActionLog(
                method:$method,
                url:$url,
                roles:$roles,
                userId: $userId,
                ip: $ip
            )
        ];
    }

    public function create(UserActionLog $log): UserActionLog
    {
        if ($this->throwDatabaseError) {
            throw new \RuntimeException('Database error');
        }

        $logData = [
            'id' => $this->nextId++,
            'method' => $log->method,
            'url' => $log->url,
            'roles' => $log->roles,
            'user_id' => $log->userId,
            'ip' => $log->ip,
            'created_at' => date('Y-m-d H:i:s'),
            'log_object' => $log
        ];

        $this->logs[] = $logData;

        return $log;
    }

    public function deleteOlderLogs(): int
    {
        if ($this->throwDatabaseError) {
            throw new \RuntimeException('Database error');
        }

        $deletedCount = 0;
        $threeMonthsAgo = date('Y-m-d', strtotime('-3 months'));

        foreach ($this->logs as $key => $log) {
            if ($log['created_at'] < $threeMonthsAgo) {
                unset($this->logs[$key]);
                $deletedCount++;
            }
        }

        // Reindexar array
        $this->logs = array_values($this->logs);

        return $deletedCount;
    }

    // Métodos de configuración para pruebas

    public function shouldThrowDatabaseError(bool $throw = true): self
    {
        $this->throwDatabaseError = $throw;
        return $this;
    }

    public function addLog(UserActionLog $log, ?string $createdAt = null): self
    {
        $logData = [
            'id' => $this->nextId++,
            'method' => $log->method,
            'url' => $log->url,
            'roles' => $log->roles,
            'user_id' => $log->userId,
            'ip' => $log->ip,
            'created_at' => $createdAt ?? date('Y-m-d H:i:s'),
            'log_object' => $log
        ];

        $this->logs[] = $logData;
        return $this;
    }

    public function getLog(int $index): ?UserActionLog
    {
        return $this->logs[$index]['log_object'] ?? null;
    }

    public function getLogsCount(): int
    {
        return count($this->logs);
    }

    public function clearLogs(): self
    {
        $this->logs = [];
        $this->nextId = 1;
        return $this;
    }

    public function getAllLogs(): array
    {
        return array_map(function($logData) {
            return $logData['log_object'];
        }, $this->logs);
    }

    public function getLogsByUser(int $userId): array
    {
        return array_filter($this->logs, function($logData) use ($userId) {
            return $logData['user_id'] === $userId;
        });
    }

    public function getLogsByMethod(string $method): array
    {
        return array_filter($this->logs, function($logData) use ($method) {
            return $logData['method'] === $method;
        });
    }
}
