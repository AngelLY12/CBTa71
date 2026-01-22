<?php

namespace App\Core\Application\DTO\Response\User;


use App\Core\Application\DTO\Response\StudentDetail\StudentDetailDTO;


/**
 * @OA\Schema(
 *     schema="UserExtraDataResponse",
 *     description="Respuesta con información adicional detallada del usuario",
 *     type="object",
 *     @OA\Property(
 *         property="basicInfo",
 *         type="object",
 *         description="Información básica adicional del usuario",
 *         @OA\Property(
 *             property="curp",
 *             type="string",
 *             description="CURP del usuario",
 *             example="MAGJ940528HDFRRN09"
 *         ),
 *         @OA\Property(
 *             property="phone_number",
 *             type="string",
 *             description="Número de teléfono",
 *             example="+5215512345678"
 *         ),
 *         @OA\Property(
 *             property="address",
 *             type="string",
 *             description="Dirección del usuario",
 *             example="Calle Principal #123, Col. Centro, CDMX"
 *         ),
 *         @OA\Property(
 *             property="blood_type",
 *             type="string",
 *             description="Tipo de sangre",
 *             example="O+"
 *         )
 *     ),
 *     @OA\Property(
 *         property="roles",
 *         type="array",
 *         description="Roles asignados al usuario",
 *         @OA\Items(type="string", example="student")
 *     ),
 *     @OA\Property(
 *         property="permissions",
 *         type="array",
 *         description="Permisos directos del usuario",
 *         @OA\Items(type="string", example="view_grades")
 *     ),
 *     @OA\Property(
 *         property="studentDetail",
 *         ref="#/components/schemas/StudentDetailDTO",
 *         description="Información detallada del estudiante (solo si es estudiante)",
 *         nullable=true
 *     )
 * )
 */
class UserExtraDataResponse
{
    public function __construct(
        public readonly array $basicInfo,
        public readonly array $roles,
        public readonly array $permissions,
        public readonly ?StudentDetailDTO $studentDetail,
    ){}

}
