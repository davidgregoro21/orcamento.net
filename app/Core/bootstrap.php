<?php
/**
 * Inicialização comum a todas as entradas do portal (index.php e instalar.php).
 * Define fuso horário, sessão segura e revalida o usuário logado a cada requisição.
 */
require_once __DIR__.'/Database.php';
require_once __DIR__.'/helpers.php';
require_once __DIR__.'/Email.php';
require_once __DIR__.'/Auth.php';
require_once __DIR__.'/Migracoes.php';
require_once __DIR__.'/Arquivos.php';
require_once __DIR__.'/Sementes.php';

iniciar_tratamento_erros();
exigir_https();
cabecalhos_seguranca();

date_default_timezone_set((string)(cfg('timezone') ?: 'America/Sao_Paulo'));

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', '86400');
    session_name((string)(cfg('session_name') ?: 'orcamento_net_sessao'));
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => conexao_https(), 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

if (!defined('SEM_BANCO')) {
    garantir_banco_atualizado();   // aplica atualizações do banco pendentes (uma vez)
    refresh_logged_user();
}
