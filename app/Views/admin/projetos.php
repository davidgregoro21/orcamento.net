<?php
$filtroCliente = (int)($_GET['cliente'] ?? 0);
$sql = 'SELECT p.*, c.nome AS cliente_nome FROM projetos p LEFT JOIN clientes c ON c.id = p.cliente_id';
$st = $filtroCliente ? db()->prepare($sql.' WHERE p.cliente_id = ? ORDER BY p.ordem, p.nome') : db()->prepare($sql.' ORDER BY c.nome, p.ordem, p.nome');
$st->execute($filtroCliente ? [$filtroCliente] : []);
$lista = $st->fetchAll();
$clientes = db()->query('SELECT id, nome FROM clientes ORDER BY nome')->fetchAll();
?>
<div class="pagina-cab">
  <div><h1>Projetos</h1><p>Sites e sistemas de cada cliente. O link de demonstração abre só para o cliente dono do projeto.</p></div>
  <a class="btn btn--primario" href="<?=url_pagina('admin_projeto', ['novo' => 1] + ($filtroCliente ? ['cliente' => $filtroCliente] : []))?>"><?=icone('mais')?> Novo projeto</a>
</div>
<form class="busca" method="get" action="<?=url_pagina('admin_projetos')?>">
  <select name="cliente" data-enviar-ao-mudar><option value="0">Todos os clientes</option><?php foreach ($clientes as $cl): ?><option value="<?=$cl['id']?>"<?= $filtroCliente === (int)$cl['id'] ? ' selected' : '' ?>><?=e($cl['nome'])?></option><?php endforeach; ?></select>
</form>
<div class="grade-3">
  <?php foreach ($lista as $p) require __DIR__.'/_projeto_card.php'; ?>
</div>
<?php if (!$lista): ?><div class="cartao vazio"><?=icone('tela')?><p>Nenhum projeto cadastrado.</p></div><?php endif; ?>
