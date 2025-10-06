<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\PaymentSystem\Staff\StudentsService;
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
        $search = $request->query('search');

        $students = $this->studentsService->showAllStudents($search);

        return response()->json([
            'success' => true,
            'data' => $students,
            'message' => $students->isEmpty() ? 'No hay estudiantes registrados.':null
        ]);
    }
}
