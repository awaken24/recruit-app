<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\{DB, Hash, Validator};
use Illuminate\Support\Facades\Auth;
use App\Models\{
    Candidato,
    Endereco,
    Usuario,
    HabilidadeCandidato,
    Experiencia,
    LogErrors,
    LogSuccess,
    Oportunidades
};

class CandidatoController extends BaseController
{

    public function salvarUsuario(Request $request)
    {
        try {
            DB::beginTransaction();

            $usuario = new Usuario();
            $usuario->email = $request->input('email');
            $usuario->password = Hash::make($request->input('password'));
            $usuario->usuarioable_type = Candidato::class;
            $usuario->save();

            DB::commit();

            $token = auth('api')->login($usuario);

            LogSuccess::create([
                'route' => $request->url(),
                'success_message' => 'O usuário foi salvo com sucesso!',
                'user_id' => $usuario->id
            ]);

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

            LogErrors::create([
                'route' => $request->url(),
                'error_message' => $exception->getMessage(),
                'user_id' => $usuario->id ?? null
            ]);
            return $this->error_response('Erro ao salvar usuário.', $exception->getMessage());
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

            LogErrors::create([
                'route' => $request->url(),
                'error_message' => $e->getMessage(),
                'user_id' => $usuario->id ?? null
            ]);

            return $this->error_response('Erro de validação.', $e->errors());
        } catch (\Exception $e) {
            DB::rollBack();

            LogErrors::create([
                'route' => $request->url(),
                'error_message' => $e->getMessage(),
                'user_id' => $usuario->id ?? null
            ]);

            return $this->error_response('Erro ao salvar usuário.', $e->getMessage());
        }
    }

    public function dashboard(Request $request)
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
            $qtdCandidaturas = $candidato->candidatura()->count();
            $qtdOportunidades = $candidato->oportunidades()->count();

            $response = [
                'candidato' => $candidato,
                'qtdCandidaturas' => $qtdCandidaturas,
                'qtdOprtunidades' => $qtdOportunidades
            ];

