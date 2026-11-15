<?php
$todos = conteudo('artigos');
$categorias = array_values(array_unique(array_column($todos, 'categoria'))); sort($categorias);
$cat = (string)($_GET['categoria'] ?? '');
$lista = $cat !== '' && in_array($cat, $categorias, true) ? array_values(array_filter($todos, fn($a) => $a['categoria'] === $cat)) : $todos;
if ($cat !== '' && in_array($cat, $categorias, true)) $tituloPagina = 'Blog: '.$cat;
?>
<section class="cab-pagina">
  <div class="container">
    <span class="sobretitulo sobretitulo--claro">Blog</span>
    <h1>Informação para síndicos e condôminos</h1>
    <p>Legislação, manutenção, segurança e convivência explicadas de forma direta.</p>
  </div>
</section>

<section class="secao">
  <div class="container">
    <nav class="filtros" aria-label="Categorias">
      <a href="<?=url_pagina('blog')?>"<?= $cat === '' ? ' class="ativo"' : '' ?>>Todos</a>
      <?php foreach ($categorias as $c): ?>
        <a href="<?=url_pagina('blog', ['categoria' => $c])?>"<?= $cat === $c ? ' class="ativo"' : '' ?>><?=e($c)?></a>
      <?php endforeach; ?>
    </nav>
    <div class="cartoes cartoes--3">
      <?php foreach ($lista as $a): require __DIR__.'/../partials/cartao_artigo.php'; endforeach; ?>
    </div>
  </div>
</section>
