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
use Illuminate\Support\Str;


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
     *     path="/api/v1/admin-actions/register",
     *     summary="Registrar un nuevo usuario",
     *     description="Crea un nuevo usuario en el sistema con los datos proporcionados.",
     *     operationId="adminRegisterUser",
     *     tags={"Admin"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos necesarios para el registro del usuario",
     *         @OA\JsonContent(ref="#/components/schemas/CreateUserDTO")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Usuario creado con éxito",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/DomainUser")
     *             ),
     *             @OA\Property(property="message", type="string", example="El usuario ha sido creado con éxito.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Error en la validación de datos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Listado de errores de validación",
     *                 example={
     *                     "email": {"El campo email es obligatorio."},
     *                     "password": {"El campo password debe tener al menos 6 caracteres."}
     *                 }
     *             ),
     *             @OA\Property(property="message", type="string", example="Error en la validación de datos.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Error inesperado en el servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Ocurrió un error inesperado.")
     *         )
     *     )
     * )
     */
    public function registerUser(Request $request)
    {
        $data = $request->only([
            'name',
            'last_name',
            'email',
            'phone_number',
            'birthdate',
            'gender',
            'curp',
            'address',
            'blood_type',
            'registration_date',
            'status'
        ]);
        $rules = [
            'name' => 'required|string',
            'last_name'  => 'required|string',
            'email'  => 'required|email',
            'phone_number'  => 'required|string',
            'birthdate' => 'sometimes|required|date',
            'gender' => 'sometimes|required|string',
            'curp' => 'required|string',
            'address' => 'sometimes|required|array',
            'blood_type' => 'sometimes|required|string',
            'registration_date' => 'sometimes|required|date',
            'status' => 'required|string'
        ];

        $validator = Validator::make($data,$rules);
        if($validator->fails()){
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => 'Error en la validación de datos.'
            ], 422);
        }
        $password= Str::random(12);
        $data['password'] = $password;
        $createUser = UserMapper::toCreateUserDTO($data);

        $user = $this->service->registerUser($createUser, $password);

        return response()->json([
            'success' => true,
            'data' => ['user'=>$user],
            'message' => 'El usuario ha sido creado con éxito.',
        ]);
    }
    /**
     * @OA\Post(
     *     path="/api/v1/admin-actions/attach-student",
     *     tags={"Admin"},
     *     summary="Asociar detalles de estudiante a un usuario existente",
     *     description="Permite asignar información académica (carrera, semestre, grupo, taller) a un usuario ya registrado.",
     *     operationId="attachStudentDetailToUser",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos necesarios para asociar un detalle de estudiante al usuario.",
     *         @OA\JsonContent(ref="#/components/schemas/CreateStudentDetailDTO")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Usuario asociado correctamente a un detalle de estudiante.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     ref="#/components/schemas/DomainUser"
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Se asociarón correctamente los datos al estudiante."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Error en la validación de datos.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error en la validación de datos."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(
     *                     property="user_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="El campo user_id es obligatorio.")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado para realizar esta acción."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario o recurso no encontrado."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor."
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
     *     description="Permite subir un archivo Excel (.xlsx) con los datos de los usuarios y sus detalles estudiantiles.
     *     El archivo debe contener las columnas en el siguiente orden:
     *     1. name (Nombre)
     *     2. last_name (Apellidos)
     *     3. email (Correo electrónico)
     *     4. password (Contraseña, si se deja vacío se asignará 'default123')
     *     5. phone_number (Teléfono)
     *     6. birthdate (Fecha de nacimiento, formato YYYY-MM-DD)
     *     7. gender (Género)
     *     8. curp (CURP)
     *     9. street (Calle)
     *     10. city (Ciudad)
     *     11. state (Estado)
     *     12. zip_code (Código postal)
     *     13. stripe_customer_id (Opcional)
     *     14. blood_type (Tipo de sangre)
     *     15. registration_date (Fecha de registro, si no se especifica se usa la actual)
     *     16. status (Estado del usuario, por defecto 'activo')
     *     17. career_id (ID de la carrera)
     *     18. n_control (Número de control)
     *     19. semestre (Semestre)
     *     20. group (Grupo)
     *     21. workshop (Taller)
     *     ",
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
     *                     description="Archivo Excel (.xlsx) con las columnas en el orden especificado"
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
     *     @OA\Response(
     *         response=400,
     *         description="Error en la validación o formato del archivo.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Archivo Excel inválido o columnas incorrectas.")
     *         )
     *     )
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
     *     Se puede especificar una lista de CURP o role (solo uno de los dos) y los permisos que se añadirán o eliminarán.",
     *     operationId="updateManyUserPermissions",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos necesarios para actualizar permisos de usuario.",
     *         @OA\JsonContent(ref="#/components/schemas/UpdateUserPermissionsDTO")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Respuesta de usuarios actualizados.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="users_permissions",
     *                     type="array",
     *                     description="Usuarios con permisos actualizados",
     *                     @OA\Items(ref="#/components/schemas/UserWithUpdatedPermissionsResponse")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 nullable=true,
     *                 example="Permisos actualizados correctamente."
     *             )
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
            'curps' => ['array'],
            'curps.*' => ['string', 'exists:users,curp'],
            'role' => ['nullable', 'string', 'exists:roles,name'],
            'permissionsToAdd' => ['array'],
            'permissionsToAdd.*' => ['string', 'exists:permissions,name'],
            'permissionsToRemove' => ['array'],
            'permissionsToRemove.*' => ['string', 'exists:permissions,name'],
        ]);

        $hasCurps = !empty($validated['curps'] ?? []);
        $hasRole = !empty($validated['role'] ?? null);

        if ($hasCurps && $hasRole) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes especificar CURPs y rol al mismo tiempo.',
            ], 422);
        }

        if (!$hasCurps && !$hasRole) {
            return response()->json([
                'success' => false,
                'message' => 'Debes proporcionar al menos un array de CURPs o un rol.',
            ], 422);
        }
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
     *     description="Permite al administrador ver a todos los usuarios registrados, junto con sus roles, permisos y detalles académicos (si aplica).",
     *     operationId="showAllUsers",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Forzar actualización del caché (true o false).",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Cantidad de usuarios por página",
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
     *         description="Usuarios obtenidos correctamente.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="users",
     *                     ref="#/components/schemas/PaginatedResponse",
     *                     description="Respuesta paginada con los usuarios.",
     *                     @OA\Property(
     *                         property="items",
     *                         type="array",
     *                         description="Lista de usuarios paginados.",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=7),
     *                             @OA\Property(property="name", type="string", example="Ana"),
     *                             @OA\Property(property="last_name", type="string", example="Lopez"),
     *                             @OA\Property(property="email", type="string", example="ana@example.com"),
     *                             @OA\Property(property="curp", type="string", example="XXXX"),
     *                             @OA\Property(
     *                                 property="roles",
     *                                 type="array",
     *                                 @OA\Items(type="string", example="student")
     *                             ),
     *                             @OA\Property(
     *                                 property="permissions",
     *                                 type="array",
     *                                 @OA\Items(type="string", example="create payment")
     *                             ),
     *                             @OA\Property(
     *                                 property="student_detail",
     *                                 type="object",
     *                                 nullable=true,
     *                                 @OA\Property(property="career", type="string", example="Ciencias Sociales"),
     *                                 @OA\Property(property="n_control", type="integer", example=21432),
     *                                 @OA\Property(property="semestre", type="integer", example=5),
     *                                 @OA\Property(property="group", type="string", example="B")
     *                             )
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Usuarios encontrados.")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);
        $perPage = $request->query('perPage', 15);
        $page    = $request->query('page', 1);
        $users=$this->service->showAllUsers($perPage, $page,$forceRefresh);
        return response()->json([
            'success' => true,
            'data' =>['users'=> $users],
            'message' => 'Usuarios encontrados.',
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin-actions/updated-roles",
     *     summary="Sincroniza roles de múltiples usuarios",
     *     description="Permite agregar o eliminar roles a varios usuarios simultáneamente.",
     *     tags={"Admin"},
     *     operationId="updateManyUserRoles",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/UpdateUserRoleDTO")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Roles actualizados correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Roles actualizados correctamente."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="users_roles", ref="#/components/schemas/UserWithUpdatedRoleResponse")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Error de validación")
     * )
     */
    public function syncRoles(Request $request)
    {
        $validated = $request->validate([
            'curps' => ['array'],
            'curps.*' => ['string', 'exists:users,curp'],
            'rolesToAdd' => ['array'],
            'rolesToAdd.*' => ['string', 'exists:permissions,name'],
            'rolesToRemove' => ['array'],
            'rolesToRemove.*' => ['string', 'exists:permissions,name'],
        ]);

        $hasCurps = !empty($validated['curps'] ?? []);

        if (!$hasCurps) {
            return response()->json([
                'success' => false,
                'message' => 'Debes proporcionar un array de CURPs.',
            ], 422);
        }
        $dto = UserMapper::toUpdateUserRoleDTO($validated);
        $updated=$this->service->syncRoles($dto);

        return response()->json([
            'success' => true,
            'data' =>['users_roles'=> $updated],
            'message' => 'Roles actualizados correctamente.',
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin-actions/activate-users",
     *     summary="Activa múltiples usuarios",
     *     description="Cambia el estado de los usuarios seleccionados a 'activado'.",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *        required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"ids"},
     *             @OA\Property(property="ids", type="array", @OA\Items(type="integer"), example={4, 5, 6})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuarios activados correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(
     *                     property="concept",
     *                     ref="#/components/schemas/UserChangedStatusResponse"
     *                 )
     *
     *             ),
     *             @OA\Property(property="message", type="string", example="Estatus de usuarios actualizados correctamente.")
     *         )
     *     ),
     *     @OA\Response(response=409, description="Conflicto en los datos")
     * )
     */
    public function activateUsers(Request $request)
    {
        $ids = $request->validate([
            'ids'=> ['array'],
        ]);
        if(!$ids)
        {
             return response()->json([
                'success' => false,
                'message' => 'Debes proporcionar un array de ids.',
            ], 422);
        }
        $updated=$this->service->activateUsers($ids);

        return response()->json([
            'success' => true,
            'data' =>['activate_users'=> $updated],
            'message' => 'Estatus de usuarios actualizados correctamente.',
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin-actions/delete-users",
     *     summary="Elimina múltiples usuarios",
     *     description="Cambia el estado de los usuarios seleccionados a 'eliminado'.",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *        required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"ids"},
     *             @OA\Property(property="ids", type="array", @OA\Items(type="integer"), example={4, 5, 6})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuarios eliminados correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(
     *                     property="concept",
     *                     ref="#/components/schemas/UserChangedStatusResponse"
     *                 )
     *
     *             ),
     *             @OA\Property(property="message", type="string", example="Estatus de usuarios actualizados correctamente.")
     *         )
     *     ),
     *     @OA\Response(response=409, description="Conflicto en los datos")
     * )
     */
    public function deleteUsers(Request $request)
    {
        $ids = $request->validate([
            'ids'=> ['array'],
        ]);
        if(!$ids)
        {
             return response()->json([
                'success' => false,
                'message' => 'Debes proporcionar un array de ids.',
            ], 422);
        }
        $updated=$this->service->deleteUsers($ids);

        return response()->json([
            'success' => true,
            'data' =>['delete_users'=> $updated],
            'message' => 'Estatus de usuarios actualizados correctamente.',
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin-actions/disable-users",
     *     summary="Da de baja múltiples usuarios",
     *     description="Cambia el estado de los usuarios seleccionados a 'baja'.",
     *     tags={"Admin"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *        required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"ids"},
     *             @OA\Property(property="ids", type="array", @OA\Items(type="integer"), example={4, 5, 6})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuarios dados de baja correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(
     *                     property="concept",
     *                     ref="#/components/schemas/UserChangedStatusResponse"
     *                 )
     *
     *             ),
     *             @OA\Property(property="message", type="string", example="Estatus de usuarios actualizados correctamente.")
     *         )
     *     ),
     *     @OA\Response(response=409, description="Conflicto en los datos")
     * )
     */
    public function disableUsers(Request $request)
    {
        $ids = $request->validate([
            'ids'=> ['array'],
        ]);
        if(!$ids)
        {
             return response()->json([
                'success' => false,
                'message' => 'Debes proporcionar un array de ids.',
            ], 422);
        }
        $updated=$this->service->disableUsers($ids);

        return response()->json([
            'success' => true,
            'data' =>['disable_users'=> $updated],
            'message' => 'Estatus de usuarios actualizados correctamente.',
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin-actions/find-permissions",
     *     summary="Mostrar permisos existentes",
     *     description="Permite al administrador ver todos los permisos registrados.",
     *     operationId="showAllPermissions",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *
     *      @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Forzar actualización del caché (true o false).",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
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
     *         description="Permisos obtenidos correctamente.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="permissions",
     *                     allOf={
     *                         @OA\Schema(ref="#/components/schemas/PaginatedResponse"),
     *                         @OA\Schema(
     *                             @OA\Property(
     *                                 property="items",
     *                                 type="array",
     *                                 @OA\Items(ref="#/components/schemas/Permission")
     *                             )
     *                         )
     *                     }
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function findAllPermissions(Request $request)
    {
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);
        $permissions= $this->service->findAllPermissions($forceRefresh);
        return response()->json([
            'success' => true,
            'data' =>['permissions'=> $permissions],
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin-actions/find-roles",
     *     summary="Mostrar roles existentes",
     *     description="Permite al administrador ver todos los roles registrados.",
     *     operationId="showAllRoles",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *
     *      @OA\Parameter(
     *         name="forceRefresh",
     *         in="query",
     *         description="Forzar actualización del caché (true o false).",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Roles obtenidos correctamente.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="roles",
     *                     type="array",
     *                     description="Lista de roles disponibles.",
     *                     @OA\Items(ref="#/components/schemas/Role")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function findAllRoles(Request $request)
    {
        $forceRefresh = filter_var($request->query('forceRefresh', false), FILTER_VALIDATE_BOOLEAN);
        $roles= $this->service->findAllRoles($forceRefresh);
        return response()->json([
            'success' => true,
            'data' =>['roles'=> $roles],
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin-actions/roles/{id}",
     *     summary="Mostrar rol por ID",
     *     description="Permite al administrador ver la información de un rol específico por su identificador.",
     *     operationId="showRoleById",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del rol a consultar",
     *         required=true,
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Rol obtenido correctamente.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="role", ref="#/components/schemas/Role")
     *             )
     *         )
     *     )
     * )
     */
    public function findRoleById(int $id)
    {
        $role= $this->service->findRolById($id);
        return response()->json([
            'success' => true,
            'data' =>['role'=> $role],
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin-actions/permissions/{id}",
     *     summary="Mostrar permiso por ID",
     *     description="Permite al administrador ver la información de un permiso específico por su identificador.",
     *     operationId="showPermissionById",
     *     tags={"Admin"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del permiso a consultar",
     *         required=true,
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Permiso obtenido correctamente.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="permission", ref="#/components/schemas/Permission")
     *             )
     *         )
     *     )
     * )
     */
    public function findPermissionById(int $id)
    {
        $permission= $this->service->findPermissionById($id);
        return response()->json([
            'success' => true,
            'data' =>['permission'=> $permission],
        ], 200);
    }

}
