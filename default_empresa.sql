-- Esse script cria uma empresa, um usuário vinculado a ela e três vagas

INSERT INTO empresas (
    nome_fantasia,
    razao_social,
    cnpj,
    email,
    contato_nome,
    contato_cargo,
    contato_telefone,
    como_encontrou
) VALUES (
    'Tech Solutions LTDA',
    'Tech Solutions Tecnologia Ltda',
    '00.000.000/0001-00',
    'contato@techsolutions.com',
    'João da Silva',
    'Gerente Comercial',
    '(11) 99999-9999',
    'Indicação de cliente'
);

SET @empresa_id = LAST_INSERT_ID();

INSERT INTO usuarios (
    nome,
    email,
    password,
    usuarioable_id,
    usuarioable_type,
    perfil_completo
) VALUES (
    'João da Silva',
    'joao@techsolutions.com',
    'senha_criptografada',
    @empresa_id,
    'App\\Models\\Empresa',
    false
);

INSERT INTO vagas (
    titulo,
    empresa_id,
    status,
    perfil,
    nivel_experiencia,
    descricao,
    requisitos,
    modelo_trabalho,
    tipo_contrato,
    faixa_salarial,
    divulgar_salario,
    vale_refeicao,
    vale_alimentacao,
    vale_transporte,
    plano_saude,
    plano_odontologico,
    seguro_vida,
    vale_estacionamento,
    academia_gympass,
    bonus
) VALUES (
    'Desenvolvedor Fullstack Pleno',
    @empresa_id,
    'ativa',
    'fullstack',
    'pleno',
    'Desenvolver e manter aplicações web modernas.',
    'Conhecimento em PHP, Laravel, JavaScript, Vue.js.',
    'remoto',
    'clt',
    '3000-4000',
    true,
    true,
    true,
    true,
    true,
    true,
    true,
    true,
    true,
    true
);

SET @vaga1_id = LAST_INSERT_ID();

INSERT INTO requisitos (vaga_id, habilidade_id, tempo_experiencia) VALUES
(@vaga1_id, 1, '2-3'),
(@vaga1_id, 2, '2-3'),
(@vaga1_id, 3, '2-3');

INSERT INTO vagas (
    titulo,
    empresa_id,
    status,
    perfil,
    nivel_experiencia,
    descricao,
    requisitos,
    modelo_trabalho,
    tipo_contrato,
    faixa_salarial,
    divulgar_salario,
    vale_refeicao,
    vale_alimentacao,
    vale_transporte,
    plano_saude,
    plano_odontologico,
    seguro_vida,
    vale_estacionamento,
    academia_gympass,
    bonus
) VALUES (
    'Desenvolvedor Frontend Pleno',
    @empresa_id,
    'ativa',
    'frontend',
    'pleno',
    'Desenvolver interfaces modernas e responsivas.',
    'Conhecimento em HTML, CSS, JavaScript, React.',
    'remoto',
    'clt',
    '3000-4000',
    true,
    true,
    true,
    true,
    true,
    true,
    true,
    true,
    true,
    true
);

SET @vaga2_id = LAST_INSERT_ID();

INSERT INTO requisitos (vaga_id, habilidade_id, tempo_experiencia) VALUES
(@vaga2_id, 4, '2-3'),
(@vaga2_id, 5, '2-3'),
(@vaga2_id, 6, '2-3');

INSERT INTO vagas (
    titulo,
    empresa_id,
    status,
    perfil,
    nivel_experiencia,
    descricao,
    requisitos,
    modelo_trabalho,
    tipo_contrato,
    faixa_salarial,
    divulgar_salario,
    vale_refeicao,
    vale_alimentacao,
    vale_transporte,
    plano_saude,
    plano_odontologico,
    seguro_vida,
    vale_estacionamento,
    academia_gympass,
    bonus
) VALUES (
    'Desenvolvedor Backend Sênior',
    @empresa_id,
    'ativa',
    'backend',
    'senior',
    'Desenvolver APIs robustas e escaláveis.',
    'Conhecimento em Node.js, Python, Docker.',
    'remoto',
    'pj',
    '3000-4000',
    true,
    true,
    true,
    true,
    true,
    true,
    true,
    true,
    true,
    true
);

SET @vaga3_id = LAST_INSERT_ID();

INSERT INTO requisitos (vaga_id, habilidade_id, tempo_experiencia) VALUES
(@vaga3_id, 7, '2-3'),
(@vaga3_id, 8, '2-3'),
(@vaga3_id, 9, '2-3');