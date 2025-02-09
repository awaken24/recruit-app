<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\{
    Endereco, 
    Experiencia 
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
}
