<?php

namespace App\Http\Controllers;

use App\Core\Application\Mappers\GeneralMapper;
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
            'data' => ['user_token'=>$userToken],
            'message' => 'Inicio de sesión exitoso.',
        ]);

   }
}
