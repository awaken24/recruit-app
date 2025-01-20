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
// Route::post('/empresas/register', [EmpresaController::class, 'salvar']);



Route::post('auth/empresas/registerUsuario', [EmpresaController::class, 'salvarUsuario']);
Route::post('/empresas/register', [EmpresaController::class, 'salvar'])->middleware('auth:api');
Route::get('/empresas/profile/{id}', [EmpresaController::class, 'show'])->middleware('auth:api');


Route::get('/vagas/empresa', [VagaController::class, 'buscarVagasPorEmpresa'])->middleware('auth:api');
Route::post('/vagas/register', [VagaController::class, 'salvar'])->middleware('auth:api');
Route::get('/vagas', [VagaController::class, 'listagemVagas']);


Route::get('/habilidades', [HabilidadeController::class, 'index']);


Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
});
