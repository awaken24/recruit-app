<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oportunidades extends Model
{
    use HasFactory;

    protected $table = 'oportunidades';

    protected $fillable = [
        'vaga_id',
        'candidato_id',
        'compatibilidade',
        'status'
    ];

    public function vaga()
    {
        return $this->belongsTo(Vaga::class);
    }

    public function candidato(){
        return $this->belongsTo(Candidato::class);
    }
}
