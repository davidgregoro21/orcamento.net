<?php /** Cartão de um trabalho no portfólio. Espera $t (linha de projetos_portfolio()). */
$imgT = url_imagem($t['imagem']); $logoT = url_imagem($t['cliente_logo'] ?? '');
$recursosT = array_slice(array_filter(array_map('trim', explode("\n", (string)$t['funcionalidades']))), 0, 4);
$noAr = $t['situacao'] === 'publicado' && $t['url_publicacao'];
?>
<article class="pf-trabalho">
  <div class="pf-trabalho__img"><?php if ($imgT): ?><img src="<?=$imgT?>" alt="<?=e($t['nome'])?>" loading="lazy"><?php else: ?><span class="ico-box"><?=icone('site')?></span><?php endif; ?></div>
  <div class="pf-trabalho__corpo">
    <div class="pf-trabalho__cliente"><?php if ($logoT): ?><img src="<?=$logoT?>" alt=""><?php endif; ?><span><b><?=e($t['cliente_nome'] ?? '')?></b></span></div>
    <h3><?=e($t['nome'])?></h3>
    <?php if ($t['tipo']): ?><span class="pf-trabalho__tipo"><?=e($t['tipo'])?></span><?php endif; ?>
    <p><?=e($t['portfolio_resumo'] ?: mb_strimwidth(strtok((string)$t['descricao'], "\n") ?: '', 0, 220, '…'))?></p>
    <?php if ($recursosT): ?><ul class="mini-lista"><?php foreach ($recursosT as $r): ?><li><?=icone('check')?> <?=e($r)?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($noAr): ?><a class="link-seta" href="<?=e($t['url_publicacao'])?>" target="_blank" rel="noopener">Ver o site no ar <?=icone('externo')?></a><?php endif; ?>
  </div>
</article>
