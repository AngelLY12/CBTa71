<?php

namespace App\Core\Application\DTO\Response\General;

/**
 * @OA\Schema(
 *     schema="LoginResponse",
 *     type="object",
 *     @OA\Property(
 *         property="access_token",
 *         type="string",
 *         nullable=true,
 *         description="Token de acceso JWT",
 *         example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
 *     ),
 *     @OA\Property(
 *         property="refresh_token",
 *         type="string",
 *         nullable=true,
 *         description="Token para refrescar el access_token",
 *         example="dGhpcy1pcy1hLXJlZnJlc2gtdG9rZW4="
 *     ),
 *     @OA\Property(
 *         property="token_type",
 *         type="string",
 *         nullable=true,
 *         description="Tipo de token, normalmente 'bearer'",
 *         example="bearer"
 *     )
 * )
 */
class LoginResponse
{
    public function __construct(
        public readonly ?string $access_token,
        public readonly ?string $refresh_token,
        public readonly ?string $token_type,
        public readonly ?array $user_data
    )
    {

    }
}
