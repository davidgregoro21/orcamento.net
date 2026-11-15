<?php
$nomeSite = (string)cfg('app_name');
$titulo = $tituloPagina ? $tituloPagina.' | '.$nomeSite : $nomeSite.' – Sites, sistemas e suporte com preço fechado e prévia antes de ir ao ar';
$menuPub = ['inicio' => 'Início', 'portfolio' => 'Portfólio', 'sistemas' => 'Soluções', 'servicos' => 'Planos', 'sobre' => 'Sobre', 'contato' => 'Contato'];
$ativoPub = $page === 'sistema' ? 'sistemas' : $page;
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($titulo)?></title>
<meta name="description" content="Orçamento grátis de site institucional, sistema web, portal do cliente, manutenção, hospedagem e automação. Proposta clara por escrito e prévia do projeto no portal antes de ir ao ar.">
<?php if (ambiente() !== 'producao'): ?><meta name="robots" content="noindex"><?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:locale" content="pt_BR">
<meta property="og:title" content="<?=e($nomeSite)?> – Sites e sistemas com preço fechado">
<meta property="og:description" content="Proposta clara por escrito e prévia do projeto no portal antes de ir ao ar.">
<meta property="og:image" content="<?=e(url_absoluta(asset('assets/img/og-orcamento.png')))?>">
<meta name="theme-color" content="#101a33">
<link rel="icon" href="<?=asset('assets/img/favicon.svg')?>" type="image/svg+xml">
<link rel="stylesheet" href="<?=asset('assets/css/app.css')?>">
</head>
<body class="publico pg-<?=e(str_replace('_', '-', $page))?>">
<header class="pub-topo" id="topo">
  <div class="container pub-topo__linha">
    <a href="<?=url_pagina('inicio')?>" aria-label="<?=e($nomeSite)?> – início"><?php require __DIR__.'/../partials/logo.php'; ?></a>
    <nav class="pub-menu" id="pub-menu" aria-label="Menu">
      <?php foreach ($menuPub as $pg => $rotulo): ?><a href="<?=url_pagina($pg)?>"<?= $ativoPub === $pg ? ' class="ativo" aria-current="page"' : '' ?>><?=e($rotulo)?></a><?php endforeach; ?>
      <a class="pub-menu__login" title="Acesso de clientes e administração" href="<?=url_pagina(is_logged() ? pagina_inicial() : 'login')?>"><?=icone('portal')?> <?= is_logged() ? (is_admin() ? 'Painel' : 'Minha área') : 'Entrar' ?></a>
    </nav>
    <div class="pub-topo__acoes">
      <a class="pub-topo__login" title="Acesso de clientes e administração" href="<?=url_pagina(is_logged() ? pagina_inicial() : 'login')?>"><?=icone('portal')?> <?= is_logged() ? (is_admin() ? 'Painel' : 'Minha área') : 'Entrar' ?></a>
      <a class="btn btn--primario btn--p" href="<?=url_pagina('contato')?>">Pedir orçamento</a>
      <button class="pub-topo__menu" type="button" aria-controls="pub-menu" aria-expanded="false" aria-label="Abrir menu"><?=icone('menu', 'ico ico--abrir')?><?=icone('fechar', 'ico ico--fechar')?></button>
    </div>
  </div>
</header>
<main id="conteudo">
  <?php $avisos = pull_flashes(); if ($avisos): ?><div class="container avisos"><?php foreach ($avisos as $f): ?><div class="aviso aviso--<?=e($f['type'])?>" role="status"><?=e($f['message'])?></div><?php endforeach; ?></div><?php endif; ?>
  <?=$contentHtml?>
</main>
<footer class="pub-rodape">
  <div class="container pub-rodape__grade">
    <div>
      <?php $logoClaro = true; require __DIR__.'/../partials/logo.php'; ?>
      <p>Sites, sistemas web e suporte em tecnologia com preço fechado e prévia antes de ir ao ar.</p>
    </div>
    <div><h4>Soluções</h4><ul><?php try { foreach (db()->query('SELECT slug, nome FROM sistemas WHERE ativo = 1 ORDER BY destaque DESC, ordem, nome LIMIT 5')->fetchAll() as $s): ?><li><a href="<?=url_pagina('sistema', ['s' => $s['slug']])?>"><?=e($s['nome'])?></a></li><?php endforeach; } catch (Throwable $e) {} ?></ul></div>
    <div><h4>Navegação</h4><ul><?php foreach ($menuPub as $pg => $rotulo): ?><li><a href="<?=url_pagina($pg)?>"><?=e($rotulo)?></a></li><?php endforeach; ?><li><a href="<?=url_pagina('login')?>">Entrar (clientes e administração)</a></li></ul></div>
    <div><h4>Fale conosco</h4><ul>
      <li><a href="<?=url_pagina('contato')?>">Pedir orçamento pelo site</a></li>
      <?php if (contato('whatsapp')): ?><li><a href="<?=e(link_whatsapp('Olá! Quero um orçamento.'))?>" target="_blank" rel="noopener">WhatsApp <?=e(contato('whatsapp'))?></a></li><?php endif; ?>
      <?php if (contato('cidade')): ?><li><?=e(contato('cidade'))?></li><?php endif; ?>
    </ul></div>
  </div>
  <div class="container pub-rodape__base"><span>© <?=date('Y')?> orcamento.net</span><span>Orçamento grátis e sem compromisso</span></div>
</footer>
<?php if (contato('whatsapp')): ?><a class="whats-flutuante" href="<?=e(link_whatsapp('Olá! Quero um orçamento.'))?>" target="_blank" rel="noopener" aria-label="Falar no WhatsApp"><?=icone('whatsapp')?></a><?php endif; ?>
<script src="<?=asset('assets/js/app.js')?>" defer></script>
</body>
</html>
