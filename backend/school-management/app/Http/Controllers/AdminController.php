<?php

namespace App\Http\Controllers;

use App\Core\Application\Mappers\StudentDetailMapper;
use App\Core\Application\Mappers\UserMapper;
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

        /**
     * @OA\Post(
     *     path="/api/v1/admin-actions/import",
     *     summary="Importar usuarios desde un archivo Excel",
     *     description="Permite subir un archivo Excel con los datos de los usuarios para registrarlos masivamente.",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="Archivo Excel (.xlsx) con la información de los usuarios a importar"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuarios importados correctamente.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Usuarios importados correctamente.")
     *         )
     *     ),
     *
     * )
     */
    public function import(ImportUsersRequest $request)
    {
        $file= $request->file('file');

        Excel::import(new UsersImport($this->service),$file);

        return response()->json([
            'success' => true,
            'message' => 'Usuarios importados correctamente.'
        ]);

    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin-actions/update-permissions",
     *     summary="Actualizar permisos a múltiples usuarios",
     *     description="Permite al administrador agregar o eliminar permisos a varios usuarios al mismo tiempo.
     *     Se puede especificar una lista de correos electrónicos y los permisos que se añadirán o eliminarán.",
     *     operationId="updateManyUserPermissions",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"curps"},
     *             @OA\Property(
     *                 property="curps",
     *                 type="array",
     *                 @OA\Items(type="string", example="TROY090304HMRPTA09"),
     *                 description="Lista de CURP de los usuarios a los que se aplicarán los cambios"
     *             ),
     *             @OA\Property(
     *                 property="permissionsToAdd",
     *                 type="array",
     *                 @OA\Items(type="string", example="view payments"),
     *                 description="Permisos que se agregarán a los usuarios"
     *             ),
     *             @OA\Property(
     *                 property="permissionsToRemove",
     *                 type="array",
     *                 @OA\Items(type="string", example="delete card"),
     *                 description="Permisos que se eliminarán de los usuarios"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Permisos actualizados correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Permisos actualizados correctamente.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación en los datos enviados",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="El campo emails es obligatorio.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado: el usuario autenticado no tiene permiso para ejecutar esta acción"
     *     )
     * )
     */
    public function updatePermissions(Request $request)
    {
        $validated = $request->validate([
            'curps' => ['required', 'array', 'min:1'],
            'curps.*' => ['string', 'exists:users,curp'],
            'permissionsToAdd' => ['array'],
            'permissionsToAdd.*' => ['string', 'exists:permissions,name'],
            'permissionsToRemove' => ['array'],
            'permissionsToRemove.*' => ['string', 'exists:permissions,name'],
        ]);
        $dto = UserMapper::toUpdateUserPermissionsDTO($validated);
        $updated=$this->service->syncPermissions($dto);

        return response()->json([
            'success' => true,
            'data' =>['users_permissions'=> $updated],
            'message' => 'Permisos actualizados correctamente.',
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin-actions/users",
     *     summary="Mostrar usuarios existentes",
     *     description="Permite al administrador ver a todos los usuarios existentes en el sistema, sus roles y permisos.",
     *     operationId="showAllUsers",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Número de usuarios por página",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página a mostrar",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuarios paginada con roles y permisos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="users",
     *                     type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(
     *                         property="data",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Juan"),
     *                             @OA\Property(property="last_name", type="string", example="Perez"),
     *                             @OA\Property(property="email", type="string", example="juan@example.com"),
     *                             @OA\Property(property="curp", type="string", example="XXXXXX"),
     *                             @OA\Property(
     *                                 property="roles",
     *                                 type="array",
     *                                 @OA\Items(type="string", example="student")
     *                             ),
     *                             @OA\Property(
     *                                 property="permissions",
     *                                 type="array",
     *                                 @OA\Items(type="string", example="create payment")
     *                             )
     *                         )
     *                     ),
     *                     @OA\Property(property="per_page", type="integer", example=15),
     *                     @OA\Property(property="last_page", type="integer", example=3),
     *                     @OA\Property(property="total", type="integer", example=25)
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Usuarios encontrados.")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->query('perPage', 15);
        $page    = $request->query('page', 1);
        $users=$this->service->showAllUsers($perPage, $page);
        return response()->json([
            'success' => true,
            'data' =>['users'=> $users],
            'message' => 'Usuarios encontrados.',
        ], 200);
    }

}
