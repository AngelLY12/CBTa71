<?php

namespace App\Core\Application\DTO\Response\User;

/**
 * @OA\Schema(
 *     schema="UserListItemResponse",
 *     description="Respuesta con información básica de usuario para listados",
 *     type="object",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID único del usuario",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="fullName",
 *         type="string",
 *         description="Nombre completo del usuario (nombre + apellido)",
 *         example="Juan Pérez"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Correo electrónico del usuario",
 *         example="juan.perez@example.com"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Estado del usuario",
 *         enum={"active", "inactive", "pending", "suspended"},
 *         example="active"
 *     ),
 *     @OA\Property(
 *         property="roles_count",
 *         type="integer",
 *         description="Cantidad de roles asignados al usuario",
 *         minimum=0,
 *         example=2
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha y hora de creación en formato Y-m-d H:i:s",
 *         example="2024-01-22 10:30:45"
 *     ),
 *     @OA\Property(
 *         property="createdAtHuman",
 *         type="string",
 *         description="Fecha de creación en formato humano relativo",
 *         example="hace 2 horas"
 *     )
 * )
 */
class UserListItemResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $fullName,
        public readonly string $email,
        public readonly string $status,
        public readonly int $roles_count,
        public readonly string $created_at,
        public readonly string $createdAtHuman
    ){}

}
