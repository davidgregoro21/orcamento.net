<section class="cab-pub">
  <div class="container"><span class="sobre sobre--claro">Serviços</span><h1>Sites, sistemas e suporte sob medida</h1><p>Do site institucional ao sistema completo, sempre com proposta por escrito e prévia no portal antes de ir ao ar.</p></div>
</section>
<!-- ============ SERVIÇOS ============ -->
<section class="pub-secao" id="servicos">
  <div class="container">
    <div class="pub-cab"><span class="sobre">Serviços</span><h2>Tudo o que sua empresa precisa em tecnologia</h2><p>Cada projeto recebe uma proposta por escrito, com escopo e valores definidos para o seu caso.</p></div>
    <div class="grade-3">
      <?php foreach (conteudo('servicos') as $s): ?>
        <article class="servico-card">
          <span class="ico-box"><?=icone($s['icone'])?></span>
          <h3><?=e($s['titulo'])?></h3>
          <p><?=e($s['texto'])?></p>
          <ul class="mini-lista"><?php foreach ($s['itens'] as $it): ?><li><?=icone('check')?> <?=e($it)?></li><?php endforeach; ?></ul>
          <div class="servico-card__rodape"><span><?= $s['a_partir'] !== '' ? 'a partir de <b>'.e($s['a_partir']).'</b>' : '<b>Sob consulta</b>' ?></span><?php if ($s['prazo'] !== ''): ?><span><?=icone('relogio')?> <?=e($s['prazo'])?></span><?php endif; ?></div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ PACOTES ============ -->
<section class="pub-secao pub-secao--escura" id="pacotes">
  <div class="container">
    <div class="pub-cab pub-cab--claro"><span class="sobre">Planos</span><h2>Essencial, Profissional e Empresarial</h2><p>Pontos de partida para evoluir de um site simples a um sistema completo. Todas com layout exclusivo, HTTPS, LGPD e garantia de correções.</p></div>
    <div class="pacotes">
      <?php foreach (conteudo('pacotes') as $pc): ?>
        <article class="pacote<?= $pc['destaque'] ? ' pacote--destaque' : '' ?>">
          <?php if ($pc['destaque']): ?><span class="pacote__selo">Mais escolhido</span><?php endif; ?>
          <h3><?=e($pc['nome'])?></h3>
          <p class="pacote__para"><?=e($pc['para'])?></p>
          <div class="pacote__preco<?= $pc['preco'] === '' ? ' pacote__preco--consulta' : '' ?>"><?php if (!empty($pc['prefixo'])): ?><small><?=e($pc['prefixo'])?></small><?php endif; ?><?= $pc['preco'] !== '' ? e($pc['preco']) : 'Sob consulta' ?></div>
          <small class="pacote__detalhe"><?= $pc['detalhe'] !== '' ? e($pc['detalhe']) : 'Valor definido na proposta' ?></small>
          <ul><?php foreach ($pc['itens'] as $it): ?><li><?=icone('check')?> <?=e($it)?></li><?php endforeach; ?></ul>
          <a class="btn <?= $pc['destaque'] ? 'btn--primario' : 'btn--linha' ?> btn--bloco" href="<?=url_pagina('contato')?>" data-servico="<?=e($pc['nome'] === 'Site + sistema' ? 'Sistema web sob medida' : $pc['nome'])?>">Consultar esta opção</a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ FAQ ============ -->
<section class="pub-secao" id="duvidas">
  <div class="container lp-faq">
    <div>
      <span class="sobre">Dúvidas frequentes</span>
      <h2>Perguntas antes de começar</h2>
      <p class="mudo">Não achou sua dúvida? Escreva no formulário abaixo que respondemos.</p>
    </div>
    <div class="faq">
      <?php foreach (conteudo('faq') as $i => $f): ?>
        <details<?= $i === 0 ? ' open' : '' ?>><summary><?=e($f['p'])?><span class="faq__mais" aria-hidden="true"></span></summary><p><?=e($f['r'])?></p></details>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__.'/../partials/faixa_cta.php'; ?>
