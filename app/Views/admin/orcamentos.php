<?php
$filtro = (string)($_GET['status'] ?? '');
$sql = 'SELECT o.*, c.nome AS cliente, (SELECT COUNT(*) FROM orcamento_itens i WHERE i.orcamento_id = o.id) AS qtd_itens,
  (SELECT SUM(i.quantidade * i.valor_unitario) FROM orcamento_itens i WHERE i.orcamento_id = o.id) AS total,
  (SELECT COUNT(*) FROM orcamento_itens i WHERE i.orcamento_id = o.id AND i.valor_unitario IS NULL) AS sem_valor
  FROM orcamentos o JOIN clientes c ON c.id = o.cliente_id';
if (isset(status_orcamento()[$filtro])) { $st = db()->prepare($sql.' WHERE o.status = ? ORDER BY o.criado_em DESC'); $st->execute([$filtro]); }
else { $filtro = ''; $st = db()->query($sql.' ORDER BY o.criado_em DESC'); }
$lista = $st->fetchAll();
$cont = []; foreach (db()->query('SELECT status, COUNT(*) AS n FROM orcamentos GROUP BY status')->fetchAll() as $r) $cont[$r['status']] = (int)$r['n'];
?>
<div class="pagina-cab">
  <div><h1>Orçamentos</h1><p>Propostas comerciais com itens, valores e condições. O cliente só vê os marcados como visíveis.</p></div>
  <a class="btn btn--primario" href="<?=url_pagina('admin_orcamento', ['novo' => 1])?>"><?=icone('mais')?> Novo orçamento</a>
</div>
<nav class="filtros">
  <a href="<?=url_pagina('admin_orcamentos')?>"<?= $filtro === '' ? ' class="ativo"' : '' ?>>Todos <b><?=array_sum($cont)?></b></a>
  <?php foreach (status_orcamento() as $k => $r): ?><a href="<?=url_pagina('admin_orcamentos', ['status' => $k])?>"<?= $filtro === $k ? ' class="ativo"' : '' ?>><?=e($r)?> <b><?=$cont[$k] ?? 0?></b></a><?php endforeach; ?>
</nav>
<div class="cartao tabela-wrap"><table class="tabela">
  <thead><tr><th>Número</th><th>Cliente</th><th>Título</th><th>Total</th><th>Situação</th><th>Visível</th><th>Emissão</th></tr></thead>
  <tbody>
  <?php foreach ($lista as $o): ?>
    <tr>
      <td><a href="<?=url_pagina('admin_orcamento', ['id' => $o['id']])?>"><b><?=e($o['numero'])?></b></a></td>
      <td><?=e($o['cliente'])?></td><td><?=e($o['titulo'])?></td>
      <td><?= (int)$o['sem_valor'] || !(int)$o['qtd_itens'] ? '<span class="mudo">a definir</span>' : moeda((float)$o['total']) ?></td>
      <td><?=selo($o['status'], status_orcamento())?></td>
      <td><?= (int)$o['visivel_cliente'] ? 'Sim' : '<span class="mudo">Não</span>' ?></td>
      <td><?=data_curta($o['emitido_em'])?></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$lista): ?><tr><td colspan="7" class="mudo">Nenhum orçamento.</td></tr><?php endif; ?>
  </tbody>
</table></div>
