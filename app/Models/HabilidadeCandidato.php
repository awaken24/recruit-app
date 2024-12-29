<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HabilidadeCandidato extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidato_id',
        'habilidade_id',
        'experiencia',
    ];
}
