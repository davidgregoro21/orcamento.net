<?php
$st = db()->prepare('SELECT o.*, p.nome AS projeto, (SELECT SUM(i.quantidade * i.valor_unitario) FROM orcamento_itens i WHERE i.orcamento_id = o.id) AS total,
  (SELECT COUNT(*) FROM orcamento_itens i WHERE i.orcamento_id = o.id AND i.valor_unitario IS NULL) AS sem_valor
  FROM orcamentos o LEFT JOIN projetos p ON p.id = o.projeto_id WHERE o.cliente_id = ? AND o.visivel_cliente = 1 ORDER BY o.emitido_em DESC, o.criado_em DESC');
$st->execute([cliente_atual()]);
$lista = $st->fetchAll();
?>
<div class="pagina-cab"><div><h1>Meus orçamentos</h1><p>Propostas comerciais com serviços, valores, condições e prazo.</p></div></div>
<div class="orc-lista">
  <?php foreach ($lista as $o): $pend = in_array($o['status'], ['enviado', 'aguardando'], true); ?>
    <a class="orc-card<?= $pend ? ' orc-card--pendente' : '' ?>" href="<?=url_pagina('orcamento', ['id' => $o['id']])?>">
      <div><small class="mudo">Nº <?=e($o['numero'])?> · emitido em <?=data_curta($o['emitido_em'])?></small><h3><?=e($o['titulo'])?></h3><?php if ($o['projeto']): ?><small><?=e($o['projeto'])?></small><?php endif; ?></div>
      <div class="orc-card__dir"><b><?= (int)$o['sem_valor'] || $o['total'] === null ? 'Valor a definir' : moeda((float)$o['total']) ?></b><?=selo($o['status'], status_orcamento())?><?php if ($pend): ?><span class="orc-card__acao">Ver e responder →</span><?php endif; ?></div>
    </a>
  <?php endforeach; ?>
  <?php if (!$lista): ?><div class="cartao vazio"><?=icone('cifrao')?><p>Nenhuma proposta disponível ainda.</p></div><?php endif; ?>
</div>
