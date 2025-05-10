<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome_fantasia',
        'razao_social', 
        'cnpj',
        'telefone',
        'email',
        'descricao',
        'website',
        'sem_cnpj',
        'youtube_video',
        'tipo_empresa',
        'ano_fundacao',
        'numero_funcionarios',
        'politica_remoto',
        'logo_path',
        'facebook',
        'twitter',
        'linkedin',
        'instagram',
        'tiktok',
        'youtube',
        'contato_nome',
        'contato_cargo',
        'contato_telefone',
        'como_encontrou'
    ];

    public function usuario()
    {
        return $this->morphOne(Usuario::class, 'usuarioable');
    }

    public function endereco()
    {
        return $this->morphOne(Endereco::class, 'enderecavel');
    }

    public function vagas(): HasMany
    {
        return $this->hasMany(Vaga::class);
    }

    public function configuracao()
    {
        return $this->hasOne(ConfiguracaoEmpresa::class);
    }

    public function toArray()
    {
        $data = parent::toArray();
         
        unset($data['created_at'], $data['updated_at']);
        return $data;
    }
}
