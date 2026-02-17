<?php

namespace Tests\Unit\Domain\Entities;

use Tests\Unit\Domain\BaseDomainTestCase;
use App\Core\Domain\Entities\UserActionLog;
use PHPUnit\Framework\Attributes\Test;

class UserActionLogTest extends BaseDomainTestCase
{
    #[Test]
    public function it_can_be_instantiated()
    {
        $userActionLog = new UserActionLog(
            method: 'GET',
            url: '/api/users'
        );

        $this->assertInstanceOf(UserActionLog::class, $userActionLog);
    }

    #[Test]
    public function it_can_be_instantiated_with_all_parameters()
    {
        $userActionLog = new UserActionLog(
            method: 'POST',
            url: '/api/users',
            userId: 1,
            roles: ['admin', 'user'],
            ip: '192.168.1.1'
        );

        $this->assertInstanceOf(UserActionLog::class, $userActionLog);
        $this->assertEquals('POST', $userActionLog->method);
        $this->assertEquals('/api/users', $userActionLog->url);
        $this->assertEquals(1, $userActionLog->userId);
        $this->assertEquals(['admin', 'user'], $userActionLog->roles);
        $this->assertEquals('192.168.1.1', $userActionLog->ip);
    }

    #[Test]
    public function it_has_required_attributes()
    {
        $userActionLog = new UserActionLog(
            method: 'PUT',
            url: '/api/users/1'
        );

        $this->assertEquals('PUT', $userActionLog->method);
        $this->assertEquals('/api/users/1', $userActionLog->url);
        $this->assertNull($userActionLog->userId);
        $this->assertEquals([], $userActionLog->roles);
        $this->assertNull($userActionLog->ip);
    }

    #[Test]
    public function it_accepts_valid_data()
    {
        $userActionLog = new UserActionLog(
            method: 'DELETE',
            url: '/api/users/1',
            userId: 123,
            roles: ['super_admin'],
            ip: '10.0.0.1'
        );

        $this->assertInstanceOf(UserActionLog::class, $userActionLog);
        $this->assertEquals('DELETE', $userActionLog->method);
        $this->assertEquals('/api/users/1', $userActionLog->url);
        $this->assertEquals(123, $userActionLog->userId);
        $this->assertEquals(['super_admin'], $userActionLog->roles);
        $this->assertEquals('10.0.0.1', $userActionLog->ip);
    }

    #[Test]
    public function it_sets_default_values_for_optional_parameters()
    {
        $userActionLog = new UserActionLog(
            method: 'GET',
            url: '/'
        );

        $this->assertNull($userActionLog->userId);
        $this->assertEquals([], $userActionLog->roles);
        $this->assertNull($userActionLog->ip);
    }

    #[Test]
    public function it_can_be_serialized_to_array_with_all_data()
    {
        $userActionLog = new UserActionLog(
            method: 'PATCH',
            url: '/api/users/1',
            userId: 42,
            roles: ['editor'],
            ip: '172.16.0.1'
        );

        $array = $userActionLog->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('userId', $array);
        $this->assertArrayHasKey('roles', $array);
        $this->assertArrayHasKey('ip', $array);
        $this->assertArrayHasKey('method', $array);
        $this->assertArrayHasKey('url', $array);

        $this->assertEquals(42, $array['userId']);
        $this->assertEquals(['editor'], $array['roles']);
        $this->assertEquals('172.16.0.1', $array['ip']);
        $this->assertEquals('PATCH', $array['method']);
        $this->assertEquals('/api/users/1', $array['url']);
    }

    #[Test]
    public function it_can_be_serialized_to_array_with_minimal_data()
    {
        $userActionLog = new UserActionLog(
            method: 'GET',
            url: '/api/health'
        );

        $array = $userActionLog->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('userId', $array);
        $this->assertArrayHasKey('roles', $array);
        $this->assertArrayHasKey('ip', $array);
        $this->assertArrayHasKey('method', $array);
        $this->assertArrayHasKey('url', $array);

        $this->assertNull($array['userId']);
        $this->assertEquals([], $array['roles']);
        $this->assertNull($array['ip']);
        $this->assertEquals('GET', $array['method']);
        $this->assertEquals('/api/health', $array['url']);
    }

