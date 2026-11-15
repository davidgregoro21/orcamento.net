<?php
$u = current_user(); $cid = cliente_atual();
$projetos = projetos_do_usuario();
$destaque = $projetos[0] ?? null;
$q = function (string $sql, array $a = []) use ($cid) { $st = db()->prepare($sql); $st->execute([$cid, ...$a]); return $st; };
$orcPendentes = (int)$q("SELECT COUNT(*) FROM orcamentos WHERE cliente_id = ? AND visivel_cliente = 1 AND status IN ('enviado','aguardando')")->fetchColumn();
$orcTotal = (int)$q('SELECT COUNT(*) FROM orcamentos WHERE cliente_id = ? AND visivel_cliente = 1')->fetchColumn();
$solAbertas = (int)$q("SELECT COUNT(*) FROM solicitacoes WHERE cliente_id = ? AND status IN ('aberta','andamento')")->fetchColumn();
$arqTotal = (int)$q('SELECT COUNT(*) FROM arquivos WHERE cliente_id = ? AND visivel_cliente = 1')->fetchColumn();
$atualizacoes = $q('SELECT a.*, p.nome AS projeto FROM projeto_atualizacoes a JOIN projetos p ON p.id = a.projeto_id WHERE p.cliente_id = ? AND p.ativo = 1 AND a.visivel_cliente = 1 ORDER BY a.criado_em DESC LIMIT 5')->fetchAll();
$img = $destaque ? url_imagem($destaque['imagem']) : null;
?>
<section class="boas-vindas-cli">
  <div>
    <span class="pilula pilula--clara"><?=icone('estrela')?> Área exclusiva</span>
    <h1>Bem-vindo à sua área exclusiva!</h1>
    <p>Olá, <b><?=e($u['cliente_nome'] ?: explode(' ', $u['nome'])[0])?></b>. Aqui você acompanha seu projeto, visualiza a demonstração do seu site e consulta suas propostas comerciais.</p>
  </div>
  <?php if ($logo = url_imagem($u['cliente_logo'] ?? '')): ?><span class="boas-vindas-cli__logo"><img src="<?=$logo?>" alt="<?=e($u['cliente_nome'])?>"></span><?php endif; ?>
</section>

<?php if ($destaque): ?>
<section class="destaque-projeto">
  <div class="destaque-projeto__texto">
    <div class="projeto-adm__selos"><?=selo($destaque['situacao'], situacoes_projeto())?><?php if ($destaque['tipo']): ?><span class="selo"><?=e($destaque['tipo'])?></span><?php endif; ?></div>
    <h2><?=e($destaque['chamada'] ?: $destaque['nome'])?></h2>
    <?php if ($destaque['descricao']): ?><p><?=nl2br(e(mb_strimwidth($destaque['descricao'], 0, 320, '…')))?></p><?php endif; ?>
    <div class="botoes">
      <a class="btn btn--primario btn--g" href="<?=url_pagina('visualizar', ['id' => $destaque['id']])?>"><?=icone('olho')?> Visualizar meu site</a>
      <a class="btn btn--linha btn--g" href="<?=url_pagina('projeto', ['id' => $destaque['id']])?>">Detalhes do projeto</a>
    </div>
  </div>
  <a class="destaque-projeto__tela" href="<?=url_pagina('visualizar', ['id' => $destaque['id']])?>" aria-label="Visualizar <?=e($destaque['nome'])?>">
    <div class="navegador">
      <div class="navegador__barra"><i></i><i></i><i></i><span><?=e(parse_url((string)($destaque['url_publicacao'] ?: $destaque['url_previa']), PHP_URL_HOST) ?: (($_SERVER['HTTP_HOST'] ?? 'orcamento.net').endereco_site((string)$destaque['url_previa'])))?></span></div>
      <?php if ($img): ?><img src="<?=$img?>" alt="Prévia do site"><?php else: ?><div class="navegador__vazio"><?=icone('tela')?></div><?php endif; ?>
    </div>
  </a>
</section>
<?php else: ?>
  <div class="cartao vazio"><?=icone('tela')?><p>Seu projeto ainda está sendo preparado. Assim que a primeira versão estiver pronta, ela aparece aqui.</p></div>
<?php endif; ?>

<div class="atalhos-cli">
  <a class="atalho" href="<?=url_pagina('orcamentos')?>"><span class="kpi__ico"><?=icone('cifrao')?></span><div><b>Meus orçamentos</b><small><?= $orcPendentes ? $orcPendentes.' aguardando sua aprovação' : $orcTotal.' proposta(s)' ?></small></div><?php if ($orcPendentes): ?><em class="contador"><?=$orcPendentes?></em><?php endif; ?></a>
  <a class="atalho" href="<?=url_pagina('solicitacoes')?>"><span class="kpi__ico"><?=icone('mensagem')?></span><div><b>Solicitar alterações</b><small><?= $solAbertas ? $solAbertas.' em andamento' : 'Peça ajustes e melhorias' ?></small></div></a>
  <a class="atalho" href="<?=url_pagina('arquivos')?>"><span class="kpi__ico"><?=icone('documento')?></span><div><b>Meus arquivos</b><small><?=$arqTotal?> arquivo(s)</small></div></a>
</div>

<div class="grade-2">
  <section class="cartao">
    <div class="cartao__cab"><h2>Últimas atualizações</h2><?php if ($destaque): ?><a href="<?=url_pagina('projeto', ['id' => $destaque['id']])?>">Histórico completo</a><?php endif; ?></div>
    <ul class="linha-tempo-v">
      <?php foreach ($atualizacoes as $a): ?><li><b><?= $a['versao'] ? 'v'.e($a['versao']).' · ' : '' ?><?=e($a['titulo'])?></b><small class="mudo"><?=e($a['projeto'])?> · <?=data_curta($a['criado_em'])?></small><?php if ($a['descricao']): ?><p><?=nl2br(e($a['descricao']))?></p><?php endif; ?></li><?php endforeach; ?>
      <?php if (!$atualizacoes): ?><li class="mudo">Nenhuma atualização publicada ainda.</li><?php endif; ?>
    </ul>
  </section>
  <section class="cartao">
    <h2 class="cartao__titulo">Precisa de algo?</h2>
    <p class="mudo">Viu algo para ajustar no site ou quer uma funcionalidade nova? Registre aqui que respondemos pela sua área.</p>
    <a class="btn btn--primario" href="<?=url_pagina('solicitacoes', ['nova' => 1])?>"><?=icone('mensagem')?> Nova solicitação</a>
    <?php if (count($projetos) > 1): ?><p class="mudo" style="margin-top:18px">Você tem <?=count($projetos)?> projetos. <a href="<?=url_pagina('projetos')?>">Ver todos</a></p><?php endif; ?>
  </section>
</div>
