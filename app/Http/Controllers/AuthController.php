<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\CustomException;
use App\Helpers\LogHelper;
use App\Models\LogErrors;
use App\Models\LogSuccess;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log;

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

            LogSuccess::create([
                'route' => $request->url(),
                'success_message' => 'Usuário realizou o login com sucesso!',
                'user_id' => Auth()->id() ?? null
            ]);

            $user = auth('api')->user();
            return response()->json([
                'status' => 'success',
                'user' => [
                    'id' => $user->id,
                    'nome' => $user->nome,
                    'email' => $user->email,
                    'usuarioable_type' => $user->usuarioable_type
                ],
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ]);

        } catch (CustomException $exception) {
            LogErrors::create([
                'route' => $request->url(),
                'error_message' => $exception->getMessage(),
                'user_id' => Auth()->id() ?? null
            ]);
            return $this->error_response($exception->getMessage(), null, $exception->getCode());
        } catch (\Exception $exception) {
            LogErrors::create([
                'route' => $request->url(),
                'error_message' => $exception->getMessage(),
                'user_id' => Auth()->id() ?? null
            ]);
            return $this->error_response("Não foi possivel realizar login", $exception->getMessage());
        }
    }

    public function logout()
    {
        try {
            auth('api')->logout();

            LogHelper::saveLog('logout', "Usuário realizou logout do sistema.");

            return $this->success_response("Logout realizado com sucesso");
        } catch (\Exception $exception) {
            return $this->error_response("Erro ao realizar logout", $exception->getMessage());
        }
    }


}
