<?php

namespace App\Http\Controllers;

use App\Core\Application\Mappers\StudentDetailMapper;
use App\Core\Application\Services\Admin\AdminService;
use App\Http\Requests\ImportUsersRequest;
use App\Imports\UsersImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    private AdminService $service;

    public function __construct(AdminService $service)
    {
        $this->service= $service;
    }

    public function attachStudent(Request $request)
    {
        $data= $request->only([
            'user_id',
            'career_id',
            'n_control',
            'semestre',
            'group',
            'workshop'
        ]);

        $rules = [
            'user_id' => 'required|int',
            'career_id' => 'required|int',
            'n_control' => 'required|string',
            'semestre' => 'required|int',
            'group' => 'required|string',
            'workshop' => 'required|string'
        ];

        $validator = Validator::make($data,$rules);
        if($validator->fails()){
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => 'Error en la validación de datos.'
            ], 422);
        }

        $attachUser = StudentDetailMapper::toCreateStudentDetailDTO($data);

        $user = $this->service->attachStudentDetail($attachUser);

        return response()->json([
            'success' => true,
            'data' => ['user'=>$user],
            'message' => 'El usuario ha sido creado con éxito.',
        ]);

    }

    public function import(ImportUsersRequest $request)
    {
        $file= $request->file('file');

        Excel::import(new UsersImport($this->service),$file);

        return response()->json([
            'success' => true,
            'message' => 'Usuarios importados correctamente.'
        ]);

    }
}
