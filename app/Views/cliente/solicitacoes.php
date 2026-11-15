<?php
$cid = cliente_atual();
$projetos = projetos_do_usuario();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $tipo = array_key_exists(post_text('tipo'), tipos_solicitacao()) ? post_text('tipo') : 'alteracao';
    $titulo = mb_substr(post_text('titulo'), 0, 160); $desc = mb_substr(post_text('descricao'), 0, 5000);
    $pid = (int)post_text('projeto_id');
    if (!in_array($pid, array_map('intval', array_column($projetos, 'id')), true)) $pid = null;   // só projetos do próprio cliente
    if (mb_strlen($titulo) < 3 || mb_strlen($desc) < 5) { $_SESSION['form_sol'] = $_POST; flash('erro', 'Descreva a solicitação (título e detalhes).'); redirect('solicitacoes', ['nova' => 1]); }
    if (!limite_taxa('solicitacao:'.current_user()['id'], 20, 3600)) { flash('erro', 'Muitas solicitações em pouco tempo. Tente mais tarde.'); redirect('solicitacoes'); }
    db()->prepare('INSERT INTO solicitacoes (cliente_id, projeto_id, usuario_id, tipo, titulo, descricao, status, criado_em, atualizado_em) VALUES (?,?,?,?,?,?,?,?,?)')
        ->execute([$cid, $pid, current_user()['id'], $tipo, $titulo, $desc, 'aberta', agora(), agora()]);
    $nid = (int)db()->lastInsertId();
    audit('solicitacao_criada', '#'.$nid.' '.$titulo);
    enviar_email((string)((cfg('mail') ?? [])['destino'] ?? contato('email')), 'Nova solicitação – '.current_user()['cliente_nome'].': '.$titulo,
        email_modelo('Nova solicitação de cliente', '<p><b>'.e(current_user()['cliente_nome']).'</b> ('.e(current_user()['nome']).') · '.e(tipos_solicitacao()[$tipo]).'</p><p><b>'.e($titulo).'</b></p><p>'.nl2br(e($desc)).'</p><p><a href="'.e(url_absoluta(url_pagina('admin_solicitacao', ['id' => $nid]))).'">Abrir no painel</a></p>'));
    flash('sucesso', 'Solicitação registrada. Você acompanha as respostas por aqui.');
    redirect('solicitacao', ['id' => $nid]);
}
$antigo = $_SESSION['form_sol'] ?? []; unset($_SESSION['form_sol']);
$nova = isset($_GET['nova']) || $antigo;
$st = db()->prepare('SELECT s.*, p.nome AS projeto, (SELECT COUNT(*) FROM solicitacao_mensagens m WHERE m.solicitacao_id = s.id) AS qtd FROM solicitacoes s LEFT JOIN projetos p ON p.id = s.projeto_id WHERE s.cliente_id = ? ORDER BY s.criado_em DESC');
$st->execute([$cid]); $lista = $st->fetchAll();
$preProjeto = (int)($_GET['projeto'] ?? ($projetos[0]['id'] ?? 0));
?>
<div class="pagina-cab">
  <div><h1>Solicitar alterações</h1><p>Peça ajustes no site, envie comentários ou sugira melhorias. Respondemos por aqui.</p></div>
  <?php if (!$nova): ?><a class="btn btn--primario" href="<?=url_pagina('solicitacoes', ['nova' => 1])?>"><?=icone('mais')?> Nova solicitação</a><?php endif; ?>
</div>
<?php if ($nova): ?>
<form class="cartao form" method="post" action="<?=url_pagina('solicitacoes')?>">
  <?=csrf_field()?>
  <h2 class="cartao__titulo">Nova solicitação</h2>
  <div class="form__grade">
    <label class="campo"><span>Tipo</span><select name="tipo"><?php foreach (tipos_solicitacao() as $k => $r): ?><option value="<?=$k?>"<?= ($antigo['tipo'] ?? '') === $k ? ' selected' : '' ?>><?=e($r)?></option><?php endforeach; ?></select></label>
    <label class="campo"><span>Projeto</span><select name="projeto_id"><option value="">Geral</option><?php foreach ($projetos as $p): ?><option value="<?=$p['id']?>"<?= $preProjeto === (int)$p['id'] ? ' selected' : '' ?>><?=e($p['nome'])?></option><?php endforeach; ?></select></label>
    <label class="campo campo--inteiro"><span>Assunto *</span><input name="titulo" required maxlength="160" placeholder="Ex.: Trocar a foto da página Sobre" value="<?=e($antigo['titulo'] ?? '')?>"></label>
    <label class="campo campo--inteiro"><span>Detalhes *</span><textarea name="descricao" rows="6" required placeholder="Diga em qual página, o que está hoje e como gostaria que ficasse."><?=e($antigo['descricao'] ?? '')?></textarea></label>
  </div>
  <div class="botoes"><button class="btn btn--primario"><?=icone('seta')?> Enviar solicitação</button><a class="btn btn--linha" href="<?=url_pagina('solicitacoes')?>">Cancelar</a></div>
</form>
<?php endif; ?>
<div class="cartao tabela-wrap"><table class="tabela">
  <thead><tr><th>Solicitação</th><th>Tipo</th><th>Situação</th><th>Aberta em</th></tr></thead>
  <tbody>
  <?php foreach ($lista as $s): ?>
    <tr><td><a href="<?=url_pagina('solicitacao', ['id' => $s['id']])?>"><b><?=e($s['titulo'])?></b></a><br><small class="mudo"><?=e($s['projeto'] ?? 'Geral')?> · <?=$s['qtd']?> resposta(s)</small></td><td><?=e(tipos_solicitacao()[$s['tipo']] ?? $s['tipo'])?></td><td><?=selo($s['status'], status_solicitacao())?></td><td><?=data_hora($s['criado_em'])?></td></tr>
  <?php endforeach; ?>
  <?php if (!$lista): ?><tr><td colspan="4" class="mudo">Nenhuma solicitação ainda.</td></tr><?php endif; ?>
  </tbody>
</table></div>
