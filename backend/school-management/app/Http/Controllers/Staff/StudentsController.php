<?php

namespace App\Http\Controllers\Staff;

use App\Core\Application\Services\Payments\Staff\StudentsService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payments\Staff\PaginationWithSearchRequest;
use Illuminate\Support\Facades\Response;

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

    
    public function index(PaginationWithSearchRequest $request)
    {
        $search = $request->validated()['search'] ?? null;
        $perPage = $request->validated()['perPage'] ?? 15;
        $page = $request->validated()['page'] ?? 1;
        $forceRefresh = $request->validated()['forceRefresh'] ?? false;
        $students = $this->studentsService->showAllStudents($search, $perPage, $page, $forceRefresh);

        return Response::success(
            ['students' => $students],
            empty($students->items) ? 'No hay estudiantes registrados.' : null
        );
    }
}
