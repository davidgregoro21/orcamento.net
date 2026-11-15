<?php
/** Portfólio de serviços: página de vendas com as áreas de atuação (conteúdo em app/Core/conteudo.php → 'portfolio'). */
$grupos = conteudo('portfolio');
$trabalhos = projetos_portfolio();
$totalServicos = array_sum(array_map(fn($g) => count($g['itens']), $grupos));
$problemas = conteudo('problemas');
?>
<!-- ============ TOPO ============ -->
<section class="pf-hero">
  <div class="lp-hero__brilho" aria-hidden="true"></div>
  <div class="container">
    <div class="pf-hero__texto">
      <span class="pilula"><span class="ponto-vivo"></span> <?=$totalServicos?> serviços em um só lugar</span>
      <h1>Sites, sistemas e automações que fazem sua empresa <em>vender mais e trabalhar menos.</em></h1>
      <p class="lp-hero__sub">Do primeiro site à empresa funcionando no automático. Você conta o problema, nós entregamos a solução pronta, com proposta por escrito e prévia para aprovar antes de ir ao ar.</p>
      <div class="botoes">
        <a class="btn btn--primario btn--g" href="<?=url_pagina('contato')?>">Quero um orçamento grátis <?=icone('seta')?></a>
        <a class="btn btn--vazado btn--g" href="#<?=e($grupos[0]['id'] ?? 'sites')?>">Ver o que fazemos</a>
      </div>
    </div>
    <div class="pf-hero__areas">
      <?php foreach ($grupos as $g): ?>
        <a class="pf-hero__area" href="#<?=e($g['id'])?>">
          <span class="ico-box"><?=icone($g['icone'])?></span>
          <span><b><?=e($g['titulo'])?></b><small><?=count($g['itens'])?> serviços</small></span>
          <?=icone('seta', 'ico pf-hero__seta')?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ O QUE VOCÊ GANHA ============ -->
<section class="pf-ganhos">
  <div class="container">
    <div><span class="ico-box"><?=icone('grafico')?></span><b>Mais clientes</b><p>Site e anúncios que transformam visita em pedido.</p></div>
    <div><span class="ico-box"><?=icone('relogio')?></span><b>Mais tempo livre</b><p>Tarefas repetitivas feitas no automático.</p></div>
    <div><span class="ico-box"><?=icone('painel')?></span><b>Mais controle</b><p>A operação inteira organizada em uma tela.</p></div>
    <div><span class="ico-box"><?=icone('escudo')?></span><b>Mais segurança</b><p>HTTPS, backup, LGPD e tudo em seu nome.</p></div>
  </div>
</section>

<!-- ============ ÁREAS ============ -->
<?php foreach ($grupos as $i => $g): ?>
<section class="pf-area-sec<?= $i % 2 ? ' pf-area-sec--suave' : '' ?>" id="<?=e($g['id'])?>">
  <div class="container">
    <div class="pf-split<?= $i % 2 ? ' pf-split--inverso' : '' ?>">
      <div class="pf-split__texto">
        <span class="sobre"><?=icone($g['icone'])?> <?=e($g['titulo'])?></span>
        <h2><?=e($g['chamada'] ?? $g['texto'])?></h2>
        <?php if (!empty($g['pitch'])): ?><p class="pf-pitch"><?=e($g['pitch'])?></p><?php endif; ?>
        <?php if (!empty($g['ganhos'])): ?><ul class="mini-lista mini-lista--grande"><?php foreach ($g['ganhos'] as $gn): ?><li><?=icone('check')?> <?=e($gn)?></li><?php endforeach; ?></ul><?php endif; ?>
        <?php if (!empty($g['ideal'])): ?><div class="pf-ideal"><small>Ideal para</small><?php foreach ($g['ideal'] as $id): ?><span><?=e($id)?></span><?php endforeach; ?></div><?php endif; ?>
        <div class="botoes"><a class="btn btn--primario" href="<?=url_pagina('contato', ['interesse' => $g['itens'][0]['titulo']])?>">Quero isso na minha empresa <?=icone('seta')?></a></div>
      </div>
      <div class="pf-split__visual" aria-hidden="true"><?php require __DIR__.'/../partials/pf_visual.php'; ?></div>
    </div>
    <div class="pf-servicos">
      <p class="pf-servicos__titulo">O que entra em <?=e(mb_strtolower($g['titulo']))?></p>
      <div class="pf-grade">
        <?php foreach ($g['itens'] as $it): ?>
          <a class="pf-item" href="<?=url_pagina('contato', ['interesse' => $it['titulo']])?>">
            <span class="ico-box"><?=icone($it['icone'])?></span>
            <span><b><?=e($it['titulo'])?></b><small><?=e($it['texto'])?></small><em>Pedir orçamento <?=icone('seta')?></em></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php endforeach; ?>

<!-- ============ ANTES E DEPOIS ============ -->
<?php if ($problemas): ?>
<section class="pub-secao">
  <div class="container">
    <div class="pub-cab"><span class="sobre">Isso acontece com você?</span><h2>Antes e depois de trabalhar com a gente</h2></div>
    <div class="lp-problemas">
      <?php foreach ($problemas as $pr): ?>
        <div class="lp-problema">
          <div class="lp-problema__dor"><span class="marca-x" aria-hidden="true">×</span><p><?=e($pr['dor'])?></p></div>
          <div class="lp-problema__seta" aria-hidden="true"><?=icone('seta')?></div>
          <div class="lp-problema__solucao"><span class="marca-ok" aria-hidden="true"><?=icone('check')?></span><p><?=e($pr['solucao'])?></p></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============ TRABALHOS ============ -->
<?php if ($trabalhos): ?>
<section class="pub-secao pub-secao--escura" id="trabalhos">
  <div class="container">
    <div class="pub-cab pub-cab--claro"><span class="sobre">Trabalhos realizados</span><h2>Projetos que já entregamos</h2><p>Cada cliente acompanhou o projeto pela área exclusiva antes de ir ao ar.</p></div>
    <div class="pf-trabalhos"><?php foreach ($trabalhos as $t) require __DIR__.'/../partials/cartao_trabalho.php'; ?></div>
  </div>
</section>
<?php endif; ?>

<!-- ============ POR QUE NÓS ============ -->
<section class="pub-secao pub-secao--suave">
  <div class="container">
    <div class="pub-cab"><span class="sobre">Por que a orcamento.net</span><h2>Você sabe o que vai receber antes de pagar</h2></div>
    <div class="grade-3 pf-diferenciais">
      <?php foreach (conteudo('diferenciais') as $d): ?><div class="modulo"><span class="ico-box"><?=icone($d['icone'])?></span><h3><?=e($d['titulo'])?></h3><p><?=e($d['texto'])?></p></div><?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ COMO FUNCIONA ============ -->
<section class="pub-secao">
  <div class="container">
    <div class="pub-cab"><span class="sobre">Como funciona</span><h2>Do pedido ao projeto no ar</h2></div>
    <ol class="linha-tempo">
      <?php foreach (conteudo('etapas') as $n => $et): ?>
        <li><span class="linha-tempo__ico"><?=icone($et['icone'])?></span><b class="linha-tempo__num">Passo <?=$n + 1?></b><h3><?=e($et['titulo'])?></h3><p><?=e($et['texto'])?></p></li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
<?php require __DIR__.'/../partials/faixa_cta.php'; ?>
