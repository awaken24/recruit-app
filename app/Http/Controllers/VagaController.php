<?php

namespace App\Http\Controllers;

use App\Exceptions\CandidaturaException;
use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\{
    Empresa,
    Vaga,
    Requisito,
    Habilidade,
    LogErrors,
    LogSuccess
};

class VagaController extends BaseController
{
    public function salvar(Request $request) {
        DB::beginTransaction();
        try {
            $usuario = auth()->user();
            if (!$usuario) {
                throw new CustomException("Usuário não autenticado.", 401);
            }

            if ($usuario->usuarioable_type === Empresa::class && $usuario->perfil_completo) {
                $empresa = $usuario->usuarioable;
            } else {
                throw new CustomException('Usuário não autorizado a criar vagas', 403);
            }

            $vaga = new Vaga();
            $vaga->titulo = $request->input('title');
            $vaga->perfil = $request->input('profile');
            $vaga->nivel_experiencia = $request->input('experienceLevel');
            $vaga->descricao = $request->input('descricao');
            $vaga->requisitos = $request->input('requisitos');
            $vaga->modelo_trabalho = $request->input('modelo_trabalho');
            $vaga->endereco_trabalho = $request->input('endereco_trabalho');
            $vaga->cidade_trabalho = $request->input('cidade_trabalho');
            $vaga->comentarios_hibrido = $request->input('comentarios_hibrido');
            $vaga->tipo_contrato = $request->input('tipo_contrato');
            $vaga->faixa_salarial = $request->input('faixa_salarial');
            $vaga->divulgar_salario = $request->input('divulgar_salario');
            $vaga->empresa_id = $empresa->id;
            $vaga->vale_refeicao = $request->input('beneficios.vale_refeicao', false);
            $vaga->vale_alimentacao = $request->input('beneficios.vale_alimentacao', false);
            $vaga->vale_transporte = $request->input('beneficios.vale_transporte', false);
            $vaga->plano_saude = $request->input('beneficios.plano_saude', false);
            $vaga->plano_odontologico = $request->input('beneficios.plano_odontologico', false);
            $vaga->seguro_vida = $request->input('beneficios.seguro_vida', false);
            $vaga->vale_estacionamento = $request->input('beneficios.vale_estacionamento', false);
            $vaga->academia_gympass = $request->input('beneficios.academia_gympass', false);
            $vaga->bonus = $request->input('beneficios.bonus', false);
            $vaga->save();

            foreach ($request->input('habilidadesRequeridas') as $habilidade) {
                $requisito = new Requisito();
                $requisito->vaga_id = $vaga->id;
                $requisito->habilidade_id = $habilidade['habilidade_id'];
                $requisito->tempo_experiencia = $habilidade['nivel_experiencia'];
                $requisito->save();
            }

            DB::commit();

            LogSuccess::create([
                'route' => $request->url(),
                'success_message' => 'Vaga criada com sucesso',
                'user_id' => $usuario->id
            ]);

            return $this->success_response('Vaga criada com sucesso.', 201);
        } catch (CustomException $exception) {
            DB::rollBack();

            LogErrors::create([
                'route' => $request->url(),
                'error_message' => $exception->getMessage(),
                'user_id' => $usuario->id ?? null
            ]);

            return $this->error_response($exception->getMessage(), null, 500);
        } catch (\Exception $exception) {
            DB::rollBack();

            LogErrors::create([
                'route' => $request->url(),
                'error_message' => $exception->getMessage(),
                'user_id' => $usuario->id ?? null
            ]);

            return $this->error_response('Não foi possivel criar a vaga.', $exception->getMessage(), 500);
        }
    }

    public function buscarVagasPorEmpresa(Request $request)
    {
        try {
            $user = Auth::user();

            if ($user->usuarioable_type !== 'App\Models\Empresa') {
                throw new CustomException('Usuário não é uma empresa', 403);
            }

            $empresaId = $user->usuarioable_id;

            $status = $request->input('status');
            $query = Vaga::where('empresa_id', $empresaId);

            if (!is_null($status)) {
                $query->where('status', $status);
            }

            $vagas = $query->get();

            LogSuccess::create([
                'route' => $request->url(),
                'success_message' => 'Vagas obtidas com sucesso!',
                'user_id' => $user->id
            ]);

            return $this->success_data_response('Vagas obtidas', $vagas);
        } catch (CustomException $exception) {
            LogErrors::create([
                'route' => $request->url(),
                'error_message' => $exception->getMessage(),
                'user_id' => Auth::id() ?? null
            ]);

            return $this->error_response($exception->getMessage(), null, 500);
        } catch (\Exception $exception) {
            LogErrors::create([
                'route' => $request->url(),
                'error_message' => $exception->getMessage(),
                'user_id' => Auth::id() ?? null
            ]);

            return $this->error_response('Não foi possivel buscar as vagas.', $exception->getMessage(), 500);
        }
    }

