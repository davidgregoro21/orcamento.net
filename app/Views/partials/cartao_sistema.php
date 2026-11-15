<?php /** Cartão de um sistema do catálogo. Espera $si (linha da tabela sistemas). */ ?>
<article class="sistema-card">
  <div class="sistema-card__topo">
    <span class="ico-box"><?=icone($si['icone'] ?: 'sistema')?></span>
    <span class="sistema-card__cat"><?=e($si['categoria'])?></span>
  </div>
  <h3><a href="<?=url_pagina('sistema', ['s' => $si['slug']])?>"><?=e($si['nome'])?></a></h3>
  <p><?=e($si['resumo'])?></p>
  <ul class="mini-lista"><?php foreach (array_slice(array_filter(array_map('trim', explode("\n", (string)$si['recursos']))), 0, 3) as $r): ?><li><?=icone('check')?> <?=e($r)?></li><?php endforeach; ?></ul>
  <div class="sistema-card__rodape">
    <span><?= $si['preco'] ? 'a partir de <b>'.e($si['preco']).'</b>' : '<b>Sob consulta</b>' ?></span>
    <span class="link-seta">Conhecer <?=icone('seta')?></span>
  </div>
</article>
