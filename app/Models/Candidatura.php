<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidatura extends Model{
    use HasFactory;

    protected $table = 'recruit_app.candidaturas';

    protected $fillable = [
        'vaga_id',
        'candidato_id',
        'empresa_id'
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
