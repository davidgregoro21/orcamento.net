<?php
$st = db()->prepare('SELECT * FROM sistemas WHERE slug = ? AND ativo = 1'); $st->execute([(string)($_GET['s'] ?? '')]);
$si = $st->fetch();
if (!$si) { http_response_code(404); require __DIR__.'/nao_encontrada.php'; return; }
$tituloPagina = $si['nome'];
$recursos = array_values(array_filter(array_map('trim', explode("\n", (string)$si['recursos']))));
$temImagem = $si['imagem'] && is_file(__DIR__.'/../../../public/assets/img/'.$si['imagem']);
$st = db()->prepare('SELECT * FROM sistemas WHERE ativo = 1 AND id <> ? ORDER BY (categoria = ?) DESC, destaque DESC, ordem LIMIT 3'); $st->execute([$si['id'], $si['categoria']]);
$outros = $st->fetchAll();
$voltarForm = $si['slug'];
$preForm = ['servico' => 'Um sistema do catálogo', 'mensagem' => 'Tenho interesse no sistema '.$si['nome'].'.'];
?>
<section class="cab-pub cab-pub--sistema">
  <div class="container sistema-hero">
    <div>
      <a class="voltar-pub" href="<?=url_pagina('sistemas')?>">← Todos os sistemas</a>
      <span class="sistema-card__cat sistema-card__cat--clara"><?=e($si['categoria'])?></span>
      <h1><?=e($si['nome'])?></h1>
      <p><?=e($si['resumo'])?></p>
      <div class="sistema-precos">
        <?php if ($si['preco']): ?><div><small>Implantação a partir de</small><b><?=e($si['preco'])?></b></div><?php endif; ?>
        <?php if ($si['mensalidade']): ?><div><small>Mensalidade (servidor e suporte)</small><b><?=e($si['mensalidade'])?></b></div><?php endif; ?>
        <?php if (!$si['preco'] && !$si['mensalidade']): ?><div><small>Investimento</small><b>Sob consulta</b></div><?php endif; ?>
      </div>
      <div class="botoes">
        <a class="btn btn--primario btn--g" href="#orcamento">Quero este sistema <?=icone('seta')?></a>
        <a class="btn btn--vazado btn--g" href="<?=url_pagina('login')?>"><?=icone('portal')?> Já sou cliente</a>
      </div>
    </div>
    <div class="sistema-hero__visual" aria-hidden="true">
      <div class="navegador">
        <div class="navegador__barra"><i></i><i></i><i></i><span><?=e($si['slug'])?>.seu-dominio.com.br</span></div>
        <?php if ($temImagem): ?><img src="<?=asset('assets/img/'.$si['imagem'])?>" alt="">
        <?php else: ?>
          <div class="tela-modelo">
            <div class="tela-modelo__lateral"><span class="ativo"></span><span></span><span></span><span></span><span></span></div>
            <div class="tela-modelo__corpo">
              <div class="tela-modelo__titulo"><?=icone($si['icone'] ?: 'sistema')?> <?=e($si['nome'])?></div>
              <div class="tela-modelo__cards"><b></b><b></b><b></b></div>
              <div class="tela-modelo__linhas"><i></i><i></i><i></i><i></i></div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<section class="pub-secao">
  <div class="container sistema-corpo">
    <div>
      <h2>Sobre o sistema</h2>
      <?php if ($si['descricao']): ?><p class="lead-pub"><?=nl2br(e($si['descricao']))?></p><?php endif; ?>
      <?php if ($si['para_quem']): ?><div class="para-quem"><?=icone('pessoas')?><div><b>Para quem é</b><span><?=e($si['para_quem'])?></span></div></div><?php endif; ?>
      <h3 class="titulo-recursos">O que vem no sistema</h3>
      <ul class="recursos"><?php foreach ($recursos as $r): ?><li><span class="marca-ok"><?=icone('check')?></span><?=e($r)?></li><?php endforeach; ?></ul>
      <h3 class="titulo-recursos">Incluso em todos os sistemas</h3>
      <ul class="inclusos">
        <li><?=icone('pessoas')?> Administração de usuários</li>
        <li><?=icone('usuario')?> Cadastro de usuários e senha provisória</li>
        <li><?=icone('portal')?> Login individual e perfis de acesso</li>
        <li><?=icone('escudo')?> HTTPS, LGPD e auditoria</li>
        <li><?=icone('site')?> Funciona no computador e no celular</li>
        <li><?=icone('nuvem')?> Backup diário</li>
        <li><?=icone('estrela')?> Sua marca e suas cores</li>
        <li><?=icone('olho')?> Prévia no portal antes de ir ao ar</li>
      </ul>
    </div>
    <aside class="sistema-lateral" id="orcamento">
      <h2 class="cartao__titulo">Peça o orçamento deste sistema</h2>
      <p class="mudo">Diga o que precisa adaptar. O orçamento é grátis.</p>
      <?php require __DIR__.'/../partials/form_orcamento.php'; ?>
    </aside>
  </div>
</section>

<?php if ($outros): ?>
<section class="pub-secao pub-secao--suave">
  <div class="container">
    <div class="pub-cab"><h2>Outros sistemas</h2></div>
    <div class="grade-3"><?php foreach ($outros as $si) require __DIR__.'/../partials/cartao_sistema.php'; ?></div>
  </div>
</section>
<?php endif; ?>
