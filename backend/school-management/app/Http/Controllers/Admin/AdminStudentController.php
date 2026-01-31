<?php

namespace App\Http\Controllers\Admin;

use App\Core\Application\Mappers\StudentDetailMapper;
use App\Core\Application\Services\Admin\AdminStudentServiceFacades;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttachStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Http\Requests\General\ImportRequest;
use App\Imports\StudentDetailsImport;
use App\Jobs\PromoteStudentsJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

/**
 * @OA\Tag(
 *     name="Admin",
 *     description="Endpoints para gestión administrativa (asignación e importación de usuarios)"
 * )
 */
class AdminStudentController extends Controller
{
    private AdminStudentServiceFacades $service;

    public function __construct(AdminStudentServiceFacades $service)
    {
        $this->service= $service;
    }

    public function attachStudent(AttachStudentRequest $request)
    {
        $data= $request->validated();
        $attachUser = StudentDetailMapper::toCreateStudentDetailDTO($data);

        $user = $this->service->attachStudentDetail($attachUser);

        return Response::success(['user' => $user], 'Se asociaron correctamente los datos al estudiante.', 201);


    }

    public function findStudentDetail(int $id)
    {
        $details= $this->service->findStudentDetail($id);
        return Response::success(['student_details' => $details]);
    }

    public function updateStudentDetail(UpdateStudentRequest $request, int $id)
    {
        $data=$request->validated();
        $userUpdate= $this->service->updateStudentDetail($id,$data);
        return Response::success(['user' => $userUpdate], 'Se actualizaron correctamente los detalles de estudiante.');

    }

    public function promotionStudents()
    {
        PromoteStudentsJob::dispatch(Auth::id())->onQueue('maintenance-heavy');
        return Response::success(null, 'Promoción de estudiantes iniciada en segundo plano.');
    }

    public function importStudents(ImportRequest $request)
    {
        $file= $request->file('file');
        $fileName = 'import_' . time() . '_' . Str::random(10) . '.xlsx';
        $filePath = Storage::disk('local')->putFileAs('imports/temp', $file, $fileName);
        $fullPath = storage_path('app/' . $filePath);
        $import= new StudentDetailsImport($this->service, Auth::user(), $fullPath);
        Excel::queueImport($import,$fullPath)->onQueue('imports');
        return Response::success(null, 'Usuarios procesandose, se te notificara cuando termine.');
    }
}
