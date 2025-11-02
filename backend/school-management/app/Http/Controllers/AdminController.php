<?php

namespace App\Http\Controllers;

use App\Core\Application\Mappers\StudentDetailMapper;
use App\Core\Application\Services\Admin\AdminService;
use App\Http\Requests\ImportUsersRequest;
use App\Imports\UsersImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;


/**
 * @OA\Tag(
 *     name="Admin",
 *     description="Endpoints para gestión administrativa (asignación e importación de usuarios)"
 * )
 */
class AdminController extends Controller
{
    private AdminService $service;

    public function __construct(AdminService $service)
    {
        $this->service= $service;
    }


    /**
     * @OA\Post(
     *     path="/api/v1/admin-actions/attach-student",
     *     summary="Asociar detalles de estudiante a un usuario existente",
     *     description="Permite asignar información académica a un usuario, incluyendo carrera, semestre, grupo y taller.",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "career_id", "n_control", "semestre", "group", "workshop"},
     *             @OA\Property(property="user_id", type="integer", example=12),
     *             @OA\Property(property="career_id", type="integer", example=3),
     *             @OA\Property(property="n_control", type="string", example="2020456789"),
     *             @OA\Property(property="semestre", type="integer", example=5),
     *             @OA\Property(property="group", type="string", example="B"),
     *             @OA\Property(property="workshop", type="string", example="Taller de Robótica")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario asociado correctamente a un detalle de estudiante.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=12),
     *                     @OA\Property(property="name", type="string", example="Carlos Pérez"),
     *                     @OA\Property(property="career", type="string", example="Ingeniería en Sistemas"),
     *                     @OA\Property(property="n_control", type="string", example="2020456789"),
     *                     @OA\Property(property="semestre", type="integer", example=5),
     *                     @OA\Property(property="group", type="string", example="B"),
     *                     @OA\Property(property="workshop", type="string", example="Taller de Robótica")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Se asociarón correctamente los datos al estudiante.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error en la validación de datos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error en la validación de datos."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="user_id", type="array", @OA\Items(type="string", example="El campo user_id es obligatorio."))
     *             )
     *         )
     *     )
     * )
     */
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
            'message' => 'Se asociarón correctamente los datos al estudiante.',
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
