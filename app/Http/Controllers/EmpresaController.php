<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\{DB, Validator, Hash};
use App\Models\{
    Empresa,
    Endereco,
    Usuario
};

class EmpresaController extends BaseController
{
    public function show($id)
    {
        try {
            $company = Empresa::find($id);

            if (!$company) {
                throw new CustomException('Empresa não encontrada', 404);
            }

            return $this->success_data_response(
                'Dados da empresa recuperados com sucesso',
                $company->toArrayProfile()
            );
        } catch (CustomException $exception) {
            $this->error_response($exception->getMessage());
        } catch (\Exception $exception) {
            return $this->error_response('Erro ao consultar empresa.', $exception->getMessage());
        }
    }

    public function salvarUsuario(Request $request)
    {
        try {
            DB::beginTransaction();

            // $request->validate([
            //     'email' => 'required|email|unique:usuarios,email',
            //     'password' => 'required|min:8|confirmed',
            // ]);

            $usuario = new Usuario();
            $usuario->email = $request->input('email');
            $usuario->password = Hash::make($request->input('password'));
            $usuario->usuarioable_type = Empresa::class;
            $usuario->save();

            DB::commit();

            $token = auth('api')->login($usuario);
            return response()->json([
                'status' => 'success',
                'message' => 'Usuário salvo e autenticado com sucesso.',
                'data' => [
                    'token' => $token,
                    'user' => $usuario,
                ],
            ], 201);

            return $this->success_response('Usuário salvo com sucesso.');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error_response('Erro ao salvar usuário.', $exception->getMessage());
        }
    }

    public function salvar(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $usuario = auth()->user();
            if (!$usuario) {
                throw new CustomException("Usuário não autenticado.", 401);
            }

            $empresa = new Empresa();
            $empresa->nome_fantasia = $request->input('nome_fantasia');
            $empresa->razao_social = $request->input('razao_social');
            $empresa->cnpj = $request->input('cnpj');
            $empresa->sem_cnpj = $request->boolean('sem_cnpj');
            $empresa->telefone = $request->input('telefone');
            $empresa->email = $usuario->email;
            $empresa->descricao = $request->input('descricao');
            $empresa->website = $request->input('website');
            $empresa->youtube_video = $request->input('youtube_video');
            $empresa->tipo_empresa = $request->input('tipo_empresa');
            $empresa->ano_fundacao = $request->input('ano_fundacao');
            $empresa->numero_funcionarios = $request->input('numero_funcionarios');
            $empresa->politica_remoto = $request->input('politica_remoto');
            
            $empresa->facebook = $request->input('facebook');
            $empresa->twitter = $request->input('twitter');
            $empresa->linkedin = $request->input('linkedin');
            $empresa->instagram = $request->input('instagram');
            $empresa->tiktok = $request->input('tiktok');
            $empresa->youtube = $request->input('youtube');
            
            $empresa->contato_nome = $request->input('contato_nome');
            $empresa->contato_cargo = $request->input('contato_cargo');
            $empresa->contato_telefone = $request->input('contato_telefone');
            $empresa->como_encontrou = $request->input('como_encontrou');
            $empresa->save();

            $enderecoData = $request->input('endereco');
            $endereco = new Endereco();
            $endereco->fill($enderecoData);
            $endereco->enderecavel_type = Empresa::class;
            $endereco->enderecavel_id = $empresa->id;
            $endereco->save();

            $usuario->nome = $request->nome_fantasia;
            $usuario->usuarioable_id = $empresa->id;
            $usuario->perfil_completo = true;
            $usuario->save();

            $empresa->usuario()->save($usuario);

            DB::commit();
            return $this->success_response('Empresa cadastrada.');
        } catch (CustomException $exception) {
            DB::rollBack();
            return $this->error_response($exception->getMessage(), null, $exception->getCode());
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error_response('Erro ao cadastrar empresa.', $exception->getMessage());
        }
    }
}
