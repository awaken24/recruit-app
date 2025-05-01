<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoEmpresa extends Model
{
    use HasFactory;

    protected $table = 'configuracao_empresas';

    public const TEMPLATE_SUCESSO_PADRAO = <<<HTML
        Olá {{nome}}, tudo bem?<br><br>
        Temos uma ótima notícia para você! Sua candidatura para a vaga de {{vaga}} na {{empresa}} foi aprovada 🎉<br><br>
        Em breve, entraremos em contato com os próximos passos. Agradecemos por confiar no nosso processo seletivo.<br><br>
        Atenciosamente,<br>
        Equipe {{empresa}}
        HTML;
        
        public const TEMPLATE_RECUSADO_PADRAO = <<<HTML
            Olá {{nome}}, tudo bem?<br><br>
            Agradecemos muito o seu interesse na vaga de {{vaga}} na {{empresa}}. Após uma análise cuidadosa, infelizmente, optamos por seguir com outro perfil neste momento.<br><br>
            Reconhecemos o valor da sua trajetória e esperamos ter a oportunidade de nos conectarmos novamente em futuras oportunidades.<br><br>
            Desejamos sucesso em sua jornada profissional.<br><br>
            Atenciosamente,<br>
            Equipe {{empresa}}
            HTML;

    protected $fillable = [
        'empresa_id',
        'whatsapp_ativo',
        'whatsapp_token',
        'whatsapp_instance',
        'whatsapp_template',
        'whatsapp_security_token',
        'email_template_sucesso',
        'email_template_recusado'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
