<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\{DB, Validator, Hash};
use App\Models\{
    Empresa,
    Endereco,
    LogErrors,
    LogSuccess,
    Usuario,
    Vaga
};

class EmpresaController extends BaseController
{
    public function show($id, Request $request)
    {
        try {
            $company = Empresa::find($id);

            if (!$company) {
                throw new CustomException('Empresa não encontrada', 404);
            }

            LogSuccess::create([
                'route' => $request->url(),
                'success_message' => 'Dados da empresa recuperados com succeso.',
                'user_id' => auth()->id()
            ]);

            return $this->success_data_response(
                'Dados da empresa recuperados com sucesso',
                $company->toArrayProfile()
            );
        } catch (CustomException $exception) {
            LogErrors::create([
                'route' => $request->url(),
                'error_message' => $exception->getMessage(),
                'user_id' => auth()->id()
            ]);

            $this->error_response($exception->getMessage());
        } catch (\Exception $exception) {
            LogErrors::create([
                'route' => $request->url(),
                'error_message' => $exception->getMessage(),
                'user_id' => auth()->id()
            ]);

            return $this->error_response('Erro ao consultar empresa.', $exception->getMessage());
        }
    }

    public function salvarUsuario(Request $request)
    {
        try {
            DB::beginTransaction();

            $usuario = new Usuario();
            $usuario->email = $request->input('email');
            $usuario->password = Hash::make($request->input('password'));
            $usuario->usuarioable_type = Empresa::class;
            $usuario->save();

            DB::commit();

            $token = auth('api')->login($usuario);

            LogSuccess::create([
                'route' => $request->url(),
                'success_message' => 'Usuário como empresa foi salvo com sucesso!',
                'user_id' => $usuario->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Usuário como empresa salvo e autenticado com sucesso.',
                'data' => [
                    'token' => $token,
                    'user' => $usuario,
                ],
            ], 201);

        } catch (\Exception $exception) {
            DB::rollBack();

            $userId = isset($usuario) ? $usuario->id : null;

            LogErrors::create([
                'route' => $request->url(),
                'error_message' => $exception->getMessage(),
                'user_id' => $userId
            ]);

            return $this->error_response('Erro ao salvar usuário como empresa.', $exception->getMessage());
        }
    }

    public function salvar(Request $request)
    {

        try {
            DB::beginTransaction();

            // $request->validate(Empresa::$rules);

            $usuario = auth()->user();
            if (!$usuario) {
                throw new CustomException("Usuário como empresa não autenticado.", 401);
            }

            // return response()->json("Chegou assim aqui", 500);

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

            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');   
                $nomeArquivo = 'logo_' . time() . '.' . $logo->getClientOriginalExtension();
                $caminho = $logo->storeAs('public/logos', $nomeArquivo);
                $empresa->logo_path = 'storage/logos/' . $nomeArquivo;
            }

            $empresa->save();
            $requestEndereco = $request->input('endereco');

            $endereco = new Endereco();
            $endereco->cep = $requestEndereco['cep'];
            $endereco->estado = $requestEndereco['estado'];
            $endereco->cidade = $requestEndereco['cidade'];
            $endereco->bairro = $requestEndereco['bairro'];
            $endereco->logradouro = $requestEndereco['logradouro'];
            $endereco->numero = $requestEndereco['numero'];
            $endereco->complemento = $requestEndereco['complemento'] ?? null;
            $endereco->enderecavel_type = Empresa::class;
            $endereco->enderecavel_id = $empresa->id;
            $endereco->save();

            $usuario->nome = $request->nome_fantasia;
            $usuario->usuarioable_id = $empresa->id;
            $usuario->perfil_completo = true;
            $usuario->save();

            $empresa->usuario()->save($usuario);

            DB::commit();

            LogSuccess::create([
                'route' => $request->url(),
                'success_message' => 'Empresa cadastrada com sucesso',
                'user_id' => $usuario->id
            ]);

            return $this->success_response('Empresa cadastrada.');
        } catch (CustomException $exception) {
            DB::rollBack();

            LogErrors::create([
                'route' => $request->url(),
                'error_message' => $exception->getMessage(),
                'user_id' => $usuario->id ?? null
            ]);

            return $this->error_response($exception->getMessage(), null, $exception->getCode());
        } catch (\Exception $exception) {
            DB::rollBack();

            LogErrors::create([
                'route' => $request->url(),
                'error_message' => $exception->getMessage(),
                'user_id' => $usuario->id ?? null
            ]);

            return $this->error_response('Erro ao cadastrar empresa.', $exception->getMessage());
        }
    }

    public function dashboard(Request $request)
    {
        try {
            $usuario = auth()->user();
            if (!$usuario) {
                throw new CustomException("Usuário não autenticado.", 401);
            }

            if ($usuario->usuarioable_type !== 'App\Models\Empresa') {
                throw new CustomException('Usuário não é uma empresa', 403);
            }

            $empresa = $usuario->usuarioable;
            $query = Vaga::where('empresa_id', $empresa->id);
            $vagas = $query->get();

            $response = [
                'empresa' => $empresa,
                'vagas' => $vagas
            ];

            return $this->success_data_response("Dashboard Carregado", $response);
        } catch (CustomException $exception) {
            return $this->error_response($exception->getMessage(), null, $exception->getCode());
        } catch (\Exception $e) {
            return $this->error_response('Erro ao carregar dashboard.', $e->getMessage());
        }
    }

}
