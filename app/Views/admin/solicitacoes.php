<?php
$filtro = (string)($_GET['status'] ?? 'abertas');
$sql = 'SELECT s.*, c.nome AS cliente, p.nome AS projeto, u.nome AS autor, (SELECT COUNT(*) FROM solicitacao_mensagens m WHERE m.solicitacao_id = s.id) AS qtd_msg
  FROM solicitacoes s JOIN clientes c ON c.id = s.cliente_id LEFT JOIN projetos p ON p.id = s.projeto_id LEFT JOIN usuarios u ON u.id = s.usuario_id';
if ($filtro === 'abertas') $st = db()->query($sql." WHERE s.status IN ('aberta','andamento') ORDER BY s.criado_em DESC");
elseif (isset(status_solicitacao()[$filtro])) { $st = db()->prepare($sql.' WHERE s.status = ? ORDER BY s.criado_em DESC'); $st->execute([$filtro]); }
else { $filtro = 'todas'; $st = db()->query($sql.' ORDER BY s.criado_em DESC'); }
$lista = $st->fetchAll();
?>
<div class="pagina-cab"><div><h1>Solicitações dos clientes</h1><p>Pedidos de alteração, melhorias, correções e comentários enviados pela área exclusiva.</p></div></div>
<nav class="filtros">
  <?php foreach (['abertas' => 'Em aberto', 'todas' => 'Todas'] + status_solicitacao() as $k => $r): ?><a href="<?=url_pagina('admin_solicitacoes', ['status' => $k])?>"<?= $filtro === $k ? ' class="ativo"' : '' ?>><?=e($r)?></a><?php endforeach; ?>
</nav>
<div class="cartao tabela-wrap"><table class="tabela">
  <thead><tr><th>Solicitação</th><th>Cliente</th><th>Tipo</th><th>Situação</th><th>Aberta em</th></tr></thead>
  <tbody>
  <?php foreach ($lista as $s): ?>
    <tr><td><a href="<?=url_pagina('admin_solicitacao', ['id' => $s['id']])?>"><b><?=e($s['titulo'])?></b></a><br><small class="mudo"><?=e($s['projeto'] ?? 'Geral')?> · por <?=e($s['autor'] ?? '—')?> · <?=$s['qtd_msg']?> mensagem(ns)</small></td>
      <td><?=e($s['cliente'])?></td><td><?=e(tipos_solicitacao()[$s['tipo']] ?? $s['tipo'])?></td><td><?=selo($s['status'], status_solicitacao())?></td><td><?=data_hora($s['criado_em'])?></td></tr>
  <?php endforeach; ?>
  <?php if (!$lista): ?><tr><td colspan="5" class="mudo">Nenhuma solicitação.</td></tr><?php endif; ?>
  </tbody>
</table></div>
