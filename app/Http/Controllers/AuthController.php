<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use App\Helpers\LogHelper;
use App\Models\LogErrors;
use App\Models\LogSuccess;
use App\Models\Usuario;
use Illuminate\Support\Facades\{
    Log, 
    DB, 
    Mail, 
    Auth,
    Hash
};
use Illuminate\Support\{
    Str, 
    Carbon
};
use App\Mail\ResetPasswordMail;

class AuthController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'sendResetLink', 'resetPassword', 'validateResetToken']]);
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
                    'usuarioable_id' => $user->usuarioable_id,
                    'usuarioable_type' => $user->usuarioable_type,
                    'perfil_completo' => $user->perfil_completo
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

    public function sendResetLink(Request $request)
    {
        
        $request->validate([
            'email' => 'required|email',
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario) {
            return response()->json([
                'message' => 'O e-mail informado não está cadastrado.'
            ], 404);
        }

        $token = Str::random(64);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $usuario->email],
            [
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        $url = "https://recruit-pro.onrender.com/reset-password?token={$token}&email=" . urlencode($usuario->email);

        try {
            Mail::to($usuario->email)->send(new ResetPasswordMail($usuario, $url));

            return response()->json([
                'message' => 'Um link de redefinição de senha foi enviado para o seu e-mail.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao enviar o e-mail. Tente novamente mais tarde.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $tokenData = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$tokenData) {
            return response()->json([
                'message' => 'Token inválido ou expirado.'
            ], 400);
        }

        $expiresAt = Carbon::parse($tokenData->created_at)->addMinutes(60);
        if (Carbon::now()->isAfter($expiresAt)) {
            return response()->json([
                'message' => 'Token expirado. Solicite um novo link.'
            ], 400);
        }

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario) {

            return $this->success_response("Usuário não encontrado.");

            return response()->json(['message' => 'Usuário não encontrado.'], 404);
        }

        $usuario->password = Hash::make($request->password);
        $usuario->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Senha redefinida com sucesso.'
        ]);
    }

    public function validateResetToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required'
        ]);

        $tokenData = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$tokenData) {
            return response()->json(['valid' => false, 'message' => 'Token inválido.'], 400);
        }

        $expiresAt = \Carbon\Carbon::parse($tokenData->created_at)->addMinutes(60);
        if (now()->isAfter($expiresAt)) {
            return response()->json(['valid' => false, 'message' => 'Token expirado.'], 400);
        }

        return response()->json(['valid' => true]);
    }
}
