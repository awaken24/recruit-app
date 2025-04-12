<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoEmpresa extends Model
{
    use HasFactory;

    protected $table = 'configuracao_empresas';

    protected $fillable = [
        'empresa_id',
        'whatsapp_ativo',
        'whatsapp_token',
        'whatsapp_instance',
        'whatsapp_template',
        'whatsapp_security_token',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
