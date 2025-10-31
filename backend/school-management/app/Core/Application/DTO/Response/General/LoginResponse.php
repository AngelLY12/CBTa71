<?php

namespace App\Core\Application\DTO\Response\General;

class LoginResponse
{
    public function __construct(
        public readonly ?string $access_token,
        public readonly ?string $refresh_token,
        public readonly ?string $token_type,
    )
    {

    }
}
