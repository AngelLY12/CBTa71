<?php

namespace App\Http\Controllers;

use App\Core\Application\Services\UpdateUserServiceFacades;
use App\Core\Infraestructure\Mappers\UserMapper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


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
     *     path="/api/v1/users/{id}",
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
     *         @OA\JsonContent(ref="#/components/schemas/DomainUser")
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
    public function update(Request $request, int $userId)
    {
        $data = $request->only([
            'name',
            'last_name',
            'email',
            'phone_number',
            'birthdate',
            'gender',
            'address',
            'blood_type',
        ]);
        $rules = [
            'name' => 'required|string',
            'last_name'  => 'required|string',
            'email' => 'sometimes|email|unique:users,email,' . $userId,
            'phone_number'  => 'required|string',
            'birthdate' => 'sometimes|required|date',
            'gender' => 'sometimes|required|string',
            'address' => 'sometimes|required|array',
            'blood_type' => 'sometimes|required|string',
        ];

        $validator = Validator::make($data,$rules);
        if($validator->fails()){
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => 'Error en la validación de datos.'
            ], 422);
        }
        $updated=$this->service->updateUser($userId,$data);

        return response()->json([
            'success' => true,
            'data' => ['user'=>$updated],
            'message' => 'El usuario ha sido actualizado con éxito.',
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/users/{id}/password",
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
     *             required={"password", "password_confirmation"},
     *             @OA\Property(property="password", type="string", minLength=8, example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", example="newpassword123")
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
    public function updatePassword(Request $request, int $userId)
    {
        $data = $request->only([
            'password'
        ]);
        $rules=['required|string|min:8|confirmed'];
        $validator = Validator::make($data,$rules);
        if($validator->fails()){
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => 'Error en la validación de datos.'
            ], 422);
        }
        $updated = $this->service->updatePassword($userId, $data['password']);

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Password actualizada con éxito',
        ]);
    }
}
