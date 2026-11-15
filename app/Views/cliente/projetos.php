<?php
$projetos = projetos_do_usuario();
$demo = modo_demo();
if (!$demo && count($projetos) === 1) redirect('projeto', ['id' => $projetos[0]['id']]);
$linkProjeto = fn(array $p) => url_pagina($demo ? 'visualizar' : 'projeto', ['id' => $p['id']]);
?>
<div class="pagina-cab"><div><h1>Projetos</h1><p><?= $demo ? 'Rascunho – Demo do projeto. Clique para ver como o seu site está ficando.' : 'Sites e sistemas desenvolvidos para você.' ?></p></div></div>
<div class="grade-3">
  <?php foreach ($projetos as $p): $img = url_imagem($p['imagem']); ?>
    <article class="projeto-adm">
      <a class="projeto-adm__img" href="<?=$linkProjeto($p)?>"><?php if ($img): ?><img src="<?=$img?>" alt=""><?php else: ?><span><?=icone('tela')?></span><?php endif; ?></a>
      <div class="projeto-adm__corpo">
        <div class="projeto-adm__selos"><?= $demo ? '<span class="selo selo--aviso">Rascunho – Demo do projeto</span>' : selo($p['situacao'], situacoes_projeto()) ?></div>
        <h3><a href="<?=$linkProjeto($p)?>"><?=e($p['nome'])?></a></h3>
        <small class="mudo"><?=e($p['tipo'] ?? '')?></small>
        <div class="botoes botoes--sem-margem"><a class="btn btn--primario btn--p" href="<?=url_pagina('visualizar', ['id' => $p['id']])?>"><?=icone('olho')?> <?= $demo ? 'Abrir demo' : 'Visualizar' ?></a><?php if (!$demo): ?><a class="btn btn--linha btn--p" href="<?=url_pagina('projeto', ['id' => $p['id']])?>">Detalhes</a><?php endif; ?></div>
      </div>
    </article>
  <?php endforeach; ?>
</div>
<?php if (!$projetos): ?><div class="cartao vazio"><?=icone('tela')?><p>Nenhum projeto liberado ainda.</p></div><?php endif; ?>
