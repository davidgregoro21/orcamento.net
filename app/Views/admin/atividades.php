<?php
$perfil = in_array($_GET['perfil'] ?? '', ['cliente', 'admin'], true) ? $_GET['perfil'] : '';
$clienteF = (int)($_GET['cliente'] ?? 0);
$pagina = max(1, (int)($_GET['p'] ?? 1)); $porPagina = 100;
$where = []; $args = [];
if ($perfil) { $where[] = 'u.perfil = ?'; $args[] = $perfil; }
if ($clienteF) { $where[] = 'u.cliente_id = ?'; $args[] = $clienteF; }
$sqlWhere = $where ? ' WHERE '.implode(' AND ', $where) : '';
$st = db()->prepare('SELECT a.*, u.nome, u.perfil, c.nome AS cliente FROM auditoria a LEFT JOIN usuarios u ON u.id = a.usuario_id LEFT JOIN clientes c ON c.id = u.cliente_id'.$sqlWhere.' ORDER BY a.id DESC LIMIT '.($porPagina + 1).' OFFSET '.(($pagina - 1) * $porPagina));
$st->execute($args);
$lista = $st->fetchAll();
$temMais = count($lista) > $porPagina; $lista = array_slice($lista, 0, $porPagina);
$clientes = db()->query('SELECT id, nome FROM clientes ORDER BY nome')->fetchAll();
$q = fn(array $extra) => array_filter(['perfil' => $perfil, 'cliente' => $clienteF ?: null] + $extra);
?>
<div class="pagina-cab"><div><h1>Atividades</h1><p>Histórico de acessos e ações: logins, visualizações de site e orçamento, alterações no painel.</p></div></div>
<form class="busca" method="get" action="<?=url_pagina('admin_atividades')?>">
  <select name="perfil" data-enviar-ao-mudar><option value="">Todos os perfis</option><option value="cliente"<?= $perfil === 'cliente' ? ' selected' : '' ?>>Somente clientes</option><option value="admin"<?= $perfil === 'admin' ? ' selected' : '' ?>>Somente administradores</option></select>
  <select name="cliente" data-enviar-ao-mudar><option value="0">Todos os clientes</option><?php foreach ($clientes as $cl): ?><option value="<?=$cl['id']?>"<?= $clienteF === (int)$cl['id'] ? ' selected' : '' ?>><?=e($cl['nome'])?></option><?php endforeach; ?></select>
</form>
<div class="cartao tabela-wrap"><table class="tabela tabela--compacta">
  <thead><tr><th>Quando</th><th>Quem</th><th>Ação</th><th>Detalhe</th><th>IP</th></tr></thead>
  <tbody>
  <?php foreach ($lista as $a): ?>
    <tr><td><?=data_hora($a['criado_em'])?></td><td><?=e($a['nome'] ?? '—')?><?= $a['cliente'] ? '<br><small class="mudo">'.e($a['cliente']).'</small>' : ($a['perfil'] === 'admin' ? '<br><small class="mudo">admin</small>' : '') ?></td><td><?=e(str_replace('_', ' ', $a['acao']))?></td><td><?=e($a['detalhe'] ?? '')?></td><td><small><?=e($a['ip'] ?? '')?></small></td></tr>
  <?php endforeach; ?>
  <?php if (!$lista): ?><tr><td colspan="5" class="mudo">Nada registrado.</td></tr><?php endif; ?>
  </tbody>
</table></div>
<div class="paginacao">
  <?php if ($pagina > 1): ?><a class="btn btn--linha btn--p" href="<?=url_pagina('admin_atividades', $q(['p' => $pagina - 1]))?>">← Mais recentes</a><?php endif; ?>
  <?php if ($temMais): ?><a class="btn btn--linha btn--p" href="<?=url_pagina('admin_atividades', $q(['p' => $pagina + 1]))?>">Mais antigas →</a><?php endif; ?>
</div>
