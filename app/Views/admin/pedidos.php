<?php
/**
 * Pedidos de orçamento enviados pelo formulário do site público.
 * Situação, anotações internas, resposta rápida (WhatsApp/e-mail), conversão em cliente, exclusão e exportação.
 */
$status = ['novo' => 'Novo', 'em_contato' => 'Em contato', 'proposta' => 'Proposta enviada', 'fechado' => 'Fechado', 'perdido' => 'Perdido'];
$filtro = (string)($_GET['status'] ?? ''); if (!isset($status[$filtro])) $filtro = '';
$busca = trim(mb_substr((string)($_GET['q'] ?? ''), 0, 80));
$voltar = array_filter(['status' => $filtro, 'q' => $busca]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)post_text('id'); $acao = post_text('acao');
    $st = db()->prepare('SELECT * FROM pedidos_orcamento WHERE id = ?'); $st->execute([$id]); $pd = $st->fetch();
    if (!$pd) { flash('erro', 'Pedido não encontrado.'); redirect('admin_pedidos', $voltar); }

    if ($acao === 'converter') {
        db()->prepare('INSERT INTO clientes (nome, contato_nome, email, telefone, observacoes, ativo, criado_em) VALUES (?,?,?,?,?,1,?)')
            ->execute([$pd['empresa'] ?: $pd['nome'], $pd['nome'], $pd['email'], $pd['telefone'], 'Origem: pedido de orçamento #'.$pd['id'].' ('.$pd['servico'].').'.($pd['mensagem'] ? "\n".$pd['mensagem'] : '').($pd['anotacoes'] ? "\n\nAnotações:\n".$pd['anotacoes'] : ''), agora()]);
        $novoCliente = (int)db()->lastInsertId();
        db()->prepare("UPDATE pedidos_orcamento SET status = 'fechado', atualizado_em = ? WHERE id = ?")->execute([agora(), $id]);
        audit('pedido_convertido', '#'.$id);
        flash('sucesso', 'Cliente criado a partir do pedido. Complete os dados e crie o login.');
        redirect('admin_cliente', ['id' => $novoCliente]);
    }
    if ($acao === 'status' && isset($status[post_text('status')])) {
        db()->prepare('UPDATE pedidos_orcamento SET status = ?, atualizado_em = ? WHERE id = ?')->execute([post_text('status'), agora(), $id]);
        audit('pedido_status', '#'.$id.' '.post_text('status'));
    }
    if ($acao === 'anotacoes') {
        db()->prepare('UPDATE pedidos_orcamento SET anotacoes = ?, atualizado_em = ? WHERE id = ?')->execute([mb_substr(post_text('anotacoes'), 0, 5000) ?: null, agora(), $id]);
        flash('sucesso', 'Anotação salva.');
    }
    if ($acao === 'excluir') {
        db()->prepare('DELETE FROM pedidos_orcamento WHERE id = ?')->execute([$id]);
        audit('pedido_excluido', '#'.$id.' '.$pd['email']);
        flash('sucesso', 'Pedido excluído.');
    }
    redirect('admin_pedidos', $voltar);
}

// Consulta com filtro de situação e busca.
$where = []; $par = [];
if ($filtro !== '') { $where[] = 'status = ?'; $par[] = $filtro; }
if ($busca !== '') { $where[] = '(nome LIKE ? OR email LIKE ? OR empresa LIKE ? OR telefone LIKE ? OR servico LIKE ? OR mensagem LIKE ?)'; array_push($par, ...array_fill(0, 6, '%'.$busca.'%')); }
$st = db()->prepare('SELECT * FROM pedidos_orcamento'.($where ? ' WHERE '.implode(' AND ', $where) : '').' ORDER BY criado_em DESC'); $st->execute($par);
$lista = $st->fetchAll();

// Exportar para planilha (CSV, abre no Excel).
if (isset($_GET['exportar'])) {
    audit('pedidos_exportados', count($lista).' pedidos');
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="pedidos-orcamento-'.date('Y-m-d').'.csv"');
    $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF");
    $seguro = fn($v) => preg_match('/^[=+\-@]/', (string)$v) ? "'".$v : (string)$v;   // evita fórmulas no Excel
    fputcsv($out, ['Data', 'Nome', 'Empresa', 'E-mail', 'Telefone', 'Serviço', 'Para quando', 'Mensagem', 'Situação', 'Anotações'], ';');
    foreach ($lista as $p) fputcsv($out, array_map($seguro, [date('d/m/Y H:i', strtotime($p['criado_em'])), $p['nome'], $p['empresa'], $p['email'], $p['telefone'], $p['servico'], $p['prazo'], $p['mensagem'], $status[$p['status']] ?? $p['status'], $p['anotacoes'] ?? '']), ';');
    fclose($out); exit;
}

