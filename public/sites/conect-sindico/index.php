<?php
// Buffer de saída: permite redirects HTTP mesmo quando uma página processa POST (formulário de contato).
if (ob_get_level() === 0) { ob_start(); }
// O site da Conect roda dentro do orcamento.net: o código fica em sites_clientes/conect-sindico (fora da pasta pública).
define('CONECT_RAIZ', dirname(__DIR__, 3).'/sites_clientes/conect-sindico');
define('PASTA_PUBLICA', __DIR__);
require CONECT_RAIZ.'/app/Core/bootstrap.php';

$page = (string)($_GET['page'] ?? 'home');
$page = str_replace('-', '_', $page); $_GET['page'] = $page; // endereços amigáveis usam hífen

// Site antigo: /blog/detalhes/slug → /blog/slug (mantém os links já indexados no Google e compartilhados).
if ($page === 'blog_antigo') {
    $slug = (string)($_GET['slug'] ?? '');
    header('Location: '.(artigo_por_slug($slug) ? url_pagina('artigo', ['slug' => $slug]) : url_pagina('blog')), true, 301); exit;
}
// Mapa do site para o Google (/sitemap.xml).
if ($page === 'sitemap') {
    header('Content-Type: application/xml; charset=UTF-8');
    $urls = ['home', 'sobre', 'servicos', 'blog', 'contato'];
    echo '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    foreach ($urls as $u) echo '  <url><loc>'.e(url_absoluta(url_pagina($u))).'</loc></url>'."\n";
    foreach (conteudo('artigos') as $a) echo '  <url><loc>'.e(url_absoluta(url_pagina('artigo', ['slug' => $a['slug']]))).'</loc></url>'."\n";
    echo '</urlset>'; exit;
}

$routes = require CONECT_RAIZ.'/app/Core/routes.php';
if (!isset($routes[$page])) { http_response_code(404); $page = 'nao_encontrada'; $_GET['page'] = $page; }
[$file, $tituloPagina, $descricaoPagina] = $routes[$page];

// Executa a página ANTES do layout. Assim todo POST/redirect acontece antes de qualquer HTML ser enviado.
// A página pode trocar $tituloPagina, $descricaoPagina e $imagemPagina (usado no artigo do blog).
$imagemPagina = null;
ob_start();
require CONECT_RAIZ.'/app/Views/pages/'.$file;
$contentHtml = ob_get_clean();

require CONECT_RAIZ.'/app/Views/layouts/site.php';
