<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\CustomException;

class AuthController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $credentials = $request->only(['email', 'password']);

            Auth::setProvider(new \Illuminate\Auth\EloquentUserProvider(
                app('hash'),
                Usuario::class
            ));

            if (!$token = auth('api')->attempt($credentials)) {
                throw new CustomException('Credenciais inválidas', 401);
            }

            return response()->json([
                'status' => 'success',
                'user' => auth('api')->user(),
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ]);

        } catch (CustomException $exception) {
            return $this->error_response($exception->getMessage(), null, $exception->getCode());
        } catch (\Exception $exception) {
            return $this->error_response("Não foi possivel realizar login", $exception->getMessage());
        }
    }

    public function logout()
    {
        try {
            auth('api')->logout();

            return $this->success_response("Logout realizado com sucesso");
        } catch (\Exception $exception) {
            return $this->error_response("Erro ao realizar logout", $e->getMessage());
        }
    }


}