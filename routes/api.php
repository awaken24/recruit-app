<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    CandidatoController,
    AuthController,
    EmpresaController,
    HabilidadeController,
    VagaController
};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/candidatos', [CandidatoController::class, 'salvar']);
Route::post('auth/candidato/register/usuario', [CandidatoController::class, 'salvarUsuario']);
Route::post('/candidato/register', [CandidatoController::class, 'salvar'])->middleware('auth:api');
Route::post('/candidato/dashboard', [CandidatoController::class, 'dashboard'])->middleware('auth:api');
Route::post('/candidato/painel', [CandidatoController::class, 'painelVagas'])->middleware('auth:api');
Route::post('/candidato/profile/{id}', [CandidatoController::class, 'show']);
Route::post('/candidato/configuracao', [CandidatoController::class, 'getConfiguracao'])->middleware('auth:api');
Route::post('/candidato/salvarConfiguracoes', [CandidatoController::class, 'salvarConfiguracao'])->middleware('auth:api');
Route::get('/candidato/perfil', [CandidatoController::class, 'perfil'])->middleware('auth:api');
Route::post('/candidato/{id}/atualizar', [CandidatoController::class, 'atualizar']);

Route::post('auth/empresas/register/usuario', [EmpresaController::class, 'salvarUsuario']);
Route::post('/empresas/register', [EmpresaController::class, 'salvar'])->middleware('auth:api');
Route::get('/empresas/profile/{id}', [EmpresaController::class, 'show']);
Route::post('/empresa/dashboard', [EmpresaController::class, 'dashboard'])->middleware('auth:api');
Route::post('/empresa/config', [EmpresaController::class, 'getConfiguracaoEmpresa'])->middleware('auth:api');
Route::post('/empresa/salvarConfiguracoes', [EmpresaController::class, 'salvarConfiguracao'])->middleware('auth:api');
Route::post('/empresa/{id}/atualizar', [EmpresaController::class, 'atualizar'])->middleware('auth:api');

Route::get('/vagas/empresa', [VagaController::class, 'buscarVagasPorEmpresa'])->middleware('auth:api');
Route::post('/vagas/register', [VagaController::class, 'salvar'])->middleware('auth:api');
Route::get('/vagas', [VagaController::class, 'listagemVagas']);
Route::get('/vagas/{vagaId}', [VagaController::class, 'show']);
Route::post('/vagas/candidatura', [VagaController::class, 'candidatura'])->middleware('auth:api');
Route::post('/vagas/gerenciar/{id}', [VagaController::class, 'gerenciar']);

Route::get('/habilidades', [HabilidadeController::class, 'index']);

Route::patch('/candidaturas/{id}/aprovar', [VagaController::class, 'aprovarCandidatura'])->middleware('auth:api');
Route::patch('/candidaturas/{id}/reprovar', [VagaController::class, 'reprovarCandidatura'])->middleware('auth:api');

Route::post('/auth/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    Route::get('/validate-reset-token', [AuthController::class, 'validateResetToken']);

    Route::get('check', function () {
        return response()->json(['status' => 'ok'], 200);
    })->middleware('auth:api');
});