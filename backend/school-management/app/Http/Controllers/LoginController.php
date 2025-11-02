<?php

namespace App\Http\Controllers;

use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Application\Mappers\UserMapper;
use App\Core\Application\Services\LoginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Endpoints relacionados con la autenticación de usuarios"
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
     *     summary="Registro de nuevo usuario",
     *     description="Crea un nuevo usuario en el sistema con los datos proporcionados.",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","last_name","email","password","phone_number","curp","status"},
     *             @OA\Property(property="name", type="string", example="Carlos"),
     *             @OA\Property(property="last_name", type="string", example="Calderon Espinoza"),
     *             @OA\Property(property="email", type="string", format="email", example="example@gmail.com"),
     *             @OA\Property(property="password", type="string", example="123"),
     *             @OA\Property(property="phone_number", type="string", example="7357891145"),
     *             @OA\Property(property="birthdate", type="string", format="date", example="2000-10-22"),
     *             @OA\Property(property="gender", type="string", example="Hombre"),
     *             @OA\Property(property="curp", type="string", example="EXMP090304JMSPXNU7"),
     *             @OA\Property(
     *                 property="address",
     *                 type="object",
     *                 @OA\Property(property="street", type="string", example="Calle Reforma 123"),
     *                 @OA\Property(property="city", type="string", example="Cuautla"),
     *                 @OA\Property(property="state", type="string", example="Morelos"),
     *                 @OA\Property(property="zip", type="string", example="62740")
     *             ),
     *             @OA\Property(property="blood_type", type="string", example="O+"),
     *             @OA\Property(property="registration_date", type="string", format="date", example="2025-11-02"),
     *             @OA\Property(property="status", type="string", example="activo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario creado con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="email", type="string", example="example@gmail.com")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="El usuario ha sido creado con éxito.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error en la validación de datos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error en la validación de datos.")
     *         )
     *     )
     * )
     */

    public function register(Request $request)
    {
        $data = $request->only([
            'name',
            'last_name',
            'email',
            'password',
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
            'password'  => 'required',
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
     *     description="Autentica un usuario con su correo electrónico y contraseña, y devuelve el token y refresh token.",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="example@gmail.com"),
     *             @OA\Property(property="password", type="string", example="123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inicio de sesión exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_tokens", type="object",
     *                     @OA\Property(property="access_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
     *      *              @OA\Property(property="refresh_token", type="string", example="def50200fcdcb15b13e.."),
     *                     @OA\Property(property="token_type", type="string", example="Bearer")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Inicio de sesión exitoso.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error en la validación de datos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error en la validación de datos.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales incorrectas"
     *     )
     * )
     */
    public function login(Request $request){

        $data = $request->only([
            'email',
            'password'
        ]);
        $rules = [
            'email'=>'required|email',
            'password'=>'required'
        ];

        $validator = Validator::make($data,$rules);
        if($validator->fails()){
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => 'Error en la validación de datos.'
            ], 422);

        }
        $loginRequest = GeneralMapper::toLoginDTO($data);

        $userToken = $this->loginService->login($loginRequest);

        return response()->json([
            'success' => true,
            'data' => ['user_tokens'=>$userToken],
            'message' => 'Inicio de sesión exitoso.',
        ]);

   }
}
