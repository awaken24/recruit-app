<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requisito extends Model
{
    use HasFactory;

    protected $fillable = [
        'vaga_id',
        'habilidade_id',
        'tempo_experiencia'
    ];

    public function vaga()
    {
        return $this->belongsTo(Vaga::class);
    }

    public function habilidade()
    {
        return $this->belongsTo(Habilidade::class);
    }
}