    public function listagemVagas(Request $request)
    {
        try {
            $user = Auth::user();
            $response = ['vagas' => [], 'habilidades' => []];

            $habilidadeId = request('habilidade');
            $vagasQuery = Vaga::with(['requisitosHabilidades.habilidade', 'empresa'])->where('status', 'ativa');

            if ($habilidadeId) {
                $vagasQuery->whereHas('requisitosHabilidades', function ($query) use ($habilidadeId) {
                    $query->where('habilidade_id', $habilidadeId);
                });
            }

            $vagas = $vagasQuery->get();

            if ($user && $user->usuarioable_type === 'App\Models\Candidato') {
                $candidato = $user->usuarioable;

                if ($candidato->perfil_preenchido) {
                    foreach ($vagas as $vaga) {
                        $vaga->compatibilidade = $this->calcularCompatibilidade($candidato, $vaga);
                    }
                }
            }

            $vagas->transform(function ($vaga) {
                $vaga = $vaga->toArray();
                $vaga['habilidades'] = collect($vaga['requisitos_habilidades'])->map(function ($requisito) {
                    return [
                        'nome' => $requisito['habilidade']['nome'],
                        'tempo_experiencia' => $requisito['tempo_experiencia']
                    ];
                })->toArray();
                unset($vaga['requisitos_habilidades']);
                return $vaga;
            });

            $response['vagas'] = $vagas;
            $response['habilidades'] = Habilidade::orderBy('nome')->get();

            LogSuccess::create([
                'route' => $request->url(),
                'success_message' => 'Vagas ativas foram obtidas com sucesso!',
                'user_id' => Auth::id() ?? null
            ]);

            return $this->success_data_response('Vagas ativas obtidas', $response);
        } catch (\Exception $exception) {
            LogErrors::create([
                'route' => $request->url(),
                'error_message' => $exception->getMessage(),
                'user_id' => Auth::id() ?? null
            ]);
            return $this->error_response('Não foi possível buscar as vagas ativas.', $exception->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $vaga = Vaga::with('requisitosHabilidades.habilidade')->find($id);

        if (!$vaga) {
            return $this->error_response("Vaga não encontrada.", null, 404);
        }

        return $this->success_data_response("Vaga encontrada", $vaga);
    }

    public function candidatura(Request $request){
        try{
            $usuario = Auth::user();
            if(!$usuario){
                throw CandidaturaException::usuarioNaoAutenticado();
            }

            if($usuario->usuarioable_type !== 'App\Models\Candidato'){
                throw CandidaturaException::apenasCandidato();
            }

            $candidato = $usuario->usuarioable;

            if(!$candidato || !$candidato instanceof \App\Models\Candidato){
                throw CandidaturaException::apenasCandidato();
            }

            $vagaId = $request->input('vaga_id');
            if(!$vagaId){
                throw new CustomException("O ID da vaga é obrigatório", 400);
            }

            $vaga = Vaga::where('id', $vagaId)->where('status', 'ativa')->first();
            if(!$vaga){
                throw CandidaturaException::vagaNaoEncontrada();
            }

            $candidaturaExistente = DB::table('candidaturas')
                ->where('candidato_id', $candidato->id)
                ->where('vaga_id', $vagaId)
                ->exists();

            if($candidaturaExistente){
                throw CandidaturaException::candidaturaJaRealizada();
            }

            $candidato->candidatura()->create([
                'vaga_id' => $vagaId,
                'empresa_id' => $vaga->empresa_id,
                'candidato_id' => $candidato->id
            ]);

            LogSuccess::create([
                'route' => $request->url(),
                'success_message' => 'Candidatura realizada com sucesso',
                'user_id' => $usuario->id
            ]);

            return $this->success_response('Candidatura realizada com sucesso.', 201);
        } catch(CustomException | CandidaturaException $exception){
            LogErrors::create([
                'route' => $request->url(),
                'error_message' => $exception->getMessage(),
                'user_id' => Auth::id() ?? null
            ]);

            return $this->error_response($exception->getMessage(), null, $exception->getCode());
        } catch(\Exception $exception){
            LogErrors::create([
                'route' => $request->url(),
                'error_message' => $exception->getMessage(),
                'user_id' => Auth::id() ?? null
            ]);

            return $this->error_response('Não foi possível realizar a candidatura', $exception->getMessage(), 500);
        }
    }

    private function calcularCompatibilidade($candidato, $vaga)
    {
        return rand(0, 100);
    }
}
