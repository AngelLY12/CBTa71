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

    public function update(UpdateUserRequest $request)
    {
        $userId=Auth::id();
        $data=$request->validated();
        $updated=$this->service->updateUser($userId,$data);

        return Response::success(['user' => $updated], 'El usuario ha sido actualizado con éxito.');

    }

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
