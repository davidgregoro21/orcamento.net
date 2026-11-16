<?php
/**
 * Configuração de PRODUÇÃO (Hostinger). No servidor, RENOMEIE este arquivo para config.local.php.
 * Preencha os 3 campos do banco com os dados criados em hPanel → Bancos de dados → MySQL.
 */
return [
    'ambiente' => 'producao',
    'forcar_https' => true,
    'app_url' => 'https://orcamento.net',
    'db' => [
        'host' => 'localhost',
        'name' => 'u000000000_orcamento',      // PREENCHER: nome do banco (começa com u + números)
        'user' => 'u000000000_orcamento',      // PREENCHER: usuário do banco
        'pass' => 'COLOQUE-A-SENHA-DO-BANCO',  // PREENCHER: senha do banco
    ],
    // Seu login de administrador: criado sozinho no primeiro acesso ao site. Depois troque a senha em "Minha senha".
    'admin_inicial' => ['nome' => '', 'email' => '', 'senha' => ''],   // PREENCHER: nome, e-mail e senha forte
    // Chave dos links de prévia (o site da Conect, dentro do portal, lê esta mesma chave automaticamente).
    'previa_chave' => 'b91ae6385ea49d37353373c8cc68e0289f91af7389a017cc7a0ac4567a8adc61',
    'contato' => [
        'responsavel' => '',                    // PREENCHER: seu nome
        'email' => 'contato@orcamento.net',
        'whatsapp' => '',                       // PREENCHER: ex. (54) 99999-9999
        'cidade' => 'Nova Santa Rita/RS',
    ],
    // E-mail da Hostinger (hPanel → E-mails). Crie a caixa nao-responda@orcamento.net e coloque a senha.
    'mail' => [
        'host' => 'smtp.hostinger.com', 'port' => 465, 'seguranca' => 'ssl',
        'usuario' => 'nao-responda@orcamento.net', 'senha' => '',   // PREENCHER: senha da caixa de e-mail
        'remetente' => 'nao-responda@orcamento.net', 'nome' => 'orcamento.net',
        'destino' => 'contato@orcamento.net',
    ],
];
