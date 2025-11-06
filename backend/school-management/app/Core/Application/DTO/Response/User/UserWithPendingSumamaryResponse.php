<?php

namespace App\Core\Application\DTO\Response\User;

/**
 * @OA\Schema(
 *     schema="UserWithPendingSumamaryResponse",
 *     type="object",
 *     @OA\Property(property="userId", type="integer", nullable=true, description="ID del usuario", example=1),
 *     @OA\Property(property="fullName", type="string", nullable=true, description="Nombre completo del usuario", example="Juan Pérez"),
 *     @OA\Property(property="semestre", type="integer", nullable=true, description="Semestre del estudiante", example=5),
 *     @OA\Property(property="career_name", type="string", nullable=true, description="Nombre de la carrera del estudiante", example="Ingeniería en Sistemas"),
 *     @OA\Property(property="num_pending", type="integer", nullable=true, description="Número de conceptos pendientes", example=3),
 *     @OA\Property(property="total_amount_pending", type="string", nullable=true, description="Monto total pendiente", example="4500.00")
 * )
 */
class UserWithPendingSumamaryResponse{
    public function __construct(
        public readonly ?int $userId,
        public readonly ?string $fullName,
        public readonly ?int $semestre,
        public readonly ?string $career_name,
        public readonly ?int $num_pending,
        public readonly ?string $total_amount_pending
    )
    {
    }
}
