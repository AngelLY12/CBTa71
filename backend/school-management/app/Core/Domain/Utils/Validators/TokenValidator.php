<?php

namespace App\Core\Domain\Utils\Validators;

use App\Core\Domain\Entities\RefreshToken;
use App\Exceptions\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;

class TokenValidator
{
    public static function ensureIsTokenValid(RefreshToken $token)
    {
         if (!$token || !$token->isValid()) {
            throw new UnauthorizedException(403,"Refresh token inválido");

        }
    }
}
