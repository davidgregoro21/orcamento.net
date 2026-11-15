<?php
/**
 * Copie para config/config.local.php no servidor e ajuste.
 * Este arquivo sobrescreve config/config.php e NÃO é enviado ao Git.
 */
return [
    'ambiente' => 'producao',
    'forcar_https' => true,
    'app_url' => 'https://conectsindico.com.br',
    // Servidor de e-mail da hospedagem (ex.: Hostinger, Locaweb, HostGator) para o formulário de contato.
    'mail' => [
        'host' => 'smtp.SEU-PROVEDOR.com.br',
        'port' => 587,
        'seguranca' => 'tls',          // tls (porta 587) ou ssl (porta 465)
        'usuario' => 'site@conectsindico.com.br',
        'senha' => 'TROQUE-PELA-SENHA',
        'remetente' => 'site@conectsindico.com.br',
        'nome' => 'Site Conect Síndico',
        'destino' => 'contato@conectsindico.com.br',
    ],
];
