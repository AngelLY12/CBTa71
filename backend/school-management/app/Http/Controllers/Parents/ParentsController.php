<?php

namespace App\Http\Controllers\Parents;

use App\Core\Application\Services\Parents\ParentsServiceFacades;
use App\Http\Controllers\Controller;
use App\Http\Requests\Parents\AcceptInviteRequest;
use App\Http\Requests\Parents\SendInviteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Parents",
 *     description="Endpoints para invitar y aceptar padres de alumnos"
 * )
 */
class ParentsController extends Controller
{
    public function __construct(
        private ParentsServiceFacades $parentsFacade
    ) {}
    /**
     * Enviar invitación a un padre para asociarlo con un estudiante
     *
     * @OA\Post(
     *     path="/api/parents/invite",
     *     summary="Enviar invitación a un padre",
     *     tags={"Parents"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SendInviteRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Invitación enviada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Invitation enviada con exito"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token", type="string", example="uuid-token"),
     *                 @OA\Property(property="expires_at", type="string", format="date-time", example="2025-11-27T12:34:56Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="El correo es obligatorio")
     *         )
     *     )
     * )
     */
    public function sendInvitation(SendInviteRequest $request)
    {
        $user=Auth::user();
        $invite = $this->parentsFacade->sendInvitation(
            studentId: $request->validated()['student_id'],
            parentEmail: $request->validated()['parent_email'],
            createdBy: $user->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Invitation enviada con exito',
            'data' => [
                'token' => $invite->token,
                'expires_at' => $invite->expiresAt,
            ]
        ], 201);


    }

    /**
     * Aceptar invitación de un padre
     *
     * @OA\Post(
     *     path="/api/parents/invite/accept",
     *     summary="Aceptar invitación de un padre",
     *     tags={"Parents"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AcceptInviteRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invitación aceptada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="La invitación ha sido aceptada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="El token es obligatorio")
     *         )
     *     )
     * )
     */
    public function acceptInvitation(AcceptInviteRequest $request)
    {
        $user=Auth::user();
        $this->parentsFacade->acceptInvitation(
            token: $request->validated()['token'],
            relationship: $request->validated()['relationship'] ?? null,
            userId:$user->id
        );

        return response()->json([
            'success' => true,
            'message' => 'La invitación ha sido aceptada'
        ], 200);
    }

    /**
     * Aceptar invitación de un padre
     *
     * @OA\Get(
     *     path="/api/parents/get-children",
     *     summary="Obtiene los hijos del parent",
     *     tags={"Parents"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del parent",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="cards",
     *                     type="array",
     *                     description="Lista de métodos de pago del usuario",
     *                     @OA\Items(ref="#/components/schemas/ParentChildrenResponse")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Datos obtenidos")
     *         )
     *     ),
     *
     * )
     */
    public function getParetChildren(int $id)
    {
        $childrenData=$this->parentsFacade->getParentChildren($id);
        return response()->json([
            'success' => true,
            'data' => ['children' => $childrenData],
            'message' => 'Datos obtenidos'
        ], 200);
    }
}
