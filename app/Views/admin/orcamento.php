<?php
$id = (int)($_GET['id'] ?? 0);
$buscar = function (int $id): ?array { $st = db()->prepare('SELECT o.*, c.nome AS cliente_nome FROM orcamentos o JOIN clientes c ON c.id = o.cliente_id WHERE o.id = ?'); $st->execute([$id]); return $st->fetch() ?: null; };
$o = $id ? $buscar($id) : null;
if ($id && !$o) { flash('erro', 'Orçamento não encontrado.'); redirect('admin_orcamentos'); }
$clientes = db()->query('SELECT id, nome FROM clientes ORDER BY nome')->fetchAll();
$projetos = db()->query('SELECT id, nome, cliente_id FROM projetos ORDER BY nome')->fetchAll();
/** "1.234,56" → 1234.56 ; vazio → null */
$valor = function (string $s): ?float { $s = trim(str_replace(['R$', ' '], '', $s)); if ($s === '') return null; if (str_contains($s, ',')) $s = str_replace(['.', ','], ['', '.'], $s); return is_numeric($s) ? round((float)$s, 2) : null; };

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $acao = post_text('acao');
    if ($acao === 'salvar' || $acao === 'enviar') {
        $cid = (int)post_text('cliente_id');
        $pid = (int)post_text('projeto_id') ?: null;
        if ($pid) { $st = db()->prepare('SELECT cliente_id FROM projetos WHERE id = ?'); $st->execute([$pid]); if ((int)$st->fetchColumn() !== $cid) $pid = null; }
        $d = [
            'cliente_id' => $cid, 'projeto_id' => $pid, 'titulo' => mb_substr(post_text('titulo'), 0, 160),
            'descricao' => mb_substr(post_text('descricao'), 0, 5000) ?: null, 'condicoes' => mb_substr(post_text('condicoes'), 0, 5000) ?: null,
            'prazo' => mb_substr(post_text('prazo'), 0, 120) ?: null, 'validade' => preg_match('/^\d{4}-\d{2}-\d{2}$/', post_text('validade')) ? post_text('validade') : null,
            'status' => array_key_exists(post_text('status'), status_orcamento()) ? post_text('status') : 'elaboracao',
            'visivel_cliente' => post_text('visivel_cliente') === '1' ? 1 : 0, 'observacoes' => mb_substr(post_text('observacoes'), 0, 5000) ?: null,
            'emitido_em' => preg_match('/^\d{4}-\d{2}-\d{2}$/', post_text('emitido_em')) ? post_text('emitido_em') : null,
        ];
        if (!$cid || mb_strlen($d['titulo']) < 3) { flash('erro', 'Escolha o cliente e informe o título.'); redirect('admin_orcamento', $id ? ['id' => $id] : ['novo' => 1]); }
        // Itens enviados pelo formulário (linhas vazias são ignoradas).
        $itens = [];
        foreach ((array)($_POST['item_descricao'] ?? []) as $i => $desc) {
            $desc = mb_substr(trim((string)$desc), 0, 200); if ($desc === '') continue;
            $qtd = $valor((string)($_POST['item_qtd'][$i] ?? '1')) ?: 1;
            $itens[] = [$desc, mb_substr(trim((string)($_POST['item_detalhes'][$i] ?? '')), 0, 3000) ?: null, $qtd, $valor((string)($_POST['item_valor'][$i] ?? ''))];
        }
        if ($acao === 'enviar') {
            if (!$itens) { flash('erro', 'Inclua pelo menos um item antes de enviar.'); redirect('admin_orcamento', $id ? ['id' => $id] : ['novo' => 1]); }
            $d['status'] = 'enviado'; $d['visivel_cliente'] = 1; $d['emitido_em'] = $d['emitido_em'] ?: date('Y-m-d');
        }
        $campos = array_keys($d);
        if ($id) {
            db()->prepare('UPDATE orcamentos SET '.implode(', ', array_map(fn($k) => "$k = ?", $campos)).', atualizado_em = ? WHERE id = ?')->execute([...array_values($d), agora(), $id]);
        } else {
            db()->prepare('INSERT INTO orcamentos (numero, '.implode(', ', $campos).', criado_em, atualizado_em) VALUES (?, '.implode(', ', array_fill(0, count($campos), '?')).', ?, ?)')->execute([proximo_numero_orcamento(), ...array_values($d), agora(), agora()]);
            $id = (int)db()->lastInsertId();
        }
        db()->prepare('DELETE FROM orcamento_itens WHERE orcamento_id = ?')->execute([$id]);
        $ins = db()->prepare('INSERT INTO orcamento_itens (orcamento_id, descricao, detalhes, quantidade, valor_unitario, ordem) VALUES (?,?,?,?,?,?)');
        foreach ($itens as $n => [$desc, $det, $qtd, $vu]) $ins->execute([$id, $desc, $det, $qtd, $vu, $n]);
        audit($acao === 'enviar' ? 'orcamento_enviado' : 'orcamento_salvo', '#'.$id.' '.$d['titulo']);
        $semValor = count(array_filter($itens, fn($i) => $i[3] === null));
        flash('sucesso', $acao === 'enviar' ? 'Orçamento enviado: já aparece na área do cliente.'.($semValor ? ' Atenção: '.$semValor.' item(ns) sem valor aparecem como "a definir".' : '') : 'Orçamento salvo.');
        redirect('admin_orcamento', ['id' => $id]);
    }
    if ($o && $acao === 'excluir') {
        db()->prepare('DELETE FROM orcamentos WHERE id = ?')->execute([$id]);
        audit('orcamento_excluido', $o['numero']);
        flash('sucesso', 'Orçamento excluído.');
        redirect('admin_orcamentos');
    }
    redirect('admin_orcamento', ['id' => $id]);
}

