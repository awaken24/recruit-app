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

    protected $hidden = [
        'created_at',
        'updated_at',
        'id'
    ];

    public function habilidade()
    {
        return $this->belongsTo(Habilidade::class);
    }
}
