<?php

namespace App\Core\Application\DTO\Response\User;

class UserWithUpdatedPermissionsResponse
{
    public function __construct(
        public readonly string $fullName,
        public readonly string $email,
        public readonly array $updatedPermissions
    )
    {

    }
}
