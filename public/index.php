<?php
// Buffer de saída: permite redirects HTTP mesmo quando uma página processa POST dentro do layout.
if (ob_get_level() === 0) { ob_start(); }
if (!is_file(__DIR__.'/../storage/.instalado')) { header('Location: instalar.php'); exit; }
require __DIR__.'/../app/Core/bootstrap.php';

$page = str_replace('-', '_', (string)($_GET['page'] ?? 'inicio')); $_GET['page'] = $page;

if ($page === 'logout') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(pagina_inicial());
    verify_csrf();
    if (is_logged()) audit('logout');
    $_SESSION = []; session_regenerate_id(true); session_destroy();
    session_start(); flash('info', 'Você saiu do portal.');
    redirect('login');
}

// "Visualizar meu site" em nova aba: confere a permissão no servidor e abre a demonstração com link assinado (vale poucos minutos).
if ($page === 'abrir') {
    exigir_login();
    $p = registro_do_cliente('projetos', (int)($_GET['id'] ?? 0), 'AND ativo = 1');
    if (!$p) { http_response_code(403); flash('erro', 'Esse projeto não está liberado para a sua conta.'); redirect(pagina_inicial()); }
    audit('abrir_site', $p['slug']);
    header('Location: '.url_previa($p, current_user())); exit;
}

// Download de arquivo: só do próprio cliente (e marcado como visível) ou administrador.
if ($page === 'baixar') {
    exigir_login();
    $a = registro_do_cliente('arquivos', (int)($_GET['id'] ?? 0), 'AND visivel_cliente = 1');
    $caminho = $a ? caminho_documento($a['arquivo']) : '';
    if (!$a || !is_file($caminho)) { http_response_code(404); flash('erro', 'Arquivo não encontrado.'); redirect(pagina_inicial()); }
    audit('baixar_arquivo', '#'.$a['id'].' '.$a['nome']);
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: '.($a['mime'] ?: 'application/octet-stream'));
    header('Content-Length: '.filesize($caminho));
    header("Content-Disposition: attachment; filename*=UTF-8''".rawurlencode($a['nome']));
    header('X-Content-Type-Options: nosniff');
    readfile($caminho); exit;
}

$routes = require __DIR__.'/../app/Core/routes.php';
// Compatibilidade com endereços da versão anterior.
$antigas = ['portal' => 'painel', 'usuarios' => 'admin_usuarios', 'pedidos' => 'admin_pedidos', 'catalogo' => 'admin_catalogo'];
if (isset($antigas[$page])) redirect($antigas[$page] === 'painel' ? pagina_inicial() : $antigas[$page]);
if (!isset($routes[$page])) { http_response_code(404); $page = 'nao_encontrada'; $_GET['page'] = $page; }
[$file, $acesso, $layout, $tituloPagina] = $routes[$page];
if ($acesso === 'login') exigir_login();
if ($acesso === 'admin') exigir_admin();
if ($acesso === 'cliente') { exigir_login(); if (is_admin()) redirect('admin'); }
// Modo demo: o cliente usa só "Projetos" e a visualização do site.
if (modo_demo() && in_array($page, ['painel', 'projeto', 'orcamentos', 'orcamento', 'solicitacoes', 'solicitacao', 'arquivos'], true)) redirect('projetos');
// Senha provisória: obriga a troca antes de usar o portal.
if (is_logged() && !empty(current_user()['deve_trocar_senha']) && $acesso !== 'publico' && $page !== 'minha_senha') redirect('minha_senha');

// Executa a página ANTES do layout: todo POST/redirect acontece antes de qualquer HTML.
ob_start();
require __DIR__.'/../app/Views/'.$file;
$contentHtml = ob_get_clean();
require __DIR__.'/../app/Views/layouts/'.$layout.'.php';
