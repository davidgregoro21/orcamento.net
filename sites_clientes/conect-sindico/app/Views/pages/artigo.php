<?php
$artigo = artigo_por_slug((string)($_GET['slug'] ?? ''));
if (!$artigo) { http_response_code(404); require __DIR__.'/nao_encontrada.php'; $tituloPagina = 'Artigo não encontrado'; return; }
$tituloPagina = $artigo['titulo'];
$descricaoPagina = $artigo['resumo'];
if (!empty($artigo['imagem']) && is_file(PASTA_PUBLICA_SITE.'/'.$artigo['imagem'])) $imagemPagina = asset($artigo['imagem']);
$relacionados = array_slice(array_values(array_filter(conteudo('artigos'), fn($a) => $a['categoria'] === $artigo['categoria'] && $a['slug'] !== $artigo['slug'])), 0, 3);
?>
<article>
  <header class="cab-pagina cab-pagina--artigo">
    <div class="container container--texto">
      <a class="voltar" href="<?=url_pagina('blog')?>">← Blog</a>
      <span class="etiqueta etiqueta--clara"><?=e($artigo['categoria'])?></span>
      <h1><?=e($artigo['titulo'])?></h1>
      <p><?=e($artigo['resumo'])?></p>
    </div>
  </header>
  <div class="secao">
    <div class="container container--texto texto texto--artigo">
      <?php if (!empty($artigo['imagem']) && is_file(PASTA_PUBLICA_SITE.'/'.$artigo['imagem'])): ?><img class="artigo__capa" src="<?=asset($artigo['imagem'])?>" alt="<?=e($artigo['titulo'])?>"><?php endif; ?>
      <?php if (trim($artigo['corpo']) !== ''): ?>
        <?=$artigo['corpo'] /* HTML do artigo, escrito pela própria empresa em conteudo.php */ ?>
      <?php else: ?>
        <p class="lead"><?=e($artigo['resumo'])?></p>
        <?php if (ambiente() !== 'producao'): ?>
          <div class="aviso aviso--info">Versão de apresentação: o texto completo deste artigo será migrado do site atual.</div>
        <?php endif; ?>
      <?php endif; ?>
      <div class="box-autor">
        <div class="gestor__foto gestor__foto--pequena" aria-hidden="true"><?=icone('usuario')?></div>
        <div><strong><?=e(empresa('gestor'))?></strong><span>Síndico profissional · <?=e(empresa('nome_curto'))?></span></div>
        <a class="btn btn--whats btn--pequeno" href="<?=e(link_whatsapp('Olá! Li o artigo "'.$artigo['titulo'].'" no site e gostaria de conversar.'))?>" target="_blank" rel="noopener"><?=icone('whatsapp')?> Tirar dúvida</a>
      </div>
    </div>
  </div>
</article>

<?php if ($relacionados): ?>
<section class="secao secao--clara">
  <div class="container">
    <h2 class="titulo-secao">Leia também</h2>
    <div class="cartoes cartoes--3">
      <?php foreach ($relacionados as $a): require __DIR__.'/../partials/cartao_artigo.php'; endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
