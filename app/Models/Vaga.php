<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Requisito;
use App\Models\{
    Empresa,
    Candidato
};
use Carbon\Carbon;

class Vaga extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'titulo', 
        'perfil',
        'nivel_experiencia',
        'descricao',
        'requisitos',
        'modelo_trabalho',
        'endereco_trabalho',
        'cidade_trabalho',
        'comentarios_hibrido',
        'tipo_contrato',
        'faixa_salarial',
        'divulgar_salario',
        'vale_refeicao',
        'vale_alimentacao',
        'vale_transporte',
        'plano_saude',
        'plano_odontologico',
        'seguro_vida',
        'vale_estacionamento',
        'academia_gympass',
        'bonus',
        'status',
        'receber_candidaturas_ate'
    ];

    protected $casts = [
        'divulgar_salario' => 'boolean',
        'vale_refeicao' => 'boolean',
        'vale_alimentacao' => 'boolean',
        'vale_transporte' => 'boolean',
        'plano_saude' => 'boolean',
        'plano_odontologico' => 'boolean',
        'seguro_vida' => 'boolean',
        'vale_estacionamento' => 'boolean',
        'academia_gympass' => 'boolean',
        'bonus' => 'boolean',
        'receber_candidaturas_ate' => 'date'
    ];

    public function requisitosHabilidades()
    {
        return $this->hasMany(Requisito::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function candidaturas()
    {
        return $this->hasMany(Candidatura::class);
    }

    public function calcularCompatibilidade(Candidato $candidato): float
    {
        $pesoTecnico = 70;
        $pesoPerfil = 30;

        $requisitos = $this->requisitosHabilidades;
        $habilidadesCandidato = $candidato->habilidades;

        $pontos = 0;
        $totalRequisitos = $requisitos->count();

        if ($totalRequisitos > 0 && $habilidadesCandidato->isNotEmpty()) {
            foreach ($requisitos as $requisito) {
                $habilidadeEncontrada = $habilidadesCandidato->first(function ($habilidade) use ($requisito) {
                    return $habilidade->id === $requisito->habilidade_id;
                });

                if ($habilidadeEncontrada) {
                    $tempoCandidato = is_numeric($habilidadeEncontrada->tempo_experiencia)
                        ? (int) $habilidadeEncontrada->tempo_experiencia
                        : (int) explode('-', $habilidadeEncontrada->tempo_experiencia)[0];

                    $tempoRequisito = is_numeric($requisito->tempo_experiencia)
                        ? (int) $requisito->tempo_experiencia
                        : (int) explode('-', $requisito->tempo_experiencia)[0];

                    $pontos += $tempoCandidato >= $tempoRequisito ? 1 : 0.5;
                }
            }
        }

        $scoreTecnico = ($totalRequisitos > 0)
            ? ($pontos / $totalRequisitos) * $pesoTecnico
            : 0;

        $perfilCompativel = (
            $this->nivel_experiencia === $candidato->experienceLevel &&
            $this->tipo_contrato === $candidato->tipo_contrato
        );

        $scorePerfil = $perfilCompativel ? $pesoPerfil : 0;

        return min(100, round($scoreTecnico + $scorePerfil));
    }

    public function toArray()
    {
        $data = parent::toArray();
        $data['empresa'] = $this->empresa ? $this->empresa->toArray() : null;
        
        unset($data['updated_at']);
        return $data;
    }
}
