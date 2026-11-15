<?php
$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT s.*, c.nome AS cliente, p.nome AS projeto, u.nome AS autor, u.email AS autor_email FROM solicitacoes s JOIN clientes c ON c.id = s.cliente_id LEFT JOIN projetos p ON p.id = s.projeto_id LEFT JOIN usuarios u ON u.id = s.usuario_id WHERE s.id = ?');
$st->execute([$id]); $s = $st->fetch();
if (!$s) { flash('erro', 'Solicitação não encontrada.'); redirect('admin_solicitacoes'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $msg = mb_substr(post_text('mensagem'), 0, 5000);
    $novo = post_text('status');
    if ($msg !== '') db()->prepare('INSERT INTO solicitacao_mensagens (solicitacao_id, usuario_id, mensagem, criado_em) VALUES (?,?,?,?)')->execute([$id, current_user()['id'], $msg, agora()]);
    if (isset(status_solicitacao()[$novo]) && $novo !== $s['status']) db()->prepare('UPDATE solicitacoes SET status = ? WHERE id = ?')->execute([$novo, $id]);
    db()->prepare('UPDATE solicitacoes SET atualizado_em = ? WHERE id = ?')->execute([agora(), $id]);
    audit('solicitacao_respondida', '#'.$id.($novo !== $s['status'] ? ' → '.$novo : ''));
    // Avisa o cliente por e-mail (se houver servidor configurado).
    if ($msg !== '' && $s['autor_email']) enviar_email($s['autor_email'], 'Resposta à sua solicitação: '.$s['titulo'], email_modelo('Sua solicitação foi respondida', '<p>'.nl2br(e($msg)).'</p><p><a href="'.e(url_absoluta(url_pagina('solicitacao', ['id' => $id]))).'">Ver na sua área exclusiva</a></p>'));
    flash('sucesso', 'Resposta registrada.');
    redirect('admin_solicitacao', ['id' => $id]);
}
$tituloPagina = $s['titulo'];
$st = db()->prepare('SELECT m.*, u.nome, u.perfil FROM solicitacao_mensagens m LEFT JOIN usuarios u ON u.id = m.usuario_id WHERE m.solicitacao_id = ? ORDER BY m.criado_em, m.id'); $st->execute([$id]);
$mensagens = $st->fetchAll();
?>
<div class="pagina-cab">
  <div><h1><?=e($s['titulo'])?></h1><p><a href="<?=url_pagina('admin_cliente', ['id' => $s['cliente_id'], 'aba' => 'solicitacoes'])?>"><?=e($s['cliente'])?></a> · <?=e($s['projeto'] ?? 'Geral')?> · <?=e(tipos_solicitacao()[$s['tipo']] ?? $s['tipo'])?> · <?=selo($s['status'], status_solicitacao())?></p></div>
</div>
<div class="conversa">
  <div class="msg msg--cliente"><div class="msg__cab"><b><?=e($s['autor'] ?? 'Cliente')?></b><small><?=data_hora($s['criado_em'])?></small></div><p><?=nl2br(e($s['descricao']))?></p></div>
  <?php foreach ($mensagens as $m): ?>
    <div class="msg <?= $m['perfil'] === 'admin' ? 'msg--equipe' : 'msg--cliente' ?>"><div class="msg__cab"><b><?=e($m['nome'] ?? '—')?></b><small><?=data_hora($m['criado_em'])?></small></div><p><?=nl2br(e($m['mensagem']))?></p></div>
  <?php endforeach; ?>
</div>
<form class="cartao form" method="post" action="<?=url_pagina('admin_solicitacao', ['id' => $id])?>">
  <?=csrf_field()?>
  <label class="campo"><span>Responder ao cliente</span><textarea name="mensagem" rows="4" placeholder="Escreva a resposta (o cliente vê na área exclusiva)"></textarea></label>
  <div class="form-linha"><label class="campo"><span>Situação</span><select name="status"><?php foreach (status_solicitacao() as $k => $r): ?><option value="<?=$k?>"<?= $s['status'] === $k ? ' selected' : '' ?>><?=e($r)?></option><?php endforeach; ?></select></label><button class="btn btn--primario">Salvar</button></div>
</form>
