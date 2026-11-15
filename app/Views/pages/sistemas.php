<?php
$todos = db()->query('SELECT * FROM sistemas WHERE ativo = 1 ORDER BY destaque DESC, ordem, nome')->fetchAll();
$categorias = array_values(array_unique(array_column($todos, 'categoria')));
$cat = (string)($_GET['categoria'] ?? '');
$lista = in_array($cat, $categorias, true) ? array_values(array_filter($todos, fn($s) => $s['categoria'] === $cat)) : $todos;
if (!in_array($cat, $categorias, true)) $cat = '';
?>
<section class="cab-pub">
  <div class="container"><span class="sobre sobre--claro">Soluções</span><h1>Soluções prontas para a sua empresa</h1><p>Escolha uma base testada e nós adaptamos com a sua marca, os seus campos e as suas regras. Cada cliente acessa o seu sistema com login próprio.</p></div>
</section>
<section class="pub-secao">
  <div class="container">
    <?php if (count($categorias) > 1): ?>
    <nav class="filtros filtros--centro" aria-label="Categorias">
      <a href="<?=url_pagina('sistemas')?>"<?= $cat === '' ? ' class="ativo"' : '' ?>>Todos <b><?=count($todos)?></b></a>
      <?php foreach ($categorias as $c): ?><a href="<?=url_pagina('sistemas', ['categoria' => $c])?>"<?= $cat === $c ? ' class="ativo"' : '' ?>><?=e($c)?></a><?php endforeach; ?>
    </nav>
    <?php endif; ?>
    <div class="grade-3">
      <?php foreach ($lista as $si) require __DIR__.'/../partials/cartao_sistema.php'; ?>
    </div>
    <?php if (!$lista): ?><div class="cartao vazio"><?=icone('sistema')?><p>Em breve novos sistemas por aqui.</p></div><?php endif; ?>
    <div class="aviso-sob-medida">
      <span class="ico-box ico-box--escuro"><?=icone('engrenagem')?></span>
      <div><h3>Não achou o que procura?</h3><p>Desenvolvemos o sistema do zero, do jeito que a sua empresa trabalha.</p></div>
      <a class="btn btn--primario" href="<?=url_pagina('contato', ['servico' => 'Sistema web sob medida'])?>">Pedir sistema sob medida</a>
    </div>
  </div>
</section>
<section class="pub-secao pub-secao--suave">
  <div class="container">
    <div class="pub-cab"><span class="sobre">Como funciona</span><h2>Do catálogo ao seu sistema no ar</h2></div>
    <ol class="linha-tempo linha-tempo--4">
      <li><span class="linha-tempo__ico"><?=icone('sistema')?></span><b class="linha-tempo__num">Passo 1</b><h3>Escolha</h3><p>Você escolhe o sistema mais próximo do que precisa.</p></li>
      <li><span class="linha-tempo__ico"><?=icone('documento')?></span><b class="linha-tempo__num">Passo 2</b><h3>Proposta</h3><p>Listamos as adaptações, o prazo e o valor por escrito.</p></li>
      <li><span class="linha-tempo__ico"><?=icone('olho')?></span><b class="linha-tempo__num">Passo 3</b><h3>Prévia</h3><p>Você testa o seu sistema no portal com login próprio.</p></li>
      <li><span class="linha-tempo__ico"><?=icone('estrela')?></span><b class="linha-tempo__num">Passo 4</b><h3>No ar</h3><p>Publicamos, treinamos a equipe e damos suporte.</p></li>
    </ol>
  </div>
</section>
<?php require __DIR__.'/../partials/faixa_cta.php'; ?>
