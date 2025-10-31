<?php

namespace App\Http\Controllers\Staff;

use App\Core\Application\Services\Payments\Staff\StudentsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentsController extends Controller
{
    protected StudentsService $studentsService;

    public function __construct(StudentsService $studentsService)
    {
        $this->studentsService= $studentsService;
    }

    /**
     * Display a listing of the resource.
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
