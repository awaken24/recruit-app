<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Endereco;

class Candidato extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'nome_completo',
        'email',
        'telefone',
        'data_nascimento',
        'cpf',
        'genero',
    ];

    public function endereco()
    {
        return $this->morphMany(Endereco::class, 'enderecavel');
    }

    public function usuario()
    {
        return $this->morphOne(Usuario::class, 'usuarioable');
    }
}
