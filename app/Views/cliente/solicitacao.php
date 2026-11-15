<?php
$s = registro_do_cliente('solicitacoes', (int)($_GET['id'] ?? 0));
if (!$s) { http_response_code(404); flash('erro', 'Solicitação não encontrada.'); redirect('solicitacoes'); }
$id = (int)$s['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $msg = mb_substr(post_text('mensagem'), 0, 5000);
    if ($msg !== '') {
        db()->prepare('INSERT INTO solicitacao_mensagens (solicitacao_id, usuario_id, mensagem, criado_em) VALUES (?,?,?,?)')->execute([$id, current_user()['id'], $msg, agora()]);
        // Cliente respondeu numa solicitação concluída: reabre.
        db()->prepare("UPDATE solicitacoes SET atualizado_em = ?, status = CASE WHEN status IN ('concluida','recusada') THEN 'aberta' ELSE status END WHERE id = ?")->execute([agora(), $id]);
        audit('solicitacao_mensagem', '#'.$id);
        enviar_email((string)((cfg('mail') ?? [])['destino'] ?? contato('email')), 'Nova mensagem – '.current_user()['cliente_nome'].': '.$s['titulo'], email_modelo('Cliente respondeu uma solicitação', '<p>'.nl2br(e($msg)).'</p><p><a href="'.e(url_absoluta(url_pagina('admin_solicitacao', ['id' => $id]))).'">Abrir no painel</a></p>'));
        flash('sucesso', 'Mensagem enviada.');
    }
    redirect('solicitacao', ['id' => $id]);
}
$tituloPagina = $s['titulo'];
$st = db()->prepare('SELECT m.*, u.nome, u.perfil FROM solicitacao_mensagens m LEFT JOIN usuarios u ON u.id = m.usuario_id WHERE m.solicitacao_id = ? ORDER BY m.criado_em, m.id'); $st->execute([$id]);
$mensagens = $st->fetchAll();
$st = db()->prepare('SELECT nome FROM usuarios WHERE id = ?'); $st->execute([$s['usuario_id']]); $autor = $st->fetchColumn() ?: 'Você';
?>
<div class="pagina-cab"><div><a class="voltar-link" href="<?=url_pagina('solicitacoes')?>">← Solicitações</a><h1><?=e($s['titulo'])?></h1><p><?=e(tipos_solicitacao()[$s['tipo']] ?? $s['tipo'])?> · <?=selo($s['status'], status_solicitacao())?> · aberta em <?=data_hora($s['criado_em'])?></p></div></div>
<div class="conversa">
  <div class="msg msg--eu"><div class="msg__cab"><b><?=e($autor)?></b><small><?=data_hora($s['criado_em'])?></small></div><p><?=nl2br(e($s['descricao']))?></p></div>
  <?php foreach ($mensagens as $m): ?>
    <div class="msg <?= $m['perfil'] === 'admin' ? 'msg--equipe' : 'msg--eu' ?>"><div class="msg__cab"><b><?= $m['perfil'] === 'admin' ? 'Equipe orcamento.net' : e($m['nome'] ?? '') ?></b><small><?=data_hora($m['criado_em'])?></small></div><p><?=nl2br(e($m['mensagem']))?></p></div>
  <?php endforeach; ?>
  <?php if (!$mensagens): ?><p class="mudo centro">Recebemos sua solicitação. A resposta aparece aqui.</p><?php endif; ?>
</div>
<form class="cartao form" method="post" action="<?=url_pagina('solicitacao', ['id' => $id])?>">
  <?=csrf_field()?>
  <label class="campo"><span>Escrever mensagem</span><textarea name="mensagem" rows="3" required placeholder="Complemente, responda ou agradeça :)"></textarea></label>
  <div class="botoes botoes--sem-margem"><button class="btn btn--primario"><?=icone('seta')?> Enviar</button></div>
</form>
