<?php

namespace App\Http\Controllers;

use App\Core\Application\Mappers\GeneralMapper;
use App\Core\Application\Mappers\UserMapper;
use App\Core\Application\Services\LoginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{

    protected LoginService $loginService;

    public function __construct(LoginService $loginService)
    {
        $this->loginService=$loginService;
    }
    /**
     * Display a listing of the resource.
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
