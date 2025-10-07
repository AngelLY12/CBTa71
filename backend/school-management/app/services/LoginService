<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginService{

    public function login(string $email, string $password){
        $user=User::where('email',$email)->first();
        if(!$user || !Hash::check($password,$user->password)){
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }
        $token=$user->createToken('api-token')->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];

    }


}
