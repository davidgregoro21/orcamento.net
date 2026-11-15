<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e(($tituloPagina ? $tituloPagina.' | ' : '').cfg('app_name'))?></title>
<meta name="robots" content="noindex">
<meta name="theme-color" content="#101a33">
<link rel="icon" href="<?=asset('assets/img/favicon.svg')?>" type="image/svg+xml">
<link rel="stylesheet" href="<?=asset('assets/css/app.css')?>">
</head>
<body class="guest">
<div class="guest__grade">
  <section class="guest__arte" aria-hidden="true">
    <div class="guest__arte-conteudo">
      <?php $logoClaro = true; require __DIR__.'/../partials/logo.php'; ?>
      <h2>Acompanhe seu projeto antes de ele ir ao ar.</h2>
      <p>No portal você vê a prévia do site ou sistema, a proposta e as próximas etapas, com acesso exclusivo.</p>
      <div class="guest__janela">
        <div class="guest__janela-barra"><i></i><i></i><i></i><span>prévia do projeto</span></div>
        <div class="guest__janela-corpo"><b></b><b></b><b class="curta"></b><div class="guest__blocos"><span></span><span></span><span></span></div></div>
      </div>
    </div>
  </section>
  <main class="guest__acesso">
    <div class="guest__cartao">
      <a class="guest__logo-mobile" href="<?=url_pagina('inicio')?>"><?php $logoClaro = false; require __DIR__.'/../partials/logo.php'; ?></a>
      <?php foreach (pull_flashes() as $f): ?><div class="aviso aviso--<?=e($f['type'])?>" role="status"><?=e($f['message'])?></div><?php endforeach; ?>
      <?=$contentHtml?>
    </div>
    <a class="guest__voltar" href="<?=url_pagina('inicio')?>">← Voltar para orcamento.net</a>
  </main>
</div>
<script src="<?=asset('assets/js/app.js')?>" defer></script>
</body>
</html>
