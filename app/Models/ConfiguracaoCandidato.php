<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoCandidato extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidato_id',
        'notificacoes_email',
        'notificacoes_whatsapp',
        'receber_alertas_vagas',
    ];

    public function candidato()
    {
        return $this->belongsTo(Candidato::class);
    }
}
