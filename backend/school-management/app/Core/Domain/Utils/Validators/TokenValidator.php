<?php

namespace App\Core\Domain\Utils\Validators;

use App\Core\Domain\Entities\RefreshToken;
use Illuminate\Auth\AuthenticationException;

class TokenValidator
{
    public static function ensureIsTokenValid(RefreshToken $token)
    {
         if (!$token || !$token->isValid()) {
            throw new AuthenticationException("Refresh token inválido");

        }
    }
}
