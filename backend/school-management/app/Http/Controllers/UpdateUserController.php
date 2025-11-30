<?php

namespace App\Http\Controllers;

use App\Core\Application\Services\User\UpdateUserServiceFacades;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="Operaciones para actualizar usuarios"
 * )
 */
class UpdateUserController extends Controller
{
    private UpdateUserServiceFacades $service;
    public function __construct( UpdateUserServiceFacades $service)
    {
        $this->service=$service;
    }

     /**
     * @OA\Patch(
     *     path="/api/v1/users/update",
     *     tags={"Users"},
     *     summary="Actualizar los datos generales de un usuario",
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
    public function update(UpdateUserRequest $request)
    {
        $userId=Auth::id();
        $data=$request->validated();
        $updated=$this->service->updateUser($userId,$data);

        return Response::success(['user' => $updated], 'El usuario ha sido actualizado con éxito.');

    }

    /**
     * @OA\Patch(
     *     path="/api/v1/users/update/password",
     *     tags={"Users"},
     *     summary="Actualizar la contraseña de un usuario",
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
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $data = $request->validated();
        $userId=Auth::id();
        $updated = $this->service->updatePassword(
            $userId,
            $data['currentPassword'],
            $data['newPassword']
        );

        return Response::success(['user'=>$updated], 'Password actualizada con éxito');

    }
}
