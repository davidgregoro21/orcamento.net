<?php
/**
 * Inicialização comum a todas as entradas do site (index.php).
 * Define fuso horário, tratamento de erros, cabeçalhos de segurança e sessão segura (usada no CSRF e nos avisos).
 */
require_once __DIR__.'/helpers.php';
require_once __DIR__.'/Email.php';
require_once __DIR__.'/AcessoPortal.php';

iniciar_tratamento_erros();
exigir_https();
cabecalhos_seguranca();

date_default_timezone_set((string)(cfg('timezone') ?: 'America/Sao_Paulo'));

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name((string)(cfg('session_name') ?: 'conect_sindico_sessao'));
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => conexao_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Prévia restrita: só abre para quem o portal orcamento.net liberou (desligue em config quando o site for ao ar).
exigir_acesso_portal();
