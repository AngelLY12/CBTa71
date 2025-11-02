<?php

namespace App\Http\Controllers\Staff;

use App\Core\Application\Services\Payments\Staff\StudentsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
     *     summary="Obtener lista de estudiantes",
     *     description="Devuelve una lista paginada de estudiantes registrados. Permite buscar, filtrar y forzar la actualización del caché.",
     *     operationId="getStudents",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Filtro de búsqueda opcional (por nombre, matrícula, correo, etc.)",
     *         required=false,
     *         @OA\Schema(type="string", example="María López")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Cantidad de resultados por página (por defecto 15)",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página (por defecto 1)",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Forzar actualización de caché (true o false)",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de estudiantes obtenida exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="students", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="user_id", type="integer", example=1),
     *                         @OA\Property(property="fullName", type="string", example="María López"),
     *                         @OA\Property(property="semestre", type="integer", example=9),
     *                         @OA\Property(property="career_name", type="string", example="Matematicas"),
     *                         @OA\Property(property="num_pending", type="integer", example=2),
     *                         @OA\Property(property="total_amount_pending", type="integer", example=2500)
     *
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", nullable=true, example="No hay estudiantes registrados.")
     *         )
     *     ),
     *
     *     security={{"bearerAuth":{}}}
     * )
     */
    public function index(Request $request)
    {
        $search = $request->query('search', null);
        $perPage = $request->query('perPage', 15);
        $page    = $request->query('page', 1);
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);
        $students = $this->studentsService->showAllStudents($search, $perPage, $page, $forceRefresh);

        return response()->json([
            'success' => true,
            'data' => ['students'=>$students],
            'message' => empty($students) ? 'No hay estudiantes registrados.':null
        ]);
    }
}
