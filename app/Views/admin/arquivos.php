<?php
$filtroCliente = (int)($_GET['cliente'] ?? 0);
$clientes = db()->query('SELECT id, nome FROM clientes ORDER BY nome')->fetchAll();
$projetos = db()->query('SELECT id, nome, cliente_id FROM projetos ORDER BY nome')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $acao = post_text('acao');
    $voltar = $filtroCliente ? ['cliente' => $filtroCliente] : [];
    if ($acao === 'enviar') {
        $cid = (int)post_text('cliente_id'); $pid = (int)post_text('projeto_id') ?: null;
        $st = db()->prepare('SELECT id FROM clientes WHERE id = ?'); $st->execute([$cid]);
        if (!$st->fetch()) { flash('erro', 'Escolha o cliente.'); redirect('admin_arquivos', $voltar); }
        if ($pid) { $st = db()->prepare('SELECT cliente_id FROM projetos WHERE id = ?'); $st->execute([$pid]); if ((int)$st->fetchColumn() !== $cid) $pid = null; }
        try { [$nome, $gravado, $tam, $mime] = guardar_documento($_FILES['arquivo'] ?? []); }
        catch (RuntimeException $e) { flash('erro', $e->getMessage()); redirect('admin_arquivos', $voltar); }
        db()->prepare('INSERT INTO arquivos (cliente_id, projeto_id, nome, arquivo, tamanho, mime, descricao, visivel_cliente, usuario_id, criado_em) VALUES (?,?,?,?,?,?,?,?,?,?)')
            ->execute([$cid, $pid, $nome, $gravado, $tam, $mime, mb_substr(post_text('descricao'), 0, 255) ?: null, post_text('visivel') === '1' ? 1 : 0, current_user()['id'], agora()]);
        audit('arquivo_enviado', $nome);
        flash('sucesso', 'Arquivo enviado.');
        redirect('admin_arquivos', ['cliente' => $cid]);
    }
    $st = db()->prepare('SELECT * FROM arquivos WHERE id = ?'); $st->execute([(int)post_text('id')]); $a = $st->fetch();
    if ($a && $acao === 'alternar') { db()->prepare('UPDATE arquivos SET visivel_cliente = ? WHERE id = ?')->execute([(int)$a['visivel_cliente'] ? 0 : 1, $a['id']]); flash('sucesso', 'Visibilidade alterada.'); }
    if ($a && $acao === 'excluir') { db()->prepare('DELETE FROM arquivos WHERE id = ?')->execute([$a['id']]); @unlink(caminho_documento($a['arquivo'])); audit('arquivo_excluido', $a['nome']); flash('sucesso', 'Arquivo excluído.'); }
    redirect('admin_arquivos', $voltar);
}
$sql = 'SELECT a.*, c.nome AS cliente, p.nome AS projeto FROM arquivos a JOIN clientes c ON c.id = a.cliente_id LEFT JOIN projetos p ON p.id = a.projeto_id';
$st = $filtroCliente ? db()->prepare($sql.' WHERE a.cliente_id = ? ORDER BY a.criado_em DESC') : db()->prepare($sql.' ORDER BY a.criado_em DESC');
$st->execute($filtroCliente ? [$filtroCliente] : []);
$lista = $st->fetchAll();
?>
<div class="pagina-cab"><div><h1>Arquivos</h1><p>Documentos, imagens e propostas para cada cliente. Ficam fora da área pública e só baixam com login.</p></div></div>
<section class="cartao">
  <h2 class="cartao__titulo">Enviar arquivo</h2>
  <form class="form" method="post" enctype="multipart/form-data" action="<?=url_pagina('admin_arquivos', $filtroCliente ? ['cliente' => $filtroCliente] : [])?>">
    <?=csrf_field()?><input type="hidden" name="acao" value="enviar">
    <div class="form__grade">
      <label class="campo"><span>Cliente *</span><select name="cliente_id" required data-filtra-projetos><option value="">Escolha...</option><?php foreach ($clientes as $cl): ?><option value="<?=$cl['id']?>"<?= $filtroCliente === (int)$cl['id'] ? ' selected' : '' ?>><?=e($cl['nome'])?></option><?php endforeach; ?></select></label>
      <label class="campo"><span>Projeto</span><select name="projeto_id"><option value="">Geral</option><?php foreach ($projetos as $pj): ?><option value="<?=$pj['id']?>" data-cliente="<?=$pj['cliente_id']?>"><?=e($pj['nome'])?></option><?php endforeach; ?></select></label>
      <label class="campo"><span>Arquivo * (até 25 MB)</span><input type="file" name="arquivo" required></label>
      <label class="campo"><span>Descrição</span><input name="descricao" maxlength="255"></label>
    </div>
    <label class="opcao"><input type="checkbox" name="visivel" value="1" checked> Disponível para o cliente</label>
    <div class="botoes"><button class="btn btn--primario"><?=icone('upload')?> Enviar</button></div>
  </form>
</section>
<form class="busca" method="get" action="<?=url_pagina('admin_arquivos')?>"><select name="cliente" data-enviar-ao-mudar><option value="0">Todos os clientes</option><?php foreach ($clientes as $cl): ?><option value="<?=$cl['id']?>"<?= $filtroCliente === (int)$cl['id'] ? ' selected' : '' ?>><?=e($cl['nome'])?></option><?php endforeach; ?></select></form>
<div class="cartao tabela-wrap"><table class="tabela">
  <thead><tr><th>Arquivo</th><th>Cliente</th><th>Tamanho</th><th>Visível</th><th>Enviado em</th><th class="direita">Ações</th></tr></thead>
  <tbody>
  <?php foreach ($lista as $a): ?>
    <tr class="<?= (int)$a['visivel_cliente'] ? '' : 'inativo' ?>"><td><a href="<?=url_pagina('baixar', ['id' => $a['id']])?>"><?=icone(icone_arquivo($a['nome']))?> <?=e($a['nome'])?></a><?= $a['descricao'] ? '<br><small class="mudo">'.e($a['descricao']).'</small>' : '' ?></td>
      <td><?=e($a['cliente'])?><?= $a['projeto'] ? '<br><small class="mudo">'.e($a['projeto']).'</small>' : '' ?></td><td><?=tamanho_legivel((int)$a['tamanho'])?></td><td><?= (int)$a['visivel_cliente'] ? 'Sim' : 'Não' ?></td><td><?=data_hora($a['criado_em'])?></td>
      <td class="direita acoes">
        <form method="post" action="<?=url_pagina('admin_arquivos', $filtroCliente ? ['cliente' => $filtroCliente] : [])?>"><?=csrf_field()?><input type="hidden" name="acao" value="alternar"><input type="hidden" name="id" value="<?=(int)$a['id']?>"><button class="btn btn--linha btn--p"><?= (int)$a['visivel_cliente'] ? 'Ocultar' : 'Mostrar' ?></button></form>
        <form method="post" action="<?=url_pagina('admin_arquivos', $filtroCliente ? ['cliente' => $filtroCliente] : [])?>" data-confirmar="Excluir o arquivo definitivamente?"><?=csrf_field()?><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?=(int)$a['id']?>"><button class="btn btn--linha btn--p">Excluir</button></form>
      </td></tr>
  <?php endforeach; ?>
  <?php if (!$lista): ?><tr><td colspan="6" class="mudo">Nenhum arquivo.</td></tr><?php endif; ?>
  </tbody>
</table></div>