$contagem = []; foreach (db()->query('SELECT status, COUNT(*) AS n FROM pedidos_orcamento GROUP BY status')->fetchAll() as $c) $contagem[$c['status']] = (int)$c['n'];
$total = array_sum($contagem);
$mesAtual = (int)db()->query("SELECT COUNT(*) FROM pedidos_orcamento WHERE criado_em >= '".date('Y-m-01 00:00:00')."'")->fetchColumn();
$taxa = $total ? round(($contagem['fechado'] ?? 0) * 100 / $total) : 0;
$primeiroNome = fn(string $n) => explode(' ', trim($n))[0];
?>
<div class="pagina-cab">
  <div><h1>Pedidos de orçamento</h1><p>Tudo o que chega pelo formulário do site. Responda, anote a conversa e converta em cliente quando fechar.</p></div>
  <div class="botoes botoes--sem-margem"><a class="btn btn--linha" href="<?=url_pagina('admin_pedidos', $voltar + ['exportar' => 1])?>"><?=icone('download')?> Exportar planilha</a><a class="btn btn--linha" href="<?=url_pagina('contato')?>" target="_blank" rel="noopener"><?=icone('externo')?> Ver o formulário</a></div>
</div>

<div class="ped-resumo">
  <div class="cartao"><small>Novos</small><b><?=$contagem['novo'] ?? 0?></b><span>aguardando resposta</span></div>
  <div class="cartao"><small>Em negociação</small><b><?=($contagem['em_contato'] ?? 0) + ($contagem['proposta'] ?? 0)?></b><span>em contato ou com proposta</span></div>
  <div class="cartao"><small>Este mês</small><b><?=$mesAtual?></b><span>pedidos recebidos</span></div>
  <div class="cartao"><small>Fechados</small><b><?=$contagem['fechado'] ?? 0?></b><span><?=$taxa?>% de todos os pedidos</span></div>
</div>

<form class="ped-busca" method="get" action="<?=url_pagina('admin_pedidos')?>">
  <?php if (!cfg('urls_amigaveis')): ?><input type="hidden" name="page" value="admin_pedidos"><?php endif; ?>
  <?php if ($filtro): ?><input type="hidden" name="status" value="<?=e($filtro)?>"><?php endif; ?>
  <input type="search" name="q" value="<?=e($busca)?>" placeholder="Buscar por nome, e-mail, empresa, telefone ou mensagem" aria-label="Buscar pedidos">
  <button class="btn btn--primario">Buscar</button><?php if ($busca): ?><a class="btn btn--linha" href="<?=url_pagina('admin_pedidos', $filtro ? ['status' => $filtro] : [])?>">Limpar</a><?php endif; ?>
</form>

<nav class="filtros">
  <a href="<?=url_pagina('admin_pedidos', $busca ? ['q' => $busca] : [])?>"<?= $filtro === '' ? ' class="ativo"' : '' ?>>Todos <b><?=$total?></b></a>
  <?php foreach ($status as $k => $r): ?><a href="<?=url_pagina('admin_pedidos', array_filter(['status' => $k, 'q' => $busca]))?>"<?= $filtro === $k ? ' class="ativo"' : '' ?>><?=e($r)?> <b><?=$contagem[$k] ?? 0?></b></a><?php endforeach; ?>
</nav>

