<?php

namespace App\Core\Application\DTO\Request\User;


/**
 * @OA\Schema(
 *     schema="UpdateUserPermissionsDTO",
 *     type="object",
 *     @OA\Property(
 *         property="curps",
 *         type="array",
 *         description="Lista de CURPs de los usuarios a actualizar",
 *         @OA\Items(type="string"),
 *         example={"XAXX010101HNEXXXA","XEXX010101HNEXXXB"}
 *     ),
 *     @OA\Property(
 *         property="permissionsToAdd",
 *         type="array",
 *         description="Lista de permisos a agregar a los usuarios",
 *         @OA\Items(type="string"),
 *         example={"find user","find concept"}
 *     ),
 *     @OA\Property(
 *         property="permissionsToRemove",
 *         type="array",
 *         description="Lista de permisos a remover de los usuarios",
 *         @OA\Items(type="string"),
 *         example={"delete payment"}
 *     )
 * )
 */

class UpdateUserPermissionsDTO{
    public function __construct(
        public readonly array $curps =[],
        public readonly array $permissionsToAdd = [],
        public readonly array $permissionsToRemove = []
    )
    {

    }
}
