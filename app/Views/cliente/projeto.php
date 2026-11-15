<?php
$p = registro_do_cliente('projetos', (int)($_GET['id'] ?? 0), 'AND ativo = 1');
if (!$p) { http_response_code(404); flash('erro', 'Projeto não encontrado.'); redirect('painel'); }
$id = (int)$p['id'];
// "Tenho interesse" numa possibilidade de expansão: vira uma solicitação de melhoria.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $item = mb_substr(post_text('interesse'), 0, 150);
    if ($item !== '' && in_array($item, linhas($p['futuras']), true)) {
        db()->prepare('INSERT INTO solicitacoes (cliente_id, projeto_id, usuario_id, tipo, titulo, descricao, status, criado_em, atualizado_em) VALUES (?,?,?,?,?,?,?,?,?)')
            ->execute([$p['cliente_id'], $id, current_user()['id'], 'melhoria', 'Interesse: '.$item, 'Tenho interesse em incluir "'.$item.'" no projeto '.$p['nome'].'. Podemos conversar sobre como funcionaria e o valor?', 'aberta', agora(), agora()]);
        audit('solicitacao_criada', 'Interesse: '.$item);
        enviar_email((string)((cfg('mail') ?? [])['destino'] ?? contato('email')), 'Interesse em nova funcionalidade – '.current_user()['cliente_nome'], email_modelo('Cliente interessado em expansão', '<p><b>'.e(current_user()['cliente_nome']).'</b> ('.e(current_user()['nome']).') tem interesse em: <b>'.e($item).'</b></p>'));
        flash('sucesso', 'Recebemos seu interesse em "'.$item.'". Vamos entrar em contato com uma proposta.');
    }
    redirect('projeto', ['id' => $id]);
}
$tituloPagina = $p['nome'];
$st = db()->prepare('SELECT * FROM projeto_atualizacoes WHERE projeto_id = ? AND visivel_cliente = 1 ORDER BY criado_em DESC, id DESC'); $st->execute([$id]); $historico = $st->fetchAll();
$st = db()->prepare("SELECT * FROM orcamentos WHERE projeto_id = ? AND cliente_id = ? AND visivel_cliente = 1 ORDER BY criado_em DESC"); $st->execute([$id, $p['cliente_id']]); $orcs = $st->fetchAll();
$st = db()->prepare("SELECT titulo FROM solicitacoes WHERE projeto_id = ? AND tipo = 'melhoria' AND titulo LIKE 'Interesse: %'"); $st->execute([$id]);
$jaPediu = array_map(fn($t) => substr($t, 11), $st->fetchAll(PDO::FETCH_COLUMN));
$img = url_imagem($p['imagem']);
$func = linhas($p['funcionalidades']); $futuras = linhas($p['futuras']);
?>
<div class="projeto-cab">
  <div>
    <div class="projeto-adm__selos"><?=selo($p['situacao'], situacoes_projeto())?><?php if ($p['tipo']): ?><span class="selo"><?=e($p['tipo'])?></span><?php endif; ?></div>
    <h1><?=e($p['nome'])?></h1>
    <p class="mudo">Projeto criado em <?=data_curta($p['criado_em'])?><?= $p['atualizado_em'] ? ' · atualizado em '.data_curta($p['atualizado_em']) : '' ?></p>
  </div>
  <div class="botoes botoes--sem-margem">
    <a class="btn btn--primario btn--g" href="<?=url_pagina('visualizar', ['id' => $id])?>"><?=icone('olho')?> Visualizar meu site</a>
    <a class="btn btn--linha btn--g" href="<?=url_pagina('solicitacoes', ['nova' => 1, 'projeto' => $id])?>"><?=icone('mensagem')?> Solicitar alteração</a>
  </div>
</div>

<div class="grade-projeto">
  <div>
    <section class="cartao">
      <?php if ($img): ?><a class="previa-grande" href="<?=url_pagina('visualizar', ['id' => $id])?>"><img src="<?=$img?>" alt="Prévia do site"><span><?=icone('olho')?> Visualizar</span></a><?php endif; ?>
      <h2 class="cartao__titulo">Apresentação</h2>
      <p class="pre"><?= $p['descricao'] ? nl2br(e($p['descricao'])) : '<span class="mudo">Sem descrição.</span>' ?></p>
      <?php if ($p['url_publicacao']): ?><p><b>Endereço publicado:</b> <a href="<?=e($p['url_publicacao'])?>" target="_blank" rel="noopener"><?=e($p['url_publicacao'])?></a></p><?php endif; ?>
    </section>
    <?php if ($func): ?>
    <section class="cartao">
      <h2 class="cartao__titulo">Funcionalidades já implementadas</h2>
      <ul class="recursos recursos--uma"><?php foreach ($func as $f): ?><li><span class="marca-ok"><?=icone('check')?></span><?=e($f)?></li><?php endforeach; ?></ul>
    </section>
    <?php endif; ?>
    <?php if ($futuras): ?>
    <section class="cartao cartao--futuro">
      <h2 class="cartao__titulo"><?=icone('foguete')?> Próximos passos possíveis</h2>
      <p class="mudo">Funcionalidades que podem ser adicionadas ao seu projeto. Marque as que interessam e preparamos uma proposta.</p>
      <ul class="futuras">
        <?php foreach ($futuras as $f): $pedido = in_array($f, $jaPediu, true); ?>
          <li><span><?=icone('estrela')?> <?=e($f)?></span>
            <?php if ($pedido): ?><span class="selo selo--ok">Interesse enviado</span><?php else: ?>
            <form method="post" action="<?=url_pagina('projeto', ['id' => $id])?>"><?=csrf_field()?><input type="hidden" name="interesse" value="<?=e($f)?>"><button class="btn btn--linha btn--p">Tenho interesse</button></form><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>
  </div>
  <aside>
    <section class="cartao">
      <h2 class="cartao__titulo">Histórico de atualizações</h2>
      <ul class="linha-tempo-v">
        <?php foreach ($historico as $a): ?><li><b><?= $a['versao'] ? 'v'.e($a['versao']).' · ' : '' ?><?=e($a['titulo'])?></b><small class="mudo"><?=data_curta($a['criado_em'])?></small><?php if ($a['descricao']): ?><p><?=nl2br(e($a['descricao']))?></p><?php endif; ?></li><?php endforeach; ?>
        <?php if (!$historico): ?><li class="mudo">Nenhuma atualização publicada.</li><?php endif; ?>
      </ul>
    </section>
    <section class="cartao">
      <h2 class="cartao__titulo">Orçamentos deste projeto</h2>
      <?php foreach ($orcs as $o): ?><a class="linha-link" href="<?=url_pagina('orcamento', ['id' => $o['id']])?>"><span><b><?=e($o['numero'])?></b> · <?=e($o['titulo'])?></span><?=selo($o['status'], status_orcamento())?></a><?php endforeach; ?>
      <?php if (!$orcs): ?><p class="mudo">Nenhuma proposta disponível ainda.</p><?php endif; ?>
    </section>
  </aside>
</div>
