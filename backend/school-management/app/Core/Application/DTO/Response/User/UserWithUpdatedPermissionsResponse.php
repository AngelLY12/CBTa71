<?php

namespace App\Core\Application\DTO\Response\User;

class UserWithUpdatedPermissionsResponse
{
    public function __construct(
        public readonly string $fullName,
        public readonly string $curp,
        public readonly array $updatedPermissions
    )
    {

    }
}
