<?php

namespace App\Http\Controllers;

use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Application\Mappers\UserMapper;
use App\Core\Application\Services\LoginService;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Endpoints relacionados con la autenticación de usuarios, tokens de acceso, contraseñas y verificación"
 * )
 */
class LoginController extends Controller
{

    protected LoginService $loginService;

    public function __construct(LoginService $loginService)
    {
        $this->loginService=$loginService;
    }
    /**
     * @OA\Post(
     *     path="/api/v1/register",
     *     summary="Registrar un nuevo usuario",
     *     description="Crea un nuevo usuario en el sistema con los datos proporcionados.",
     *     operationId="registerUser",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos necesarios para el registro del usuario",
     *         @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
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
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $createUser = UserMapper::toCreateUserDTO($data);

        $user = $this->loginService->register($createUser);

        return response()->json([
            'success' => true,
            'data' => ['user'=>$user],
            'message' => 'El usuario ha sido creado con éxito.',
        ]);

    }

    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="Inicio de sesión de usuario",
     *     description="Autentica un usuario con su correo electrónico y contraseña, devolviendo el access token y refresh token.",
     *     operationId="loginUser",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Credenciales del usuario para iniciar sesión",
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Inicio de sesión exitoso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_tokens", ref="#/components/schemas/LoginResponse")
     *             ),
     *             @OA\Property(property="message", type="string", example="Inicio de sesión exitoso.")
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
     *                     "password": {"El campo password es obligatorio."}
     *                 }
     *             ),
     *             @OA\Property(property="message", type="string", example="Error en la validación de datos.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales incorrectas",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Credenciales incorrectas.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Error inesperado del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Ocurrió un error inesperado.")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request){

        $request->authenticate();
        $data = $request->validated();
        $loginRequest = GeneralMapper::toLoginDTO($data);

        $userToken = $this->loginService->login($loginRequest);

        return response()->json([
            'success' => true,
            'data' => ['user_tokens'=>$userToken],
            'message' => 'Inicio de sesión exitoso.',
        ]);
   }
}
