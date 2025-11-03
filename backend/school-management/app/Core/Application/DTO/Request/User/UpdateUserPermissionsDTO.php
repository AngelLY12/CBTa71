<?php

namespace App\Core\Application\DTO\Request\User;

class UpdateUserPermissionsDTO{
    public function __construct(
        public readonly array $emails =[],
        public readonly array $permissionsToAdd = [],
        public readonly array $permissionsToRemove = []
    )
    {

    }
}