<div class="pedidos">
  <?php foreach ($lista as $p):
    $zap = so_digitos($p['telefone']); if ($zap !== '' && !str_starts_with($zap, '55')) $zap = '55'.$zap;
    $saudacao = 'Olá, '.$primeiroNome($p['nome']).'! Aqui é da orcamento.net. Recebemos seu pedido de orçamento ('.$p['servico'].') e gostaria de entender melhor o que você precisa.';
    $assunto = 'Seu pedido de orçamento – orcamento.net'; ?>
    <article class="cartao pedido pedido--<?=e($p['status'])?>" id="pedido-<?=(int)$p['id']?>">
      <div class="pedido__cab">
        <div>
          <h3><?php if ($p['status'] === 'novo'): ?><span class="selo selo--novo">Novo</span> <?php endif; ?><?=e($p['nome'])?><?= $p['empresa'] ? ' <small>· '.e($p['empresa']).'</small>' : '' ?></h3>
          <span class="mudo">#<?=(int)$p['id']?> · <?=e(date('d/m/Y \à\s H:i', strtotime($p['criado_em'])))?></span>
          <div class="pedido__tags"><span class="pedido__tag"><?=icone('caixa')?> <?=e($p['servico'])?></span><?php if ($p['prazo']): ?><span class="pedido__tag"><?=icone('calendario')?> <?=e($p['prazo'])?></span><?php endif; ?></div>
        </div>
        <form method="post" action="<?=url_pagina('admin_pedidos', $voltar)?>" class="pedido__status">
          <?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$p['id']?>"><input type="hidden" name="acao" value="status">
          <select name="status" data-enviar-ao-mudar aria-label="Situação do pedido"><?php foreach ($status as $k => $r): ?><option value="<?=$k?>"<?= $p['status'] === $k ? ' selected' : '' ?>><?=e($r)?></option><?php endforeach; ?></select>
        </form>
      </div>
      <?php if ($p['mensagem']): ?><p class="pedido__msg"><?=nl2br(e($p['mensagem']))?></p><?php endif; ?>
      <div class="pedido__contato">
        <?php if ($zap !== ''): ?><a class="btn btn--whats btn--p" href="https://wa.me/<?=e($zap)?>?text=<?=rawurlencode($saudacao)?>" target="_blank" rel="noopener"><?=icone('whatsapp')?> Responder no WhatsApp</a><?php endif; ?>
        <a class="btn btn--linha btn--p" href="mailto:<?=e($p['email'])?>?subject=<?=rawurlencode($assunto)?>&amp;body=<?=rawurlencode($saudacao)?>"><?=icone('email')?> Responder por e-mail</a>
        <span class="pedido__dados"><a href="tel:+<?=e($zap)?>"><?=icone('telefone')?> <?=e($p['telefone'])?></a> <a href="mailto:<?=e($p['email'])?>"><?=e($p['email'])?></a></span>
      </div>
      <details class="pedido__notas"<?= $p['anotacoes'] ? ' open' : '' ?>>
        <summary><?=icone('editar')?> Anotações internas<?= $p['anotacoes'] ? '' : ' (o cliente não vê)' ?></summary>
        <form method="post" action="<?=url_pagina('admin_pedidos', $voltar)?>#pedido-<?=(int)$p['id']?>"><?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$p['id']?>"><input type="hidden" name="acao" value="anotacoes">
          <textarea name="anotacoes" rows="3" placeholder="Ex.: liguei dia 20, quer o site até o fim do mês, mandar proposta do plano Profissional."><?=e((string)$p['anotacoes'])?></textarea>
          <button class="btn btn--linha btn--p">Salvar anotação</button><?php if ($p['atualizado_em']): ?><small>Última mudança: <?=e(date('d/m/Y H:i', strtotime($p['atualizado_em'])))?></small><?php endif; ?>
        </form>
      </details>
      <div class="pedido__rodape">
        <form method="post" action="<?=url_pagina('admin_pedidos', $voltar)?>"><?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$p['id']?>"><input type="hidden" name="acao" value="converter"><button class="btn btn--primario btn--p"><?=icone('predio')?> Fechou? Converter em cliente</button></form>
        <form method="post" action="<?=url_pagina('admin_pedidos', $voltar)?>" data-confirmar="Excluir o pedido de <?=e($p['nome'])?>? Use para spam ou pedidos duplicados."><?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$p['id']?>"><input type="hidden" name="acao" value="excluir"><button class="link-perigo"><?=icone('lixeira')?> Excluir</button></form>
      </div>
    </article>
  <?php endforeach; ?>
  <?php if (!$lista): ?><div class="cartao vazio"><?=icone('caixa')?><p><?= $busca ? 'Nenhum pedido encontrado para "'.e($busca).'".' : 'Nenhum pedido'.($filtro ? ' nesta situação' : ' ainda').'. Os pedidos feitos no formulário do site aparecem aqui.' ?></p></div><?php endif; ?>
</div>
