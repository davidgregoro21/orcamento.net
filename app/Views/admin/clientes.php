<?php
$busca = trim((string)($_GET['q'] ?? ''));
$sql = 'SELECT c.*,
  (SELECT COUNT(*) FROM projetos p WHERE p.cliente_id = c.id) AS qtd_projetos,
  (SELECT COUNT(*) FROM usuarios u WHERE u.cliente_id = c.id) AS qtd_usuarios,
  (SELECT COUNT(*) FROM solicitacoes s WHERE s.cliente_id = c.id AND s.status = \'aberta\') AS qtd_abertas,
  (SELECT MAX(u.ultimo_acesso_em) FROM usuarios u WHERE u.cliente_id = c.id) AS ultimo_acesso
  FROM clientes c';
$args = [];
if ($busca !== '') { $sql .= ' WHERE c.nome LIKE ? OR c.localizacao LIKE ? OR c.categoria LIKE ?'; $args = array_fill(0, 3, '%'.$busca.'%'); }
$st = db()->prepare($sql.' ORDER BY c.ativo DESC, c.nome'); $st->execute($args);
$lista = $st->fetchAll();
?>
<div class="pagina-cab">
  <div><h1>Clientes</h1><p>Empresas atendidas. Cada cliente tem seus usuários, projetos, orçamentos e arquivos.</p></div>
  <a class="btn btn--primario" href="<?=url_pagina('admin_cliente', ['novo' => 1])?>"><?=icone('mais')?> Novo cliente</a>
</div>
<form class="busca" method="get" action="<?=url_pagina('admin_clientes')?>"><input type="search" name="q" placeholder="Buscar por nome, cidade ou ramo" value="<?=e($busca)?>"><button class="btn btn--linha">Buscar</button></form>
<div class="cartoes-clientes">
  <?php foreach ($lista as $c): $logo = url_imagem($c['logo']); ?>
    <a class="cliente-card<?= (int)$c['ativo'] ? '' : ' inativo' ?>" href="<?=url_pagina('admin_cliente', ['id' => $c['id']])?>">
      <div class="cliente-card__topo">
        <?php if ($logo): ?><span class="cliente-card__logo"><img src="<?=$logo?>" alt=""></span><?php else: ?><span class="avatar avatar--g"><?=e(mb_substr($c['nome'], 0, 1))?></span><?php endif; ?>
        <div><b><?=e($c['nome'])?></b><small><?=e(trim(($c['localizacao'] ?? '').' · '.($c['tipo_projeto'] ?? ''), ' ·'))?></small></div>
        <?php if (!(int)$c['ativo']): ?><span class="selo selo--off">Desativado</span><?php elseif ($c['qtd_abertas']): ?><span class="selo selo--aberta"><?=$c['qtd_abertas']?> aberta(s)</span><?php endif; ?>
      </div>
      <div class="cliente-card__nums"><span><b><?=$c['qtd_projetos']?></b> projetos</span><span><b><?=$c['qtd_usuarios']?></b> usuários</span><span>Último acesso: <?=$c['ultimo_acesso'] ? data_curta($c['ultimo_acesso']) : 'nunca'?></span></div>
    </a>
  <?php endforeach; ?>
  <?php if (!$lista): ?><div class="cartao vazio"><?=icone('predio')?><p><?= $busca ? 'Nenhum cliente encontrado.' : 'Cadastre o primeiro cliente.' ?></p></div><?php endif; ?>
</div>
