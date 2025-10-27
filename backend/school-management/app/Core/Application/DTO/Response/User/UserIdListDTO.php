<?php

namespace App\Core\Application\DTO\Response\User;

class UserIdListDTO{
    public function __construct(
        public readonly ?array $userIds
    ) {}
}
