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
     *     path="/parents/invite",
     *     summary="Enviar invitación a un padre",
     *     tags={"Parents"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id","parent_email"},
     *             @OA\Property(property="student_id", type="integer", example=123),
     *             @OA\Property(property="parent_email", type="string", format="email", example="parent@example.com")
     *         )
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
     *     path="/parents/invite/accept",
     *     summary="Aceptar invitación de un padre",
     *     tags={"Parents"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token"},
     *             @OA\Property(property="token", type="string", example="uuid-token"),
     *             @OA\Property(property="relationship", type="string", enum={"padre","madre","tutor","tutor_legal"}, nullable=true, example="tutor")
     *         )
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
        $this->parentsFacade->acceptInvitation(
            token: $request->validated()['token'],
            relationship: $request->validated()['relationship'] ?? null,
        );

        return response()->json([
            'success' => true,
            'message' => 'La invitación ha sido aceptada'
        ], 200);

    }
}
