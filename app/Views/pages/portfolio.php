<?php
$grupos = conteudo('portfolio');
$trabalhos = projetos_portfolio();
$destaques = db()->query('SELECT * FROM sistemas WHERE ativo = 1 ORDER BY destaque DESC, ordem, nome LIMIT 3')->fetchAll();
?>
<section class="cab-pub">
  <div class="container"><span class="sobre sobre--claro">Portfólio</span><h1>Tudo o que podemos fazer pela sua empresa</h1><p>Sites, sistemas, automações e suporte em um só lugar. Escolha um serviço ou junte vários em um projeto sob medida. Proposta por escrito e prévia antes de ir ao ar.</p>
    <nav class="filtros filtros--centro pf-atalhos" aria-label="Áreas">
      <?php foreach ($grupos as $g): ?><a href="#<?=e($g['id'])?>"><?=icone($g['icone'])?> <?=e($g['titulo'])?></a><?php endforeach; ?>
      <?php if ($trabalhos): ?><a href="#trabalhos"><?=icone('estrela')?> Trabalhos realizados</a><?php endif; ?>
    </nav>
  </div>
</section>

<?php foreach ($grupos as $i => $g): ?>
<section class="pub-secao<?= $i % 2 ? ' pub-secao--suave' : '' ?>" id="<?=e($g['id'])?>">
  <div class="container">
    <div class="pub-cab"><span class="sobre"><?=e($g['titulo'])?></span><h2><?=e($g['texto'])?></h2></div>
    <div class="pf-grade">
      <?php foreach ($g['itens'] as $it): ?>
        <a class="pf-item" href="<?=url_pagina('contato', ['interesse' => $it['titulo']])?>">
          <span class="ico-box"><?=icone($it['icone'])?></span>
          <span><b><?=e($it['titulo'])?></b><small><?=e($it['texto'])?></small></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endforeach; ?>

<?php if ($trabalhos): ?>
<section class="pub-secao pub-secao--escura" id="trabalhos">
  <div class="container">
    <div class="pub-cab pub-cab--claro"><span class="sobre">Trabalhos realizados</span><h2>Projetos que já entregamos</h2><p>Cada cliente acompanhou o projeto pela área exclusiva antes de ir ao ar.</p></div>
    <div class="pf-trabalhos">
      <?php foreach ($trabalhos as $t) require __DIR__.'/../partials/cartao_trabalho.php'; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($destaques): ?>
<section class="pub-secao">
  <div class="container">
    <div class="pub-cab"><span class="sobre">Soluções prontas</span><h2>Ou comece por uma base testada</h2><p>Sistemas que já funcionam e que adaptamos com a sua marca e as suas regras.</p></div>
    <div class="grade-3"><?php foreach ($destaques as $si) require __DIR__.'/../partials/cartao_sistema.php'; ?></div>
    <div class="centro botoes botoes--centro"><a class="btn btn--primario" href="<?=url_pagina('sistemas')?>">Ver todas as soluções <?=icone('seta')?></a></div>
  </div>
</section>
<?php endif; ?>
<?php require __DIR__.'/../partials/faixa_cta.php'; ?>
