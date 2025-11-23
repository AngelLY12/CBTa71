<?php

namespace App\Http\Controllers\Staff;

use App\Core\Application\Services\Payments\Staff\StudentsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\Staff\PaginationWithSearchRequest;

/**
 * @OA\Tag(
 *     name="Students",
 *     description="Endpoints para la gestión y consulta de estudiantes registrados"
 * )
 */
class StudentsController extends Controller
{
    protected StudentsService $studentsService;

    public function __construct(StudentsService $studentsService)
    {
        $this->studentsService= $studentsService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/students",
     *     tags={"Students"},
     *     summary="Obtener lista de alumnos",
     *     description="Devuelve una lista paginada de estudiantes registrados, con opción de búsqueda por email, CURP o número de control, y posibilidad de forzar actualización del caché.",
     *     operationId="getStudents",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Filtro opcional para buscar por email, CURP o número de control del estudiante.",
     *         required=false,
     *         @OA\Schema(type="string", example="PERA020804MSHPNXA8")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Número de resultados por página (por defecto 15).",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página actual (por defecto 1).",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Forzar actualización de caché (true o false).",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Lista de pagos obtenida exitosamente.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="payments",
     *                     allOf={
     *                         @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
     *                         @OA\Schema(
     *                             @OA\Property(
     *                                 property="items",
     *                                 type="array",
     *                                 @OA\Items(ref="#/components/schemas/UserWithPendingSumamaryResponse")
     *                             )
     *                         )
     *                     }
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", nullable=true, example="No hay estudiantes registrados.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación en los parámetros enviados."
     *     ),
     *      @OA\Response(
     *         response=409,
     *         description="Recurso no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor."
     *     )
     * )
     */
    public function index(PaginationWithSearchRequest $request)
    {
        $search = $request->validated()['search'] ?? null;
        $perPage = $request->validated()['perPage'] ?? 15;
        $page = $request->validated()['page'] ?? 1;
        $forceRefresh = $request->validated()['forceRefresh'] ?? false;
        $students = $this->studentsService->showAllStudents($search, $perPage, $page, $forceRefresh);

        return response()->json([
            'success' => true,
            'data' => ['students'=>$students],
            'message' => empty($students) ? 'No hay estudiantes registrados.':null
        ]);
    }
}
