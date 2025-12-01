<?php

namespace App\Swagger\controllers;

class Admin
{
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
 *         description="Datos necesarios para el registro del usuario. Nota: La contraseña no debe ser incluida en la request, se genera para cada usuario desde el sistema",
 *         @OA\JsonContent(ref="#/components/schemas/RegisterUserRequest")
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
public function registerUser(){}


/**
 * @OA\Post(
 *     path="/api/v1/admin-actions/promotion",
 *     summary="Incrementa el semestre de los alumnos y da de baja a quienes sobrepasan",
 *     description="Se hace un incremento en el semestre de todos los alumnos sin importar status y da de baja a quienes sobrepasan el semestre 12.",
 *     operationId="promotionStudents",
 *     tags={"Admin"},
 *
 *
 *
 *     @OA\Response(
 *         response=200,
 *         description="Usuarios promovidos con exito",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="affected", type="object",
 *                    @OA\Property(property="usuarios_promovidos", type="integer", example=27),
 *                    @OA\Property(property="usuarios_baja", type="integer", example=5)
 *                )
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
 *                     "email": {"No se puede promover alumnos en este mes."},
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
public function promotion(){}

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
 *         @OA\JsonContent(ref="#/components/schemas/AttachStudentRequest")
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
public function attachStudent(){}

/**
 * @OA\Get(
 *     path="/api/v1/admin-actions/get-student/{id}",
 *     tags={"Admin"},
 *     summary="Obtener detalles de estudiante a un usuario existente",
 *     description="Permite obtener información académica (carrera, grupo, taller) a un usuario ya registrado.",
 *     operationId="getStudentDetailToUser",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="query",
 *         description="ID del estudiante del que se quieren los detalles.",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Detalles de estudiante encontrados correctamente.",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="user",
 *                     ref="#/components/schemas/DomainStudentDetail"
 *                 )
 *             ),
 *
 *         )
 *     ),
 *
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
public function getStudentDetails(){}


/**
 * @OA\Patch(
 *     path="/api/v1/admin-actions/update-student/{id}",
 *     tags={"Admin"},
 *     summary="Actualizar detalles de estudiante a un usuario existente",
 *     description="Permite actualizar información académica (carrera, grupo, taller) a un usuario ya registrado.",
 *     operationId="updateStudentDetailToUser",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Datos necesarios para actualizar un detalle de estudiante al usuario.",
 *         @OA\JsonContent(ref="#/components/schemas/UpdateStudentRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Usuario actualizado correctamente con detalle de estudiante.",
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
 *                 example="Se actualizaron correctamente los detalles de estudiante."
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
 *                     @OA\Items(type="string", example="El campo career_id es obligatorio.")
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
public function updateStudentDetails(){}


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
public function import(){}

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
 *         @OA\JsonContent(ref="#/components/schemas/UpdatePermissionsRequest")
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
public function updatePermissions(){}

/**
 * @OA\Get(
 *     path="/api/v1/admin-actions/showUsers",
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
public function showUsers(){}

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
 *        @OA\JsonContent(ref="#/components/schemas/UpdateRolesRequest")
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
public function updateRoles(){}


/**
 * @OA\Post(
 *     path="/api/v1/admin-actions/activate-users",
 *     summary="Activa múltiples usuarios",
 *     description="Cambia el estado de los usuarios seleccionados a 'activado'.",
 *     tags={"Admin"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *        required=true,
 *        @OA\JsonContent(ref="#/components/schemas/ChangeUserStatusRequest")
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
public function activateUsers(){}


/**
 * @OA\Post(
 *     path="/api/v1/admin-actions/delete-users",
 *     summary="Elimina múltiples usuarios",
 *     description="Cambia el estado de los usuarios seleccionados a 'eliminado'.",
 *     tags={"Admin"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *        required=true,
 *        @OA\JsonContent(ref="#/components/schemas/ChangeUserStatusRequest")
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
public function deleteUsers(){}


/**
 * @OA\Post(
 *     path="/api/v1/admin-actions/disable-users",
 *     summary="Da de baja múltiples usuarios",
 *     description="Cambia el estado de los usuarios seleccionados a 'baja'.",
 *     tags={"Admin"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *        required=true,
 *        @OA\JsonContent(ref="#/components/schemas/ChangeUserStatusRequest")
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
public function disableUsers(){}


/**
 * @OA\Post(
 *     path="/api/v1/admin-actions/find-permissions",
 *     summary="Mostrar permisos existentes",
 *     description="Permite al administrador ver todos los permisos registrados.",
 *     operationId="showAllPermissions",
 *     tags={"Admin"},
 *     security={{"bearerAuth": {}}},
 *
 *
 *      @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             ref="#/components/schemas/FindPermissionsRequest"
 *         )
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
 *                         @OA\Schema(ref="#/components/schemas/PermissionsByUsers"),
 *                          @OA\Schema(
 *                             @OA\Property(
 *                                 property="role",
 *                                 type="string",
 *                                 example="student"
 *                             )
 *                         ),
 *                         @OA\Schema(
 *                             @OA\Property(
 *                                 property="users",
 *                                 type="array",
 *                                 @OA\Items(
 *                                      type="object",
 *                                      @OA\Property(property="id", type="integer", example=1),
 *                                      @OA\Property(property="fullName", type="string", example="Ana García"),
 *                                      @OA\Property(property="curp", type="string", example="GAAA900101HDFRRN05")
 *                                  )
 *                             )
 *                         ),
 *                         @OA\Schema(
 *                             @OA\Property(
 *                                 property="permissions",
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
public function findPermissions(){}


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
public function findRoles(){}


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
public function findRole(){}


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
public function findPermission(){}


}
