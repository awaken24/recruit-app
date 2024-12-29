<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisito extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'vaga_id',
        'habilidade_id',
        'experiencia_min',
        'experiencia_max',
        'obrigatorio',
    ];
}