$itens = [];
if ($o) { $tituloPagina = 'Orçamento '.$o['numero']; $st = db()->prepare('SELECT * FROM orcamento_itens WHERE orcamento_id = ? ORDER BY ordem, id'); $st->execute([$id]); $itens = $st->fetchAll(); }
$linhasItens = array_merge($itens, array_fill(0, max(2, 4 - count($itens)), ['descricao' => '', 'detalhes' => '', 'quantidade' => 1, 'valor_unitario' => null]));
$total = $itens ? total_orcamento($itens) : null;
$preCliente = (int)($_GET['cliente'] ?? ($o['cliente_id'] ?? 0));
$v = fn(string $k) => e((string)($o[$k] ?? ''));
$fmt = fn($n) => $n === null || $n === '' ? '' : number_format((float)$n, 2, ',', '.');
?>
<div class="pagina-cab">
  <div><h1><?= $o ? 'Orçamento '.e($o['numero']) : 'Novo orçamento' ?></h1><p><?= $o ? e($o['cliente_nome']).' · '.selo($o['status'], status_orcamento()).((int)$o['visivel_cliente'] ? ' · visível ao cliente' : ' · oculto do cliente') : 'Monte os itens, defina valores e condições. Valores em branco aparecem como "a definir".' ?></p></div>
  <?php if ($o): ?><a class="btn btn--linha" href="<?=url_pagina('orcamento', ['id' => $id])?>"><?=icone('olho')?> Ver como o cliente</a><?php endif; ?>
</div>
<?php if ($o && $o['resposta_cliente']): ?><div class="aviso aviso--info"><b>Resposta do cliente (<?=data_hora($o['respondido_em'])?>):</b> <?=nl2br(e($o['resposta_cliente']))?></div><?php endif; ?>

