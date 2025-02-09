<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experiencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidato_id',
        'empresa',
        'cargo',
        'mesInicio',
        'anoInicio',
        'mesFim',
        'anoFim',
        'trabalhoAtual',
        'descricao',
    ];

    public function candidato()
    {
        return $this->belongsTo(Candidato::class);
    }
}
