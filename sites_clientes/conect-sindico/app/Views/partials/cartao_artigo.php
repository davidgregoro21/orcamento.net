<?php /** Cartão de artigo do blog. Espera $a (artigo de conteudo('artigos')). */ ?>
<article class="cartao cartao--artigo<?= !empty($a['imagem']) && is_file(PASTA_PUBLICA_SITE.'/'.$a['imagem']) ? ' cartao--com-capa' : '' ?>">
  <?php if (!empty($a['imagem']) && is_file(PASTA_PUBLICA_SITE.'/'.$a['imagem'])): ?><img class="cartao__capa" src="<?=asset($a['imagem'])?>" alt="" loading="lazy"><?php endif; ?>
  <span class="etiqueta etiqueta--<?=e(strtr(mb_strtolower($a['categoria']), ['ç' => 'c', 'ã' => 'a', 'ê' => 'e', 'í' => 'i']))?>"><?=e($a['categoria'])?></span>
  <h3><a href="<?=url_pagina('artigo', ['slug' => $a['slug']])?>"><?=e($a['titulo'])?></a></h3>
  <p><?=e($a['resumo'])?></p>
  <span class="link-seta">Ler artigo <?=icone('seta')?></span>
</article>