            return $this->success_data_response("Dashboard Carregado", $response);
        } catch (CustomException $exception) {
            return $this->error_response($exception->getMessage(), null, $exception->getCode());
        } catch (\Exception $e) {
            return $this->error_response('Erro ao carregar dashboard.', $e->getMessage());
        }
    }

    public function salvar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nome_completo' => 'required|string|max:255',
                'email' => 'required|email|unique:usuarios,email',
                'telefone' => 'nullable|string|max:20',
                'data_nascimento' => 'nullable|date',
                'cpf' => 'nullable|string|max:14|unique:candidatos,cpf',
                'genero' => 'nullable|string|max:20',

                'endereco.cep' => 'required|string|max:9',
                'endereco.logradouro' => 'required|string|max:255',
                'endereco.numero' => 'required|string|max:20',
                'endereco.complemento' => 'nullable|string|max:255',
                'endereco.bairro' => 'required|string|max:255',
                'endereco.cidade' => 'required|string|max:255',
                'endereco.estado' => 'required|string|max:2',
            ]);

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

            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');   
                $nomeArquivo = 'logo_' . time() . '.' . $logo->getClientOriginalExtension();
                $caminho = $logo->storeAs('public/logos', $nomeArquivo);
                $candidato->foto_perfil = 'storage/logos/' . $nomeArquivo;
            }

            $candidato->save();

            $habilidades = json_decode($request->input('habilidades'), true);
            foreach ($habilidades as $habilidade) {
                $habilidadeCandidato = new HabilidadeCandidato();
                $habilidadeCandidato->candidato_id = $candidato->id;
                $habilidadeCandidato->habilidade_id = $habilidade['habilidade_id'];
                $habilidadeCandidato->tempo_experiencia = $habilidade['nivel_experiencia'];
                $habilidadeCandidato->save();
            }
            
            $requestEndereco = json_decode($request->endereco, true);

            $endereco = new Endereco();
            $endereco->cep = $requestEndereco['cep'];
            $endereco->estado = $requestEndereco['estado'];
            $endereco->cidade = $requestEndereco['cidade'];
            $endereco->bairro = $requestEndereco['bairro'];
            $endereco->logradouro = $requestEndereco['logradouro'];
            $endereco->numero = $requestEndereco['numero'];
            $endereco->complemento = $requestEndereco['complemento'] ?? null;
            $endereco->enderecavel_type = Candidato::class;
            $endereco->enderecavel_id = $candidato->id;
            $endereco->save();

            $usuario->nome = $request->nome;
            $usuario->usuarioable_id = $candidato->id;
            $usuario->perfil_completo = true;
            $usuario->save();

            if ($request->has('experiencias')) {
                $experienciasInput = $request->input('experiencias');
                $experiencias = !empty($experienciasInput) ? json_decode($experienciasInput, true) : [];

                if (is_array($experiencias)) {
                    foreach ($experiencias as $dados) {
                        $experiencia = new Experiencia();
                        $experiencia->empresa = $dados['empresa'];
                        $experiencia->cargo = $dados['cargo'];
                        $experiencia->mesInicio = $dados['mesInicio'];
                        $experiencia->anoInicio = $dados['anoInicio'];
                        $experiencia->mesFim = $dados['mesFim'] ?? null;
                        $experiencia->anoFim = $dados['anoFim'] ?? null;
                        $experiencia->trabalhoAtual = $dados['trabalhoAtual'] ?? false;
                        $experiencia->descricao = $dados['descricao'] ?? null;
                        $experiencia->candidato_id = $candidato->id;
                        $experiencia->save();
                    }                    
                }
            }

            DB::commit();

            LogSuccess::create([
                'route' => $request->url(),
                'success_message' => 'Candidato(a) cadastrado(a) com sucesso!',
                'user_id' => $usuario->id
            ]);
            return $this->success_response('Candidato cadastrado.');

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

            return $this->error_response('Erro ao cadastrar candidato.', $exception->getMessage());
        }
    }

    public function painelVagas()
    {
        try {
            $usuario = Auth::user();
            
            if (!$usuario) {
                throw new CustomException('Usuário não autenticado.', 403);
            }

            $candidato = $usuario->usuarioable;

            $candidaturas = $candidato->candidatura()->with(['candidato.habilidades', 'vaga', 'empresa'])->get();
            $oportunidades = $candidato->oportunidades()->where('status', 'pendente')->with('vaga')->get();

            $response = [
                'candidaturas' => $candidaturas,
                'oportunidades' => $oportunidades
            ];

            return $this->success_data_response("", $response);

        } catch (CustomException $exception) {
            return $this->error_response($exception->getMessage(), null, $exception->getCode());
        } catch (\Exception $exception) {
            return $this->error_response('Não foi possível aprovar a candidatura', $exception->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $candidato = Candidato::with('experiencias', 'habilidadeCandidatos.habilidade', 'endereco')->find($id);

            if (!$candidato) {
                throw new CustomException('Candidato não encontrato', 404);
            }
    
            return $this->success_data_response('', $candidato);
        } catch (CustomException $exception) {
            return $this->error_response($exception->getMessage(), null, $exception->getCode());
        } catch (\Exception $exception) {
            return $this->error_response('Não foi possível encontrar o candidato', $exception->getMessage(), $exception->getCode());
        }
    }

    public function getConfiguracao()
    {
        try {
            $usuario = Auth::user();

            if ($usuario->usuarioable_type !== 'App\Models\Candidato') {
                throw new CustomException('Usuário autenticado não é um candidato.', 403);
            }

            $candidato = $usuario->usuarioable;

            $config = $candidato->configuracao ?? $candidato->configuracao()->create([
                'notificacoes_email' => true,
                'notificacoes_whatsapp' => false,
                'receber_alertas_vagas' => true,
            ]);

            return $this->success_data_response("Configurações", $config);
        } catch (CustomException $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()
            ], $exception->getCode());
        } catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => 'Não foi possível carregar as configurações.', 'debug' => $exception->getMessage()], 500);
        }
    }

    public function salvarConfiguracao(Request $request)
    {
        try {
            $usuario = Auth::user();

            if ($usuario->usuarioable_type !== 'App\Models\Candidato') {
                throw new CustomException('Usuário autenticado não é um candidato.', 403);
            }

            $candidato = $usuario->usuarioable;

            $dadosValidados = $request->validate([
                'notificacoes_email' => 'required|boolean',
                'notificacoes_whatsapp' => 'required|boolean',
                'receber_alertas_vagas' => 'required|boolean'
            ]);

            $config = $candidato->configuracao;

            if ($config) {
                $config->update($dadosValidados);
            } else {
                $config = $candidato->configuracao()->create($dadosValidados);
            }

            return $this->success_response("Configuração atualizada com sucesso.", 200);
        } catch (CustomException $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], $exception->getCode());
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return response()->json(['status' => 'error', 'message' => 'Dados inválidos.', 'errors' => $exception->errors()], 422);
        } catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => 'Não foi possível salvar as configurações.', 'debug' => $exception->getMessage()], 500);
        }
    }

    public function atualizar(Request $request, $id)
    {
        try {
            $usuario = auth()->user();
            // if (!$usuario) {
            //    throw new CustomException("Usuário não autenticado.", 401);
            //}

            DB::beginTransaction();

            $candidato = Candidato::findOrFail($id);

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

            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');   
                $nomeArquivo = 'logo_' . time() . '.' . $logo->getClientOriginalExtension();
                $logo->storeAs('public/logos', $nomeArquivo);
                $candidato->foto_perfil = 'storage/logos/' . $nomeArquivo;
            }

            $candidato->save();

            // Atualizar habilidades
            HabilidadeCandidato::where('candidato_id', $candidato->id)->delete();

            $habilidades = json_decode($request->input('habilidades'), true);
            foreach ($habilidades as $habilidade) {
                $hc = new HabilidadeCandidato();
                $hc->candidato_id = $candidato->id;
                $hc->habilidade_id = $habilidade['habilidade_id'];
                $hc->tempo_experiencia = $habilidade['nivel_experiencia'];
                $hc->save();
            }


            $dadosEndereco = json_decode($request->endereco, true);
            $endereco = $candidato->endereco()->firstOrNew([]);
            $endereco->fill([
                'cep' => $dadosEndereco['cep'],
                'estado' => $dadosEndereco['estado'],
                'cidade' => $dadosEndereco['cidade'],
                'bairro' => $dadosEndereco['bairro'],
                'logradouro' => $dadosEndereco['logradouro'],
                'numero' => $dadosEndereco['numero'],
                'complemento' => $dadosEndereco['complemento'] ?? null,
            ]);
            $endereco->save();

            $candidato->experiencias()->delete();
            $experienciasInput = $request->input('experiencias');
            $experiencias = !empty($experienciasInput) ? json_decode($experienciasInput, true) : [];

            foreach ($experiencias as $dados) {
                $experiencia = new Experiencia();
                $experiencia->empresa = $dados['empresa'];
                $experiencia->cargo = $dados['cargo'];
                $experiencia->mesInicio = $dados['mesInicio'];
                $experiencia->anoInicio = $dados['anoInicio'];
                $experiencia->mesFim = $dados['mesFim'] ?? null;
                $experiencia->anoFim = $dados['anoFim'] ?? null;
                $experiencia->trabalhoAtual = $dados['trabalhoAtual'] ?? false;
                $experiencia->descricao = $dados['descricao'] ?? null;
                $experiencia->candidato_id = $candidato->id;
                $experiencia->save();
            }

            DB::commit();

            LogSuccess::create([
                'route' => $request->url(),
                'success_message' => 'Candidato atualizado com sucesso',
                'user_id' => Auth::id() ?? null
            ]);

            return $this->success_response("Candidato atualizado com sucesso");
        } catch (CustomException $exception) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], $exception->getCode());
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Erro ao atualizar candidato', 'error' => $exception->getMessage()], 500);
        }
    }



}
