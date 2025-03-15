<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\{DB, Validator, Hash};
use App\Models\{
    Candidato,
    Endereco,
    Usuario,
    HabilidadeCandidato,
    Experiencia
};

class CandidatoController extends BaseController
{

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
            $usuario->usuarioable_type = Candidato::class;
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

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return $this->error_response('Erro de validação.', $e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error_response('Erro ao salvar usuário.', $e->getMessage());
        }
    }

    public function dashboard()
    {
        try {
            $usuario = auth()->user();
            if (!$usuario) {
                throw new CustomException("Usuário não autenticado.", 401);
            }

            if ($usuario->usuarioable_type !== 'App\Models\Candidato') {
                throw new CustomException('Usuário não é um candidato', 403);
            }

            $candidato = $usuario->usuarioable;
            $response = [
                'candidato' => $candidato,
                'qtdCandidaturas' => 0,
                'qtdOprtunidades' => 0
            ];

            return $this->success_data_response("Dashboard Carregado", $response);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error_response('Erro de validação.', $e->errors());
        } catch (\Exception $e) {
            return $this->error_response('Erro ao salvar usuário.', $e->getMessage());
        }
    }

    public function salvar(Request $request)
    {
        try {
            // $validator = Validator::make($request->all(), [
            //     'nome_completo' => 'required|string|max:255',
            //     'email' => 'required|email|unique:usuarios,email',
            //     'telefone' => 'nullable|string|max:20',
            //     'data_nascimento' => 'nullable|date',
            //     'cpf' => 'nullable|string|max:14|unique:candidatos,cpf',
            //     'genero' => 'nullable|string|max:20',
                
            //     'endereco.cep' => 'required|string|max:9',
            //     'endereco.logradouro' => 'required|string|max:255',
            //     'endereco.numero' => 'required|string|max:20',
            //     'endereco.complemento' => 'nullable|string|max:255',
            //     'endereco.bairro' => 'required|string|max:255',
            //     'endereco.cidade' => 'required|string|max:255',
            //     'endereco.estado' => 'required|string|max:2',
            // ]);

            // if ($validator->fails()) {
            //     throw new CustomException("Erro de validação: {$validator->errors()}.", 422);
            // }

            DB::beginTransaction();

            $usuario = auth()->user();
            if (!$usuario) {
                throw new CustomException("Usuário não autenticado.", 401);
            }

            $candidato = new Candidato();
            $candidato->nome = $request->input('nome');
            $candidato->sobrenome = $request->input('sobrenome');
            $candidato->telefone = $request->input('telefone');
            $candidato->cpf = $request->input('cpf');
            $candidato->titulo = $request->input('titulo');
            $candidato->nivelIngles = $request->input('nivelIngles');
            $candidato->descricao = $request->input('descricao');
            $candidato->linkedin = $request->input('linkedin');
            $candidato->github = $request->input('github');
            $candidato->foco_carreira = $request->input('foco_carreira');
            $candidato->experienceLevel = $request->input('experienceLevel');
            $candidato->salario_desejado = $request->input('salario_desejo');
            $candidato->tipo_empresa = $request->input('tipo_empresa');
            $candidato->tipo_contrato = $request->input('tipo_contrato');
            $candidato->status_busca = $request->input('status_busca');
            $candidato->trabalho_remoto = $request->input('trabalho_remoto') === 'sim';
            $candidato->pcd = $request->input('pcd') === 'sim';
            $candidato->save();
    
            foreach ($request->input('habilidades') as $habilidade) {
                $habilidadeCandidato = new HabilidadeCandidato();
                $habilidadeCandidato->candidato_id = $candidato->id;
                $habilidadeCandidato->habilidade_id = $habilidade['habilidade_id'];
                $habilidadeCandidato->tempo_experiencia = $habilidade['nivel_experiencia'];
                $habilidadeCandidato->save();
            }

            $endereco = new Endereco();
            $endereco->cep = $request->input('endereco.cep');
            $endereco->estado = $request->input('endereco.estado');
            $endereco->cidade = $request->input('endereco.cidade');
            $endereco->bairro = $request->input('endereco.bairro');
            $endereco->logradouro = $request->input('endereco.logradouro');
            $endereco->numero = $request->input('endereco.numero');
            $endereco->complemento = $request->input('endereco.complemento');
            $endereco->enderecavel_type = Candidato::class;
            $endereco->enderecavel_id = $candidato->id;
            $endereco->save();

            $usuario->nome = $request->nome;
            $usuario->usuarioable_id = $candidato->id;
            $usuario->perfil_completo = true;
            $usuario->save();

            if ($request->has('experiencias')) {
                foreach ($request->input('experiencias') as $experienciaData) {
                    $experiencia = new Experiencia();
                    $experiencia->empresa = $experienciaData['empresa'];
                    $experiencia->cargo = $experienciaData['cargo'];
                    $experiencia->mesInicio = $experienciaData['mesInicio'];
                    $experiencia->anoInicio = $experienciaData['anoInicio'];
                    $experiencia->mesFim = $experienciaData['mesFim'] ?? null;
                    $experiencia->anoFim = $experienciaData['anoFim'] ?? null;
                    $experiencia->trabalhoAtual = $experienciaData['trabalhoAtual'] ?? false;
                    $experiencia->descricao = $experienciaData['descricao'] ?? null;
                    $experiencia->candidato_id = $candidato->id;
                    $experiencia->save();
                }
            }

            DB::commit();
            return $this->success_response('Candidato cadastrado.');

        } catch (CustomException $exception) {
            DB::rollBack();
            return $this->error_response($exception->getMessage(), null, $exception->getCode());
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error_response('Erro ao cadastrar candidato.', $exception->getMessage());
        }
    }
}
