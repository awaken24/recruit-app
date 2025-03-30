<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Requisito;
use App\Models\Empresa;

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
        'status'
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
        'bonus' => 'boolean'
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

    public function toArray()
    {
        $data = parent::toArray();
        $data['empresa'] = $this->empresa ? $this->empresa->toArray() : null;
        
        unset($data['updated_at']);
        return $data;
    }
}
