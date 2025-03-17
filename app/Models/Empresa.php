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

    /**
     * @return array
     */
    public function toArrayProfile()
    {
        return [
            'id' => $this->id,
            'nome_fantasia' => $this->nome_fantasia,
            'razao_social' => $this->razao_social,
            'cnpj' => $this->sem_cnpj ? 'Não possui CNPJ' : $this->cnpj,
            'telefone' => $this->telefone,
            'email' => $this->email,
            'descricao' => $this->descricao,
            'website' => $this->website,
            'ano_fundacao' => $this->ano_fundacao,
            'numero_funcionarios' => $this->numero_funcionarios,
            'politica_remoto' => $this->politica_remoto,
            'tipo_empresa' => $this->tipo_empresa,
            'quantidade_vagas' => $this->vagas()->count(),
            'redes_sociais' => [
                'facebook' => $this->facebook,
                'twitter' => $this->twitter,
                'linkedin' => $this->linkedin,
                'instagram' => $this->instagram,
                'tiktok' => $this->tiktok,
                'youtube' => $this->youtube,
            ],
            'logo_url' => $this->logo_path ? asset($this->logo_path) : null,
            'contato' => [
                'nome' => $this->contato_nome,
                'cargo' => $this->contato_cargo,
                'telefone' => $this->contato_telefone,
            ],
            'youtube_video' => $this->youtube_video,
            'como_encontrou' => $this->como_encontrou
        ];
    }

    public function toArray()
    {
        $data = parent::toArray();
         
        unset($data['created_at'], $data['updated_at']);
        return $data;
    }
}
