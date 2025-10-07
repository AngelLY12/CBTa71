<?php

namespace App\Http\Controllers;

use App\Services\LoginService;
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

        try {
            $tokenUser = $this->loginService->login($data['email'], $data['password']);

            return response()->json([
                'success' => true,
                'data' => $tokenUser,
                'message' => 'Inicio de sesión exitoso.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hubo un error en el inicio de sesión, intentalo nuevamente',
                'errors' => $e->errors(),
            ], 401);
        }

   }
}
