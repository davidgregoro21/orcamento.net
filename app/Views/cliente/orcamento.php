<?php
// Cliente: só orçamentos do próprio cliente marcados como visíveis. Admin: qualquer um (pré-visualização).
$o = registro_do_cliente('orcamentos', (int)($_GET['id'] ?? 0), 'AND visivel_cliente = 1');
if (!$o) { http_response_code(404); flash('erro', 'Orçamento não encontrado.'); redirect(pagina_inicial()); }
$id = (int)$o['id'];
$podeResponder = !is_admin() && in_array($o['status'], ['enviado', 'aguardando'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $resp = post_text('resposta');
    if ($podeResponder && in_array($resp, ['aprovar', 'recusar'], true)) {
        $novo = $resp === 'aprovar' ? 'aprovado' : 'recusado';
        $coment = mb_substr(post_text('comentario'), 0, 3000);
        db()->prepare('UPDATE orcamentos SET status = ?, respondido_em = ?, resposta_cliente = ?, atualizado_em = ? WHERE id = ?')
            ->execute([$novo, agora(), trim(($novo === 'aprovado' ? 'Aprovado' : 'Recusado').' por '.current_user()['nome'].($coment !== '' ? ': '.$coment : '.')), agora(), $id]);
        audit($novo === 'aprovado' ? 'orcamento_aprovado' : 'orcamento_recusado', $o['numero']);
        enviar_email((string)((cfg('mail') ?? [])['destino'] ?? contato('email')), 'Orçamento '.$o['numero'].' '.($novo === 'aprovado' ? 'APROVADO' : 'recusado').' – '.current_user()['cliente_nome'],
            email_modelo('Resposta do cliente', '<p><b>'.e(current_user()['cliente_nome']).'</b> ('.e(current_user()['nome']).') '.($novo === 'aprovado' ? 'aprovou' : 'recusou').' o orçamento <b>'.e($o['numero'].' – '.$o['titulo']).'</b>.</p>'.($coment !== '' ? '<p>Comentário: '.nl2br(e($coment)).'</p>' : '')));
        flash('sucesso', $novo === 'aprovado' ? 'Orçamento aprovado! Vamos entrar em contato para os próximos passos.' : 'Resposta registrada. Obrigado pelo retorno.');
    }
    redirect('orcamento', ['id' => $id]);
}
// Primeira visualização pelo cliente: "Enviado" passa a "Aguardando aprovação".
if (!is_admin()) {
    if ($o['status'] === 'enviado') { db()->prepare("UPDATE orcamentos SET status = 'aguardando' WHERE id = ?")->execute([$id]); $o['status'] = 'aguardando'; }
    audit('ver_orcamento', $o['numero']);
}
$tituloPagina = 'Orçamento '.$o['numero'];
$st = db()->prepare('SELECT * FROM orcamento_itens WHERE orcamento_id = ? ORDER BY ordem, id'); $st->execute([$id]); $itens = $st->fetchAll();
$total = total_orcamento($itens);
$st = db()->prepare('SELECT nome FROM clientes WHERE id = ?'); $st->execute([$o['cliente_id']]); $clienteNome = (string)$st->fetchColumn();
$proj = null; if ($o['projeto_id']) { $st = db()->prepare('SELECT nome FROM projetos WHERE id = ?'); $st->execute([$o['projeto_id']]); $proj = $st->fetchColumn(); }
?>
<?php if (is_admin()): ?><div class="aviso aviso--info">Pré-visualização: é assim que o cliente vê este orçamento. <a href="<?=url_pagina('admin_orcamento', ['id' => $id])?>">Voltar para a edição</a></div><?php endif; ?>
<article class="proposta">
  <header class="proposta__cab">
    <div><?php $logoClaro = false; require __DIR__.'/../partials/logo.php'; ?></div>
    <div class="proposta__num"><small>Proposta comercial</small><b>Nº <?=e($o['numero'])?></b><?=selo($o['status'], status_orcamento())?></div>
  </header>
  <div class="proposta__meta">
    <div><small>Cliente</small><b><?=e($clienteNome)?></b></div>
    <?php if ($proj): ?><div><small>Projeto</small><b><?=e($proj)?></b></div><?php endif; ?>
    <div><small>Emissão</small><b><?=data_curta($o['emitido_em'])?></b></div>
    <?php if ($o['validade']): ?><div><small>Válida até</small><b><?=data_curta($o['validade'])?></b></div><?php endif; ?>
    <?php if ($o['prazo']): ?><div><small>Prazo estimado</small><b><?=e($o['prazo'])?></b></div><?php endif; ?>
  </div>
  <h1 class="proposta__titulo"><?=e($o['titulo'])?></h1>
  <?php if ($o['descricao']): ?><p class="pre"><?=nl2br(e($o['descricao']))?></p><?php endif; ?>

  <table class="proposta__itens">
    <thead><tr><th>Serviço</th><th class="num">Qtd</th><th class="num">Valor</th><th class="num">Subtotal</th></tr></thead>
    <tbody>
      <?php foreach ($itens as $it): $vu = $it['valor_unitario'] === null ? null : (float)$it['valor_unitario']; ?>
        <tr>
          <td><b><?=e($it['descricao'])?></b><?php if ($det = linhas($it['detalhes'])): ?><ul><?php foreach ($det as $d): ?><li><?=icone('check')?> <?=e($d)?></li><?php endforeach; ?></ul><?php endif; ?></td>
          <td class="num"><?=e(rtrim(rtrim(number_format((float)$it['quantidade'], 2, ',', '.'), '0'), ','))?></td>
          <td class="num"><?= $vu === null ? '<span class="mudo">a definir</span>' : moeda($vu) ?></td>
          <td class="num"><?= $vu === null ? '—' : moeda($vu * (float)$it['quantidade']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot><tr><td colspan="3">Valor total</td><td class="num"><b><?= $total === null ? 'a definir' : moeda($total) ?></b></td></tr></tfoot>
  </table>

  <?php if ($o['condicoes']): ?><section class="proposta__bloco"><h2>Condições comerciais</h2><p class="pre"><?=nl2br(e($o['condicoes']))?></p></section><?php endif; ?>
  <?php if ($o['resposta_cliente']): ?><div class="aviso aviso--<?= $o['status'] === 'aprovado' ? 'sucesso' : 'info' ?>"><?=e($o['resposta_cliente'])?> (<?=data_hora($o['respondido_em'])?>)</div><?php endif; ?>

  <?php if ($podeResponder): ?>
  <section class="proposta__resposta">
    <h2>Sua resposta</h2>
    <form method="post" action="<?=url_pagina('orcamento', ['id' => $id])?>" class="form">
      <?=csrf_field()?>
      <label class="campo"><span>Comentário (opcional)</span><textarea name="comentario" rows="3" placeholder="Alguma observação, dúvida ou ajuste?"></textarea></label>
      <div class="botoes botoes--sem-margem">
        <button class="btn btn--primario btn--g" name="resposta" value="aprovar" data-confirmar-botao="Confirmar a aprovação deste orçamento?"><?=icone('check')?> Aprovar orçamento</button>
        <button class="btn btn--linha btn--g" name="resposta" value="recusar" data-confirmar-botao="Recusar este orçamento?">Recusar</button>
        <a class="btn btn--linha btn--g" href="<?=url_pagina('solicitacoes', ['nova' => 1])?>">Tenho dúvidas</a>
      </div>
    </form>
  </section>
  <?php endif; ?>
  <div class="proposta__rodape"><button type="button" class="btn btn--linha btn--p" data-imprimir><?=icone('download')?> Imprimir / salvar PDF</button></div>
</article>
