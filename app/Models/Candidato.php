<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{
    Endereco,
    Experiencia,
    Oportunidades,
    ConfiguracaoCandidato
};

class Candidato extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'sobrenome',
        'cpf',
        'descricao',
        'experienceLevel',
        'foco_carreira',
        'gitHub',
        'linkedIn',
        'nivelIngles',
        'pcd',
        'salario_desejado',
        'status_busca',
        'telefone',
        'tipo_contrato',
        'tipo_empresa',
        'titulo',
        'trabalho_remoto',
        'foto_perfil'
    ];

    public function endereco()
    {
        return $this->morphMany(Endereco::class, 'enderecavel');
    }

    public function usuario()
    {
        return $this->morphOne(Usuario::class, 'usuarioable');
    }

    public function experiencias()
    {
        return $this->hasMany(Experiencia::class);
    }

    public function candidatura(){
        return $this->hasMany(Candidatura::class, 'candidato_id');
    }

    public function oportunidades()
    {
        return $this->hasMany(Oportunidades::class, 'candidato_id');
    }

    public function configuracao()
    {
        return $this->hasOne(ConfiguracaoCandidato::class);
    }

    public function habilidades()
    {
        return $this->belongsToMany(Habilidade::class, 'habilidade_candidatos')
                    ->withPivot('tempo_experiencia')
                    ->withTimestamps();
    }
}
