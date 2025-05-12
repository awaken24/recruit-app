<?php

namespace App\Http\Controllers;

use App\Exceptions\CandidaturaException;
use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Libraries\ZApi;
use App\Mail\{
    CandidaturaAprovadaMail,
    CandidaturaReprovadaMail
};
use App\Models\{
    Empresa,
    Vaga,
    Requisito,
    Habilidade,
    LogErrors,
    LogSuccess,
    Candidatura
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
            // $vaga->requisitos = $request->input('requisitos');
            $vaga->receber_candidaturas_ate = $request->input('receberCandidaturasAte');
            $vaga->modelo_trabalho = $request->input('modelo_trabalho');
            $vaga->endereco_trabalho = $request->input('endereco_trabalho');
            $vaga->cidade_trabalho = $request->input('cidade_trabalho');
            $vaga->comentarios_hibrido = $request->input('comentarios_hibrido');
            $vaga->tipo_contrato = $request->input('tipo_contrato');
            $vaga->faixa_salarial = $request->input('faixa_salarial');
            $vaga->divulgar_salario = $request->input('divulgar_salario');
            $vaga->empresa_id = $empresa->id;

            $beneficios = $request->input('beneficios', []);

            $vaga->vale_refeicao = in_array('vale_refeicao', $beneficios);
            $vaga->vale_alimentacao = in_array('vale_alimentacao', $beneficios);
            $vaga->vale_transporte = in_array('vale_transporte', $beneficios);
            $vaga->plano_saude = in_array('plano_saude', $beneficios);
            $vaga->plano_odontologico = in_array('plano_odontologico', $beneficios);
            $vaga->seguro_vida = in_array('seguro_vida', $beneficios);
            $vaga->vale_estacionamento = in_array('vale_estacionamento', $beneficios);
            $vaga->academia_gympass = in_array('academia_gympass', $beneficios);
            $vaga->bonus = in_array('bonus', $beneficios);

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

            return $this->success_data_response('Vagas obtidas', $vagas);
        } catch (CustomException $exception) {
            return $this->error_response($exception->getMessage(), null, 500);
        } catch (\Exception $exception) {
            return $this->error_response('Não foi possivel buscar as vagas.', $exception->getMessage(), 500);
        }
    }

    public function listagemVagas(Request $request)
    {
        try {
            $response = ['vagas' => [], 'habilidades' => []];

            $habilidadeId = request('habilidade');
            $vagasQuery = Vaga::with(['requisitosHabilidades.habilidade', 'empresa'])->where('status', 'ativa');

            if ($habilidadeId) {
                $vagasQuery->whereHas('requisitosHabilidades', function ($query) use ($habilidadeId) {
                    $query->where('habilidade_id', $habilidadeId);
                });
            }

            $vagas = $vagasQuery->get();

            $usuario = auth('api')->user();
            if ($usuario && $usuario->usuarioable_type === 'App\Models\Candidato') {
                $candidato = $usuario->usuarioable;

                if ($usuario->perfil_completo) {
                    foreach ($vagas as $vaga) {
                        $vaga->compatibilidade = $vaga->calcularCompatibilidade($candidato);
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

            if ($candidaturaExistente) {
                throw CandidaturaException::candidaturaJaRealizada();
            }

            $candidato->candidatura()->create([
                'vaga_id' => $vagaId,
                'empresa_id' => $vaga->empresa_id,
                'candidato_id' => $candidato->id,
                'status' => Candidatura::STATUS_PENDENTE,
                'compatibilidade' => $vaga->calcularCompatibilidade($candidato)
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

    public function gerenciar($vagaId)
    {
        $vaga = Vaga::with([
            'requisitosHabilidades.habilidade',
            'candidaturas.candidato.habilidades'
        ])->find($vagaId);

        if (!$vaga) {
            return $this->error_response("Vaga não encontrada.", null, 404);
        }

        return $this->success_data_response("", $vaga);
    }

    public function aprovarCandidatura($candidaturaId)
    {
        try {
            $user = Auth::user();

            if ($user->usuarioable_type !== 'App\Models\Empresa') {
                throw new CustomException('Usuário não é uma empresa', 403);
            }

            $candidatura = Candidatura::findOrFail($candidaturaId);
            $candidatura->status = Candidatura::STATUS_APROVADA;

            $configEmpresa = $candidatura->empresa->configuracao;
            $candidato     = $candidatura->candidato;
            $vaga          = $candidatura->vaga;
            $empresa       = $candidatura->empresa;
    
            $resposta = null;
    
            if ($configEmpresa->whatsapp_ativo) {
                try {
                    $template = $configEmpresa->whatsapp_template;
    
                    $mensagem = str_replace(
                        ['{{candidato}}', '{{vaga}}', '{{empresa}}'],
                        [$candidato->nome, $vaga->titulo, $empresa->nome_fantasia],
                        $template
                    );
    
                    $zapi = new ZApi(
                        $configEmpresa->whatsapp_instance,
                        $configEmpresa->whatsapp_token,
                        $configEmpresa->whatsapp_security_token
                    );
    
                    $zapi->sendMessage($candidato->telefone, $mensagem);
                } catch (\Throwable $exception) {
                    LogErrors::create([
                        'route' => 'aprovar/candidatura',
                        'error_message' => 'Erro ao enviar mensagem via WhatsApp: ' . $exception->getMessage(),
                        'user_id' => Auth::id() ?? null
                    ]);
                }
            }

            if (!empty($configEmpresa->email_template_sucesso)) {
                try {
                    $emailRequest = $candidato->usuario->email;

                    $modeloTrabalho = null;
                    if ($vaga->modelo_trabalho === 'remoto') {
                        $modeloTrabalho = 'Remoto';
                    } else {
                        $modeloTrabalho= 'Presencial/Híbrido';
                    }

                    if (!in_array($tipoContrato = strtoupper($vaga->tipo_contrato), ['CLT', 'PJ'])) {
                        $tipoContrato = 'Estágio';
                    }

                    $detalhesHtml = '
                        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f7f7f7; padding: 15px; border-radius: 4px; margin: 20px 0;">
                            <tr>
                                <td style="font-weight: 500; color: #666666; width: 120px;">Cargo:</td>
                                <td style="color: #333333; padding-left: 10px;"> ' . $vaga->titulo . ' </td>
                            </tr>
                            <tr>
                                <td style="font-weight: 500; color: #666666; width: 120px; white-space: nowrap;">Modelo de trabalho:</td>
                                <td style="color: #333333; padding-left: 10px;">' . $modeloTrabalho . '</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 500; color: #666666; width: 120px; white-space: nowrap;">Tipo de contrato:</td>
                                <td style="color: #333333; padding-left: 10px;">' . $tipoContrato . '</td>
                            </tr>
                        </table>';
            
                    $emailBody = str_replace(
                        ['{{nome}}', '{{vaga}}', '{{empresa}}', '{{detalhes_vaga}}'],
                        [$candidato->nome, $vaga->titulo, $empresa->nome_fantasia, $detalhesHtml],
                        $configEmpresa->email_template_sucesso
                    );
            
                    Mail::to($emailRequest)->send(new CandidaturaAprovadaMail($emailBody));
            
                    LogSuccess::create([
                        'route' => 'aprovar/candidatura',
                        'success_message' => 'Email enviado ao candidato com sucesso: ' . $emailRequest,
                        'user_id' => Auth::id() ?? null
                    ]);
                } catch (\Throwable $exception) {
                    LogErrors::create([
                        'route' => 'aprovar/candidatura',
                        'error_message' => 'Erro ao enviar e-mail: ' . $exception->getMessage(),
                        'user_id' => Auth::id() ?? null
                    ]);
                }
            }

            $candidatura->save();
    
            return $this->success_response('Candidatura aprovada.', 200);
        } catch(CustomException $exception) {
            return $this->error_response($exception->getMessage(), null, $exception->getCode());
        } catch(\Exception $exception) {
            return $this->error_response('Não foi possível aprovar a candidatura', $exception->getMessage(), 500);
        }
    }

    public function reprovarCandidatura($candidaturaId)
    {
        try {
            $user = Auth::user();

            if ($user->usuarioable_type !== 'App\Models\Empresa') {
                throw new CustomException('Usuário não é uma empresa', 403);
            }

            $candidatura = Candidatura::findOrFail($candidaturaId);
            $candidatura->status = Candidatura::STATUS_REPROVADA;

            $configEmpresa = $candidatura->empresa->configuracao;
            $candidato     = $candidatura->candidato;
            $vaga          = $candidatura->vaga;
            $empresa       = $candidatura->empresa;

            if (!empty($configEmpresa->email_template_recusado)) {
                try {
                    $emailRequest = $candidato->usuario->email;
    
                    $emailBody = str_replace(
                        ['{{nome}}', '{{vaga}}', '{{empresa}}'],
                        [$candidato->nome, $vaga->titulo, $empresa->nome_fantasia],
                        $configEmpresa->email_template_recusado
                    );
    
                    Mail::to($emailRequest)->send(new CandidaturaReprovadaMail($emailBody));
    
                    LogSuccess::create([
                        'route' => 'reprovar/candidatura',
                        'success_message' => 'Email de reprovação enviado ao candidato com sucesso: ' . $emailRequest,
                        'user_id' => Auth::id() ?? null
                    ]);
                } catch (\Throwable $exception) {
                    LogErrors::create([
                        'route' => 'reprovar/candidatura',
                        'error_message' => 'Erro ao enviar e-mail de reprovação: ' . $exception->getMessage(),
                        'user_id' => Auth::id() ?? null
                    ]);
                }
            }

            $candidatura->save();

            return $this->success_response('Candidatura reprovada.', 200);
        } catch(CustomException $exception) {
            return $this->error_response($exception->getMessage(), null, $exception->getCode());
        } catch(\Exception $exception) {
            return $this->error_response('Não foi possível aprovar a candidatura', $exception->getMessage(), 500);
        }
    }
}