<form class="form" method="post" action="<?=url_pagina('admin_orcamento', $o ? ['id' => $id] : [])?>">
  <?=csrf_field()?>
  <section class="cartao">
    <div class="form__grade">
      <label class="campo"><span>Cliente *</span><select name="cliente_id" required data-filtra-projetos><option value="">Escolha...</option><?php foreach ($clientes as $cl): ?><option value="<?=$cl['id']?>"<?= $preCliente === (int)$cl['id'] ? ' selected' : '' ?>><?=e($cl['nome'])?></option><?php endforeach; ?></select></label>
      <label class="campo"><span>Projeto relacionado</span><select name="projeto_id"><option value="">Nenhum</option><?php foreach ($projetos as $pj): ?><option value="<?=$pj['id']?>" data-cliente="<?=$pj['cliente_id']?>"<?= (int)($o['projeto_id'] ?? 0) === (int)$pj['id'] ? ' selected' : '' ?>><?=e($pj['nome'])?></option><?php endforeach; ?></select></label>
      <label class="campo campo--inteiro"><span>Título *</span><input name="titulo" required value="<?=$v('titulo')?>"></label>
      <label class="campo campo--inteiro"><span>Descrição dos serviços</span><textarea name="descricao" rows="3"><?=$v('descricao')?></textarea></label>
    </div>
  </section>

  <section class="cartao">
    <div class="cartao__cab"><h2>Itens</h2><button type="button" class="btn btn--linha btn--p" data-adicionar-item><?=icone('mais')?> Adicionar item</button></div>
    <div class="itens" id="itens">
      <?php foreach ($linhasItens as $it): ?>
        <div class="item">
          <div class="item__linha">
            <input name="item_descricao[]" placeholder="Serviço / item" value="<?=e($it['descricao'])?>">
            <input name="item_qtd[]" class="item__qtd" inputmode="decimal" placeholder="Qtd" value="<?=e(rtrim(rtrim(number_format((float)$it['quantidade'], 2, ',', ''), '0'), ','))?>">
            <input name="item_valor[]" class="item__valor" inputmode="decimal" placeholder="Valor (vazio = a definir)" value="<?=$fmt($it['valor_unitario'])?>">
          </div>
          <textarea name="item_detalhes[]" rows="2" placeholder="Funcionalidades incluídas (uma por linha)"><?=e($it['detalhes'] ?? '')?></textarea>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="total-orc">Total: <b><?= $itens ? ($total === null ? 'a definir' : moeda($total)) : '—' ?></b><small class="mudo">(calculado ao salvar)</small></div>
  </section>

  <section class="cartao">
    <div class="form__grade">
      <label class="campo campo--inteiro"><span>Condições comerciais</span><textarea name="condicoes" rows="3" placeholder="Forma de pagamento, parcelas, o que não está incluso..."><?=$v('condicoes')?></textarea></label>
      <label class="campo"><span>Prazo estimado</span><input name="prazo" value="<?=$v('prazo')?>"></label>
      <label class="campo"><span>Validade da proposta</span><input type="date" name="validade" value="<?=$v('validade')?>"></label>
      <label class="campo"><span>Data de emissão</span><input type="date" name="emitido_em" value="<?=$v('emitido_em')?>"></label>
      <label class="campo"><span>Situação</span><select name="status"><?php foreach (status_orcamento() as $k => $r): ?><option value="<?=$k?>"<?= ($o['status'] ?? 'elaboracao') === $k ? ' selected' : '' ?>><?=e($r)?></option><?php endforeach; ?></select></label>
      <label class="campo campo--inteiro"><span>Observações internas</span><textarea name="observacoes" rows="2"><?=$v('observacoes')?></textarea></label>
    </div>
    <label class="opcao"><input type="checkbox" name="visivel_cliente" value="1"<?= (int)($o['visivel_cliente'] ?? 0) ? ' checked' : '' ?>> Visível na área do cliente</label>
  </section>

  <div class="botoes">
    <button class="btn btn--linha" name="acao" value="salvar">Salvar</button>
    <button class="btn btn--primario" name="acao" value="enviar" data-confirmar-botao="Enviar este orçamento ao cliente? Ele passa a aparecer na área exclusiva."><?=icone('seta')?> Salvar e enviar ao cliente</button>
    <a class="btn btn--linha" href="<?=url_pagina('admin_orcamentos')?>">Voltar</a>
  </div>
</form>
<?php if ($o && !in_array($o['status'], ['aprovado', 'concluido'], true)): ?>
  <form method="post" action="<?=url_pagina('admin_orcamento', ['id' => $id])?>" data-confirmar="Excluir este orçamento?"><?=csrf_field()?><input type="hidden" name="acao" value="excluir"><button class="link-perigo"><?=icone('lixeira')?> Excluir orçamento</button></form>
<?php endif; ?>
