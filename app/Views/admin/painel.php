<?php
$q = fn(string $sql) => (int)db()->query($sql)->fetchColumn();
$kpis = [
    ['Clientes ativos', $q("SELECT COUNT(*) FROM clientes WHERE ativo = 1"), 'predio', 'admin_clientes'],
    ['Projetos', $q("SELECT COUNT(*) FROM projetos"), 'tela', 'admin_projetos'],
    ['Orçamentos aguardando', $q("SELECT COUNT(*) FROM orcamentos WHERE status IN ('enviado','aguardando')"), 'cifrao', 'admin_orcamentos'],
    ['Solicitações abertas', $q("SELECT COUNT(*) FROM solicitacoes WHERE status IN ('aberta','andamento')"), 'mensagem', 'admin_solicitacoes'],
    ['Novos interessados', $q("SELECT COUNT(*) FROM pedidos_orcamento WHERE status = 'novo'"), 'caixa', 'admin_pedidos'],
];
$solicitacoes = db()->query("SELECT s.*, c.nome AS cliente FROM solicitacoes s JOIN clientes c ON c.id = s.cliente_id WHERE s.status IN ('aberta','andamento') ORDER BY s.criado_em DESC LIMIT 6")->fetchAll();
$acessos = db()->query("SELECT a.acao, a.detalhe, a.criado_em, u.nome, c.nome AS cliente FROM auditoria a JOIN usuarios u ON u.id = a.usuario_id LEFT JOIN clientes c ON c.id = u.cliente_id WHERE u.perfil = 'cliente' ORDER BY a.id DESC LIMIT 8")->fetchAll();
$orcamentos = db()->query("SELECT o.*, c.nome AS cliente FROM orcamentos o JOIN clientes c ON c.id = o.cliente_id ORDER BY COALESCE(o.atualizado_em, o.criado_em) DESC LIMIT 5")->fetchAll();
$rotulosAcao = ['login' => 'entrou no portal', 'logout' => 'saiu', 'abrir_site' => 'abriu o site', 'ver_site' => 'visualizou o site', 'ver_orcamento' => 'viu um orçamento', 'orcamento_aprovado' => 'aprovou um orçamento', 'orcamento_recusado' => 'recusou um orçamento', 'solicitacao_criada' => 'abriu uma solicitação', 'solicitacao_mensagem' => 'comentou numa solicitação', 'baixar_arquivo' => 'baixou um arquivo', 'senha_alterada' => 'trocou a senha'];
?>
<div class="pagina-cab">
  <div><h1>Olá, <?=e(explode(' ', current_user()['nome'])[0])?></h1><p>Resumo da plataforma em <?=date('d/m/Y')?>.</p></div>
  <div class="botoes botoes--sem-margem">
    <a class="btn btn--linha" href="<?=url_pagina('admin_cliente', ['novo' => 1])?>"><?=icone('mais')?> Cliente</a>
    <a class="btn btn--linha" href="<?=url_pagina('admin_projeto', ['novo' => 1])?>"><?=icone('mais')?> Projeto</a>
    <a class="btn btn--primario" href="<?=url_pagina('admin_orcamento', ['novo' => 1])?>"><?=icone('mais')?> Orçamento</a>
  </div>
</div>

<div class="kpis">
  <?php foreach ($kpis as [$rot, $val, $ic, $link]): ?>
    <a class="kpi" href="<?=url_pagina($link)?>"><span class="kpi__ico"><?=icone($ic)?></span><strong><?=$val?></strong><span><?=e($rot)?></span></a>
  <?php endforeach; ?>
</div>

<div class="grade-2">
  <section class="cartao">
    <div class="cartao__cab"><h2>Solicitações em aberto</h2><a href="<?=url_pagina('admin_solicitacoes')?>">Ver todas</a></div>
    <?php if (!$solicitacoes): ?><p class="mudo">Nenhuma solicitação pendente.</p><?php endif; ?>
    <ul class="lista-itens">
      <?php foreach ($solicitacoes as $s): ?>
        <li><a href="<?=url_pagina('admin_solicitacao', ['id' => $s['id']])?>"><b><?=e($s['titulo'])?></b><small><?=e($s['cliente'])?> · <?=e(tipos_solicitacao()[$s['tipo']] ?? $s['tipo'])?> · <?=data_hora($s['criado_em'])?></small></a><?=selo($s['status'], status_solicitacao())?></li>
      <?php endforeach; ?>
    </ul>
  </section>
  <section class="cartao">
    <div class="cartao__cab"><h2>Acessos dos clientes</h2><a href="<?=url_pagina('admin_atividades', ['perfil' => 'cliente'])?>">Histórico</a></div>
    <?php if (!$acessos): ?><p class="mudo">Nenhum cliente acessou ainda.</p><?php endif; ?>
    <ul class="linha-atividade">
      <?php foreach ($acessos as $a): ?>
        <li><span class="ponto"></span><div><b><?=e($a['nome'])?></b><?= $a['cliente'] ? ' <small>('.e($a['cliente']).')</small>' : '' ?> <?=e($rotulosAcao[$a['acao']] ?? str_replace('_', ' ', $a['acao']))?><small class="mudo"><?=data_hora($a['criado_em'])?></small></div></li>
      <?php endforeach; ?>
    </ul>
  </section>
</div>

<section class="cartao">
  <div class="cartao__cab"><h2>Orçamentos recentes</h2><a href="<?=url_pagina('admin_orcamentos')?>">Ver todos</a></div>
  <div class="tabela-wrap">
    <table class="tabela">
      <thead><tr><th>Número</th><th>Cliente</th><th>Título</th><th>Situação</th><th>Visível ao cliente</th></tr></thead>
      <tbody>
        <?php foreach ($orcamentos as $o): ?>
          <tr><td><a href="<?=url_pagina('admin_orcamento', ['id' => $o['id']])?>"><b><?=e($o['numero'])?></b></a></td><td><?=e($o['cliente'])?></td><td><?=e($o['titulo'])?></td><td><?=selo($o['status'], status_orcamento())?></td><td><?= (int)$o['visivel_cliente'] ? 'Sim' : '<span class="mudo">Não</span>' ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$orcamentos): ?><tr><td colspan="5" class="mudo">Nenhum orçamento.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
