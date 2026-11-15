<?php /** Cartão de projeto no painel. Espera $p (projeto, com 'cliente_nome' opcional). */ $img = url_imagem($p['imagem']); ?>
<article class="projeto-adm<?= (int)$p['ativo'] ? '' : ' inativo' ?>">
  <a class="projeto-adm__img" href="<?=url_pagina('admin_projeto', ['id' => $p['id']])?>"><?php if ($img): ?><img src="<?=$img?>" alt="" loading="lazy"><?php else: ?><span><?=icone('tela')?></span><?php endif; ?></a>
  <div class="projeto-adm__corpo">
    <div class="projeto-adm__selos"><?=selo($p['situacao'], situacoes_projeto())?><?= (int)$p['ativo'] ? '<span class="selo selo--ok">Liberado</span>' : '<span class="selo selo--off">Oculto do cliente</span>' ?></div>
    <h3><a href="<?=url_pagina('admin_projeto', ['id' => $p['id']])?>"><?=e($p['nome'])?></a></h3>
    <small class="mudo"><?=e(trim(($p['cliente_nome'] ?? '').' · '.($p['tipo'] ?? ''), ' ·'))?></small>
    <div class="botoes botoes--sem-margem">
      <a class="btn btn--linha btn--p" href="<?=url_pagina('admin_projeto', ['id' => $p['id']])?>"><?=icone('editar')?> Editar</a>
      <a class="btn btn--linha btn--p" href="<?=url_pagina('visualizar', ['id' => $p['id']])?>"><?=icone('olho')?> Ver como o cliente</a>
    </div>
  </div>
</article>
