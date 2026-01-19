<?php

namespace Application\Mappers;

use App\Core\Application\Mappers\UserActionLogMapper;
use App\Core\Domain\Entities\UserActionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserActionLogMapperTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_maps_user_and_request_to_user_action_log_domain_object(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->assignRole('admin');
        $user->assignRole('editor');

        $requestData = [
            'method' => 'POST',
            'url' => '/api/users',
            'ip' => '192.168.1.1',
        ];

        // Act
        $result = UserActionLogMapper::toDomain($user, $requestData);

        // Assert
        $this->assertInstanceOf(UserActionLog::class, $result);
        $this->assertEquals('POST', $result->method);
        $this->assertEquals('/api/users', $result->url);
        $this->assertContains('admin', $result->roles);
        $this->assertContains('editor', $result->roles);
        $this->assertEquals($user->id, $result->userId);
        $this->assertEquals('192.168.1.1', $result->ip);
    }


    #[Test]
    public function it_maps_when_user_has_no_roles(): void
    {
        // Arrange
        $user = User::factory()->create(); // Usuario sin roles asignados

        $requestData = [
            'method' => 'PUT',
            'url' => '/api/posts/1',
            'ip' => '127.0.0.1',
        ];

        // Act
        $result = UserActionLogMapper::toDomain($user, $requestData);

        // Assert
        $this->assertInstanceOf(UserActionLog::class, $result);
        $this->assertEquals('PUT', $result->method);
        $this->assertEquals('/api/posts/1', $result->url);
        $this->assertEquals([], $result->roles);
        $this->assertEquals($user->id, $result->userId);
        $this->assertEquals('127.0.0.1', $result->ip);
    }

    #[Test]
    public function it_handles_user_with_single_role(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $requestData = [
            'method' => 'DELETE',
            'url' => '/api/users/10',
            'ip' => '192.168.1.100',
        ];

        // Act
        $result = UserActionLogMapper::toDomain($user, $requestData);

        // Assert
        $this->assertInstanceOf(UserActionLog::class, $result);
        $this->assertEquals(['super-admin'], $result->roles);
        $this->assertEquals($user->id, $result->userId);
    }

    #[Test]
    public function it_handles_different_http_methods(): void
    {
        // Arrange
        $user = User::factory()->create();

        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'];

        foreach ($methods as $method) {
            $requestData = [
                'method' => $method,
                'url' => '/api/test',
                'ip' => '192.168.0.1',
            ];

            // Act
            $result = UserActionLogMapper::toDomain($user, $requestData);

            // Assert
            $this->assertEquals($method, $result->method);
        }
    }

    #[Test]
    public function it_handles_complex_urls_with_query_parameters(): void
    {
        // Arrange
        $user = User::factory()->create();

        $requestData = [
            'method' => 'GET',
            'url' => '/api/search?q=test&page=2&sort=desc',
            'ip' => '172.16.0.1',
        ];

        // Act
        $result = UserActionLogMapper::toDomain($user, $requestData);

        // Assert
        $this->assertEquals('/api/search?q=test&page=2&sort=desc', $result->url);
    }

    #[Test]
    public function it_handles_ipv6_addresses(): void
    {
        // Arrange
        $user = User::factory()->create();

        $requestData = [
            'method' => 'POST',
            'url' => '/api/login',
            'ip' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
        ];

        // Act
        $result = UserActionLogMapper::toDomain($user, $requestData);

        // Assert
        $this->assertEquals('2001:0db8:85a3:0000:0000:8a2e:0370:7334', $result->ip);
    }

    #[Test]
    public function it_handles_empty_or_null_ip(): void
    {
        // Arrange
        $user = User::factory()->create();

        $requestData = [
            'method' => 'GET',
            'url' => '/api/test',
            'ip' => '',
        ];

        // Act
        $result = UserActionLogMapper::toDomain($user, $requestData);

        // Assert
        $this->assertEquals('', $result->ip);
    }

    #[Test]
    public function it_handles_user_with_multiple_roles_from_factory(): void
    {
        // Arrange - Asumiendo que tienes una factory que crea usuarios con roles
        $user = User::factory()->create();
        $roles = ['admin', 'manager', 'user'];

        foreach ($roles as $role) {
            $user->assignRole($role);
        }

        $requestData = [
            'method' => 'POST',
            'url' => '/api/data',
            'ip' => '192.168.1.50',
        ];

        // Act
        $result = UserActionLogMapper::toDomain($user, $requestData);

        // Assert
        $this->assertCount(3, $result->roles);
        foreach ($roles as $role) {
            $this->assertContains($role, $result->roles);
        }
    }

}