    #[Test]
    public function it_can_be_converted_to_json()
    {
        $userActionLog = new UserActionLog(
            method: 'POST',
            url: '/api/auth/login',
            userId: 99,
            roles: ['guest'],
            ip: '127.0.0.1'
        );

        $json = json_encode($userActionLog);

        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertEquals(99, $decoded['userId']);
        $this->assertEquals(['guest'], $decoded['roles']);
        $this->assertEquals('127.0.0.1', $decoded['ip']);
        $this->assertEquals('POST', $decoded['method']);
        $this->assertEquals('/api/auth/login', $decoded['url']);
    }

    #[Test]
    public function it_accepts_json_serialization_with_null_values()
    {
        $userActionLog = new UserActionLog(
            method: 'OPTIONS',
            url: '/api/users'
        );

        $json = json_encode($userActionLog);
        $decoded = json_decode($json, true);

        $this->assertNull($decoded['userId']);
        $this->assertEquals([], $decoded['roles']);
        $this->assertNull($decoded['ip']);
        $this->assertEquals('OPTIONS', $decoded['method']);
        $this->assertEquals('/api/users', $decoded['url']);
    }

    #[Test]
    public function it_accepts_all_http_methods()
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];

        foreach ($methods as $method) {
            $userActionLog = new UserActionLog(
                method: $method,
                url: '/api/test'
            );

            $this->assertEquals($method, $userActionLog->method);
        }
    }

    #[Test]
    public function it_accepts_various_url_formats()
    {
        $urls = [
            '/',
            '/api/users',
            '/api/users/1',
            '/api/users?page=1&limit=10',
            '/api/auth/login',
            '/admin/dashboard'
        ];

        foreach ($urls as $url) {
            $userActionLog = new UserActionLog(
                method: 'GET',
                url: $url
            );

            $this->assertEquals($url, $userActionLog->url);
        }
    }

    #[Test]
    public function it_handles_empty_roles_array()
    {
        $userActionLog = new UserActionLog(
            method: 'GET',
            url: '/api/users',
            roles: []
        );

        $this->assertEquals([], $userActionLog->roles);
        $this->assertEquals([], $userActionLog->toArray()['roles']);
    }

    #[Test]
    public function it_handles_multiple_roles()
    {
        $roles = ['admin', 'user', 'moderator', 'editor'];

        $userActionLog = new UserActionLog(
            method: 'GET',
            url: '/api/users',
            roles: $roles
        );

        $this->assertEquals($roles, $userActionLog->roles);
        $this->assertEquals($roles, $userActionLog->toArray()['roles']);
    }

    #[Test]
    public function it_accepts_null_user_id()
    {
        $userActionLog = new UserActionLog(
            method: 'GET',
            url: '/api/public',
            userId: null
        );

        $this->assertNull($userActionLog->userId);
        $this->assertNull($userActionLog->toArray()['userId']);
    }

    #[Test]
    public function it_accepts_null_ip_address()
    {
        $userActionLog = new UserActionLog(
            method: 'GET',
            url: '/api/public',
            ip: null
        );

        $this->assertNull($userActionLog->ip);
        $this->assertNull($userActionLog->toArray()['ip']);
    }

    #[Test]
    public function it_accepts_ipv6_address()
    {
        $userActionLog = new UserActionLog(
            method: 'GET',
            url: '/api/users',
            ip: '2001:0db8:85a3:0000:0000:8a2e:0370:7334'
        );

        $this->assertEquals('2001:0db8:85a3:0000:0000:8a2e:0370:7334', $userActionLog->ip);
    }
}
