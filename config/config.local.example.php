<?php
/**
 * Copie para config/config.local.php no servidor e ajuste.
 * Este arquivo sobrescreve config/config.php e NÃO é enviado ao Git.
 */
return [
    'ambiente' => 'producao',
    'forcar_https' => true,
    'app_url' => 'https://orcamento.net',
    'db' => [
        'host' => 'localhost',
        'name' => 'orcamento_net',
        'user' => 'orcamento_app',            // usuário próprio do sistema, nunca o root
        'pass' => 'TROQUE-POR-UMA-SENHA-FORTE',
    ],
    // 64 caracteres hexadecimais: php -r "echo bin2hex(random_bytes(32));"  (a mesma no site do cliente)
    'previa_chave' => 'TROQUE-PELA-CHAVE-GERADA',
    'contato' => ['responsavel' => 'Seu Nome', 'email' => 'contato@orcamento.net', 'whatsapp' => '(54) 99999-9999'],
    'mail' => [
        'host' => 'smtp.SEU-PROVEDOR.com.br', 'port' => 587, 'seguranca' => 'tls',
        'usuario' => 'nao-responda@orcamento.net', 'senha' => 'TROQUE-PELA-SENHA',
        'remetente' => 'nao-responda@orcamento.net', 'nome' => 'orcamento.net', 'destino' => 'contato@orcamento.net',
    ],
];
