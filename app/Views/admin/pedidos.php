<?php
$status = ['novo' => 'Novo', 'em_contato' => 'Em contato', 'proposta' => 'Proposta enviada', 'fechado' => 'Fechado', 'perdido' => 'Perdido'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    // Converte o interessado em cliente (com os dados do pedido) e abre a ficha.
    if ($conv = (int)post_text('converter')) {
        $st = db()->prepare('SELECT * FROM pedidos_orcamento WHERE id = ?'); $st->execute([$conv]); $pd = $st->fetch();
        if ($pd) {
            db()->prepare('INSERT INTO clientes (nome, contato_nome, email, telefone, observacoes, ativo, criado_em) VALUES (?,?,?,?,?,1,?)')
                ->execute([$pd['empresa'] ?: $pd['nome'], $pd['nome'], $pd['email'], $pd['telefone'], 'Origem: pedido de orçamento #'.$pd['id'].' ('.$pd['servico'].').'.($pd['mensagem'] ? "\n".$pd['mensagem'] : ''), agora()]);
            $novoCliente = (int)db()->lastInsertId();
            db()->prepare("UPDATE pedidos_orcamento SET status = 'fechado' WHERE id = ?")->execute([$conv]);
            audit('interessado_convertido', '#'.$conv);
            flash('sucesso', 'Cliente criado a partir do pedido. Complete os dados e crie o login.');
            redirect('admin_cliente', ['id' => $novoCliente]);
        }
    }
    $id = (int)post_text('id'); $novo = post_text('status');
    if (isset($status[$novo])) { db()->prepare('UPDATE pedidos_orcamento SET status = ? WHERE id = ?')->execute([$novo, $id]); audit('pedido_status', '#'.$id.' '.$novo); }
    redirect('admin_pedidos', isset($_GET['status']) ? ['status' => $_GET['status']] : []);
}
$filtro = (string)($_GET['status'] ?? '');
if (isset($status[$filtro])) { $st = db()->prepare('SELECT * FROM pedidos_orcamento WHERE status = ? ORDER BY criado_em DESC'); $st->execute([$filtro]); }
else { $filtro = ''; $st = db()->query('SELECT * FROM pedidos_orcamento ORDER BY criado_em DESC'); }
$lista = $st->fetchAll();
$contagem = []; foreach (db()->query('SELECT status, COUNT(*) AS n FROM pedidos_orcamento GROUP BY status')->fetchAll() as $c) $contagem[$c['status']] = (int)$c['n'];
?>
<div class="pagina-cab"><div><h1>Interessados</h1><p>Pedidos de orçamento enviados pelo site público. Converta em cliente quando fechar negócio.</p></div></div>
<nav class="filtros">
  <a href="<?=url_pagina('admin_pedidos')?>"<?= $filtro === '' ? ' class="ativo"' : '' ?>>Todos <b><?=array_sum($contagem)?></b></a>
  <?php foreach ($status as $k => $r): ?><a href="<?=url_pagina('admin_pedidos', ['status' => $k])?>"<?= $filtro === $k ? ' class="ativo"' : '' ?>><?=e($r)?> <b><?=$contagem[$k] ?? 0?></b></a><?php endforeach; ?>
</nav>
<div class="pedidos">
  <?php foreach ($lista as $p): ?>
    <article class="cartao pedido">
      <div class="pedido__cab">
        <div><h3><?=e($p['nome'])?><?= $p['empresa'] ? ' <small>· '.e($p['empresa']).'</small>' : '' ?></h3>
          <span class="mudo"><?=e(date('d/m/Y H:i', strtotime($p['criado_em'])))?> · <?=e($p['servico'])?><?= $p['prazo'] ? ' · '.e($p['prazo']) : '' ?></span></div>
        <form method="post" action="<?=url_pagina('admin_pedidos', $filtro ? ['status' => $filtro] : [])?>" class="pedido__status">
          <?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$p['id']?>">
          <select name="status" data-enviar-ao-mudar aria-label="Situação do pedido"><?php foreach ($status as $k => $r): ?><option value="<?=$k?>"<?= $p['status'] === $k ? ' selected' : '' ?>><?=e($r)?></option><?php endforeach; ?></select>
        </form>
      </div>
      <?php if ($p['mensagem']): ?><p class="pedido__msg"><?=nl2br(e($p['mensagem']))?></p><?php endif; ?>
      <div class="pedido__contato">
        <a href="mailto:<?=e($p['email'])?>"><?=icone('email')?> <?=e($p['email'])?></a>
        <a href="https://wa.me/55<?=e(so_digitos($p['telefone']))?>" target="_blank" rel="noopener"><?=icone('whatsapp')?> <?=e($p['telefone'])?></a>
        <form method="post" action="<?=url_pagina('admin_pedidos')?>"><?=csrf_field()?><input type="hidden" name="converter" value="<?=(int)$p['id']?>"><button class="btn btn--linha btn--p"><?=icone('predio')?> Converter em cliente</button></form>
      </div>
    </article>
  <?php endforeach; ?>
  <?php if (!$lista): ?><div class="cartao vazio"><?=icone('caixa')?><p>Nenhum pedido<?= $filtro ? ' nesta situação' : ' ainda' ?>.</p></div><?php endif; ?>
</div>
