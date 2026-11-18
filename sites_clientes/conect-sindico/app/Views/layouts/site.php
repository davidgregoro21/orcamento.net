<?php
$nomeSite = (string)cfg('app_name');
$titulo = $tituloPagina ? $tituloPagina.' | '.$nomeSite : $nomeSite.' – Síndico profissional em '.empresa('cidade');
$descricao = (string)($descricaoPagina ?? '');
$canonical = url_absoluta(strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/');
$imagemOg = url_absoluta($imagemPagina ?? asset('assets/img/og-conect.png'));
$menu = ['home' => 'Início', 'sobre' => 'Sobre', 'servicos' => 'Serviços', 'blog' => 'Blog', 'contato' => 'Contato'];
$ativo = $page === 'artigo' ? 'blog' : $page;
// Dados estruturados (Google): empresa local com endereço, telefone e área de atendimento.
$jsonLd = [
    '@context' => 'https://schema.org', '@type' => 'LocalBusiness', 'name' => empresa('nome'),
    'description' => 'Síndico profissional e gestão de condomínios em '.empresa('regiao').'.',
    'url' => url_absoluta(url_pagina('home')), 'logo' => url_absoluta(asset('assets/img/logo.png')),
    'telephone' => '+55'.so_digitos(empresa('telefone')), 'email' => empresa('email'),
    'address' => ['@type' => 'PostalAddress', 'streetAddress' => empresa('endereco'), 'addressLocality' => empresa('cidade'), 'addressRegion' => empresa('uf'), 'postalCode' => empresa('cep'), 'addressCountry' => 'BR'],
    'areaServed' => empresa('regiao'), 'founder' => empresa('gestor'),
];
?><!doctype html>
<html lang="pt-BR">
<head>
<script>try { if (window.self !== window.top) document.documentElement.className += ' no-portal'; } catch (e) { document.documentElement.className += ' no-portal'; }</script>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($titulo)?></title>
<?php if ($descricao !== ''): ?><meta name="description" content="<?=e($descricao)?>"><?php endif; ?>
<?php if ($page === 'nao_encontrada' || ambiente() !== 'producao' || acesso_portal_ativo()): ?><meta name="robots" content="noindex"><?php endif; ?>
<link rel="canonical" href="<?=e($canonical)?>">
<meta property="og:type" content="<?= $page === 'artigo' ? 'article' : 'website' ?>">
<meta property="og:locale" content="pt_BR">
<meta property="og:site_name" content="<?=e($nomeSite)?>">
<meta property="og:title" content="<?=e($tituloPagina ?: $nomeSite)?>">
<?php if ($descricao !== ''): ?><meta property="og:description" content="<?=e($descricao)?>"><?php endif; ?>
<meta property="og:image" content="<?=e($imagemOg)?>">
<meta property="og:url" content="<?=e($canonical)?>">
<meta name="theme-color" content="#0f3d47">
<link rel="icon" type="image/png" href="<?=asset('assets/img/favicon.png')?>">
<link rel="stylesheet" href="<?=asset('assets/css/site.css')?>">
<script type="application/ld+json"><?=json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)?></script>
</head>
<body class="pg-<?=e(str_replace('_', '-', $page))?>">
<?php if (ambiente() === 'homologacao'): ?><div class="faixa-ambiente">HOMOLOGAÇÃO · VERSÃO DE APRESENTAÇÃO</div><?php endif; ?>
<?php if ($visitante = visitante_previa()): ?><div class="faixa-previa"><strong>Rascunho – Demo do projeto</strong> · versão de apresentação liberada para <?=e($visitante)?> · <span>orcamento.net</span></div><?php endif; ?>
<a class="pular" href="#conteudo">Pular para o conteúdo</a>

