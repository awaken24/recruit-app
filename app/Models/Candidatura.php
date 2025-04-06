<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidatura extends Model{
    use HasFactory;

    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_APROVADA = 'aprovada';
    public const STATUS_REPROVADA = 'reprovada';

    protected $table = 'recruit_app.candidaturas';

    protected $fillable = [
        'vaga_id',
        'candidato_id',
        'empresa_id',
        'status',
        'compatibilidade'
    ];

    public function vaga(){
        return $this->belongsTo(Vaga::class);
    }

    public function candidato(){
        return $this->belongsTo(Candidato::class);
    }

    public function empresa(){
        return $this->belongsTo(Empresa::class);
    }
}
