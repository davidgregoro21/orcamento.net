<?php
/**
 * Site Amanda Rodrigues Advocacia dentro do orcamento.net (prévia para a cliente).
 * Os arquivos ficam em sites_clientes/amanda-rodrigues/site (fora da pasta pública). Esta "portaria" só entrega
 * o site para quem está logado no portal com o projeto liberado (ou é administrador), e serve cada arquivo pedido.
 */
if (ob_get_level() === 0) { ob_start(); }
const AR_SLUG = 'amanda-rodrigues';
define('AR_SITE', dirname(__DIR__, 3).'/sites_clientes/amanda-rodrigues/site');
require dirname(__DIR__, 3).'/app/Core/bootstrap.php';

/** Endereço do portal a partir desta pasta (…/sites/amanda-rodrigues → …). */
function ar_portal(): string {
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $dir = preg_replace('#/sites/[^/]+$#', '', $dir);
    $uri = (string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (str_ends_with($dir, '/public') && !str_starts_with($uri, $dir.'/')) $dir = substr($dir, 0, -7);   // Hostinger: raiz encaminha para public/
    return $dir;
}
function ar_login_url(): string { return ar_portal().(cfg('urls_amigaveis') ? '/login' : '/index.php?page=login'); }

// Chegou pelo botão do portal (?acesso=...): o acesso é conferido pela sessão do portal; só limpa o endereço.
if (isset($_GET['acesso'])) { header('Location: '.strtok($_SERVER['REQUEST_URI'] ?? '/', '?')); exit; }

// Permissão: logado no portal e com este projeto liberado (administrador vê sempre).
$projeto = projeto_liberado(AR_SLUG);
if (!$projeto) {
    if (!is_logged()) { $_SESSION['voltar_para'] = (string)($_SERVER['REQUEST_URI'] ?? ''); header('Location: '.ar_login_url()); exit; }
    http_response_code(403); exit('Este site ainda não está liberado para a sua conta.');
}

// Arquivo pedido (o .htaccess desta pasta manda tudo para cá em ?f=caminho).
$f = trim(str_replace('\\', '/', (string)($_GET['f'] ?? '')), '/');
if ($f === '') $f = 'index.html';
$tipos = ['html' => 'text/html; charset=UTF-8', 'css' => 'text/css; charset=UTF-8', 'js' => 'application/javascript; charset=UTF-8',
    'svg' => 'image/svg+xml', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp', 'ico' => 'image/x-icon',
    'woff' => 'font/woff', 'woff2' => 'font/woff2', 'xml' => 'application/xml; charset=UTF-8', 'txt' => 'text/plain; charset=UTF-8'];
$ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
$caminho = realpath(AR_SITE.'/'.$f);
$raiz = realpath(AR_SITE);

header('X-Robots-Tag: noindex, nofollow');   // prévia: fora do Google

// Formulário de contato do site (envia por e-mail; responde em JSON).
if ($f === 'enviar.php') { while (ob_get_level()) ob_end_clean(); require AR_SITE.'/enviar.php'; exit; }

if (!$caminho || !$raiz || !str_starts_with($caminho, $raiz.DIRECTORY_SEPARATOR) || !is_file($caminho) || !isset($tipos[$ext])) {
    http_response_code(404); $f = '404.html'; $ext = 'html'; $caminho = AR_SITE.'/404.html';
}

while (ob_get_level()) ob_end_clean();
header('Content-Type: '.$tipos[$ext]);
if ($ext === 'html') {
    header('Cache-Control: no-store');
    $html = (string)file_get_contents($caminho);
    // Faixa de identificação da prévia: aparece só quando o site é aberto em nova aba (dentro do portal a barra do portal já mostra).
    $nome = e((string)(current_user()['nome'] ?? ''));
    $faixa = '<div id="faixa-previa-portal" style="display:none;position:fixed;left:0;right:0;bottom:0;z-index:2000;background:#101a33;color:#c9d6ff;text-align:center;font:600 13px/1.4 system-ui,sans-serif;padding:7px 12px">'
        .'<strong style="color:#fff">Rascunho – Demo do projeto</strong> · versão de apresentação liberada para '.$nome.' · <span style="color:#8fb0ff;font-weight:800">orcamento.net</span></div>'
        .'<script>try{if(window.self===window.top)document.getElementById("faixa-previa-portal").style.display="block"}catch(e){}</script>';
    $html = preg_replace('#<body([^>]*)>#i', '<body$1>'.$faixa, $html, 1);
    echo $html; exit;
}
header('Cache-Control: private, max-age=86400');
header('Content-Length: '.filesize($caminho));
readfile($caminho); exit;
