<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\{
    Empresa,
    Vaga,
    Requisito,
    Habilidade
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
            return $this->success_response('Vaga criada com sucesso.', 201);
        } catch (CustomException $exception) {
            DB::rollBack();
            return $this->error_response($exception->getMessage(), null, 500);
        } catch (\Exception $exception) {
            DB::rollBack();
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
            
            return $this->success_data_response('Vagas obtidas', $vagas);
        } catch (CustomException $exception) {
            return $this->error_response($exception->getMessage(), null, 500);
        } catch (\Exception $exception) {
            return $this->error_response('Não foi possivel buscar as vagas.', $exception->getMessage(), 500);
        }
    }

    public function listagemVagas()
    {
        try {
            $user = Auth::user();
            $response = ['vagas' => [], 'habilidades' => []];

            $habilidadeId = request('habilidade');
            $vagasQuery = Vaga::with(['requisitos.habilidade', 'empresa'])->where('status', 'ativa');

            if ($habilidadeId) {
                $vagasQuery->whereHas('requisitos', function ($query) use ($habilidadeId) {
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
                $vaga['habilidades'] = collect($vaga['requisitos'])->map(function ($requisito) {
                    return [
                        'nome' => $requisito['habilidade']['nome'],
                        'tempo_experiencia' => $requisito['tempo_experiencia']
                    ];
                })->toArray();
                
                unset($vaga['requisitos']);
                return $vaga;
            });

            $response['vagas'] = $vagas;
            $response['habilidades'] = Habilidade::orderBy('nome')->get();

            return $this->success_data_response('Vagas ativas obtidas', $response);
        } catch (\Exception $exception) {
            return $this->error_response('Não foi possível buscar as vagas ativas.', $exception->getMessage(), 500);
        }
    }

    public function show() 
    {
        return $this->success_response("Deu certo");
    }


    private function calcularCompatibilidade($candidato, $vaga)
    {
        return rand(0, 100);
    }
}
