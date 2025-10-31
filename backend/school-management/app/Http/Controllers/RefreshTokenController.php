<?php

namespace App\Http\Controllers;

use App\Core\Application\Services\RefreshTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefreshTokenController extends Controller
{
    private RefreshTokenService $service;
    public function __construct(
        RefreshTokenService $service
    )
    {
        $this->service= $service;
    }

    public function store(Request $request)
    {
        $request->validate(['refresh_token' => 'required|string']);
        $newToken =$this->service->refreshToken($request->refresh_token);

        return response()->json([
            'success'=>true,
            'data' => ['user_tokens'=>$newToken],
            'token_type' => 'Bearer'
        ]);
    }
    public function logout(Request $request)
    {
        $user=Auth::user();
        $refreshToken = $request->header('x-refresh-token');
        $this->service->logout($user, $refreshToken);
        return response()->noContent();
    }
}