<header class="topo" id="topo">
  <div class="container topo__linha">
    <a class="topo__marca" href="<?=url_pagina('home')?>" aria-label="<?=e($nomeSite)?> – página inicial">
      <img src="<?=asset('assets/img/logo.png')?>" alt="<?=e($nomeSite)?>" width="59" height="64">
    </a>
    <nav class="menu" id="menu" aria-label="Menu principal">
      <?php foreach ($menu as $p => $rotulo): ?>
        <a href="<?=url_pagina($p)?>"<?= $ativo === $p ? ' class="ativo" aria-current="page"' : '' ?>><?=e($rotulo)?></a>
      <?php endforeach; ?>
      <a class="menu__portal<?= ($page ?? '') === 'area_condomino' ? ' ativo' : '' ?>" href="<?=url_pagina('area_condomino')?>"><?=icone('usuario')?> Área do condômino</a>
    </nav>
    <div class="topo__acoes">
      <a class="btn btn--whats btn--pequeno" href="<?=e(link_whatsapp())?>" target="_blank" rel="noopener"><?=icone('whatsapp')?><span>WhatsApp</span></a>
      <button class="topo__menu" type="button" aria-controls="menu" aria-expanded="false" aria-label="Abrir menu"><?=icone('menu', 'ico ico--abrir')?><?=icone('fechar', 'ico ico--fechar')?></button>
    </div>
  </div>
</header>

<main id="conteudo">
  <?php $avisos = pull_flashes(); if ($avisos): ?>
    <div class="container avisos">
      <?php foreach ($avisos as $f): ?><div class="aviso aviso--<?=e($f['type'])?>" role="status"><?=e($f['message'])?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>
  <?=$contentHtml?>
</main>

<footer class="rodape">
  <div class="container rodape__grade">
    <div class="rodape__marca">
      <img class="rodape__logo" src="<?=asset('assets/img/logo-rodape-branco.png')?>" alt="<?=e($nomeSite)?>" width="81" height="89" loading="lazy">
      <p>Gestão profissional de condomínios residenciais, comerciais e mistos em <?=e(empresa('regiao'))?>.</p>
      <div class="rodape__redes">
        <?php foreach (['instagram', 'facebook', 'linkedin'] as $rede): if (empresa($rede) === '') continue; ?>
          <a href="<?=e(empresa($rede))?>" target="_blank" rel="noopener" aria-label="<?=ucfirst($rede)?>"><?=icone($rede)?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <div>
      <h2 class="rodape__titulo">Navegação</h2>
      <ul class="rodape__lista">
        <?php foreach ($menu as $p => $rotulo): ?><li><a href="<?=url_pagina($p)?>"><?=e($rotulo)?></a></li><?php endforeach; ?>
        <li><a href="<?=url_pagina('area_condomino')?>">Área do condômino</a></li>
      </ul>
    </div>
    <div>
      <h2 class="rodape__titulo">Contato</h2>
      <ul class="rodape__lista rodape__contato">
        <li><?=icone('local')?><span><?=e(empresa('endereco'))?><br><?=e(empresa('bairro'))?> · <?=e(empresa('cidade'))?>/<?=e(empresa('uf'))?></span></li>
        <li><?=icone('telefone')?><a href="<?=e(link_telefone(empresa('telefone')))?>"><?=e(empresa('telefone'))?></a></li>
        <li><?=icone('whatsapp')?><a href="<?=e(link_whatsapp())?>" target="_blank" rel="noopener"><?=e(empresa('whatsapp'))?></a></li>
        <li><?=icone('email')?><a href="mailto:<?=e(empresa('email'))?>"><?=e(empresa('email'))?></a></li>
      </ul>
    </div>
  </div>
  <div class="container rodape__base">
    <span>© <?=date('Y')?> <?=e(empresa('nome'))?></span>
    <a href="<?=url_pagina('privacidade')?>">Política de privacidade</a>
  </div>
</footer>

<a class="whats-flutuante" href="<?=e(link_whatsapp())?>" target="_blank" rel="noopener" aria-label="Conversar no WhatsApp"><?=icone('whatsapp')?></a>
<script src="<?=asset('assets/js/site.js')?>" defer></script>
</body>
</html>
