<?php

namespace App\Http\Controllers;

use App\Core\Application\Services\UpdateUserServiceFacades;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateUserRequest;


/**
 * @OA\Tag(
 *     name="Users",
 *     description="Operaciones para actualizar usuarios"
 * )
 */
class UpdateUserController extends Controller
{
    public function __construct(private UpdateUserServiceFacades $service)
    {
    }

     /**
     * @OA\Patch(
     *     path="/api/v1/users/{userId}",
     *     tags={"Users"},
     *     summary="Actualizar los datos generales de un usuario",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateUserRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario actualizado correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/DomainUser")
     *             ),
     *             @OA\Property(property="message", type="string", example="El usuario ha sido actualizado con éxito.")
     *         )
     *     )
     * )
     */
    public function update(UpdateUserRequest $request, int $userId)
    {
        $data=$request->validated();
        $updated=$this->service->updateUser($userId,$data);

        return response()->json([
            'success' => true,
            'data' => ['user'=>$updated],
            'message' => 'El usuario ha sido actualizado con éxito.',
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/users/{userId}/password",
     *     tags={"Users"},
     *     summary="Actualizar la contraseña de un usuario",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             ref="#/components/schemas/UpdatePasswordRequest"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contraseña actualizada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password actualizada con éxito")
     *         )
     *     )
     * )
     */
    public function updatePassword(UpdatePasswordRequest $request, int $userId)
    {
        $data = $request->validated();

        $updated = $this->service->updatePassword(
            $userId,
            $data['currentPassword'],
            $data['newPassword']
        );

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Password actualizada con éxito',
        ]);
    }
}
