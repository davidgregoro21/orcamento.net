<?php
$u = current_user();
$admin = is_admin();
// Contadores do menu (pendências).
$pend = ['sol' => 0, 'ped' => 0];
try {
    if ($admin) {
        $pend['sol'] = (int)db()->query("SELECT COUNT(*) FROM solicitacoes WHERE status = 'aberta'")->fetchColumn();
        $pend['ped'] = (int)db()->query("SELECT COUNT(*) FROM pedidos_orcamento WHERE status = 'novo'")->fetchColumn();
    } else {
        $st = db()->prepare("SELECT COUNT(*) FROM orcamentos WHERE cliente_id = ? AND visivel_cliente = 1 AND status IN ('enviado','aguardando')"); $st->execute([cliente_atual()]); $pend['orc'] = (int)$st->fetchColumn();
    }
} catch (Throwable $e) {}
$menu = $admin ? [
    ['Geral', [['admin', 'Painel', 'painel'], ['admin_atividades', 'Atividades', 'atividade']]],
    ['Clientes', [['admin_clientes', 'Clientes', 'predio'], ['admin_projetos', 'Projetos', 'tela'], ['admin_orcamentos', 'Orçamentos', 'cifrao'], ['admin_solicitacoes', 'Solicitações', 'mensagem', $pend['sol']], ['admin_arquivos', 'Arquivos', 'documento'], ['admin_usuarios', 'Usuários', 'pessoas']]],
    ['Comercial', [['admin_pedidos', 'Interessados', 'caixa', $pend['ped']], ['admin_catalogo', 'Catálogo', 'sistema']]],
] : (modo_demo() ? [
    ['', [['projetos', 'Projetos', 'tela']]],
] : [
    ['', [['painel', 'Início', 'painel'], ['projetos', 'Meu projeto', 'tela'], ['orcamentos', 'Meus orçamentos', 'cifrao', $pend['orc'] ?? 0], ['solicitacoes', 'Solicitar alterações', 'mensagem'], ['arquivos', 'Meus arquivos', 'documento']]],
]);
// Página atual → item do menu (páginas de detalhe acendem o item da lista).
$mapaAtivo = ['projeto' => 'projetos', 'visualizar' => 'projetos', 'orcamento' => 'orcamentos', 'solicitacao' => 'solicitacoes', 'admin_cliente' => 'admin_clientes', 'admin_projeto' => 'admin_projetos', 'admin_orcamento' => 'admin_orcamentos', 'admin_solicitacao' => 'admin_solicitacoes'];
$ativo = $mapaAtivo[$page] ?? $page;
if ($admin && $page === 'visualizar') $ativo = 'admin_projetos';
if ($admin && $page === 'orcamento') $ativo = 'admin_orcamentos';
$iniciais = mb_strtoupper(implode('', array_map(fn($p) => mb_substr($p, 0, 1), array_slice(preg_split('/\s+/', trim($u['nome'])), 0, 2))));
$logoCliente = !$admin ? url_imagem($u['cliente_logo'] ?? '') : null;
$telaCheia = !empty($telaCheia);
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e(($tituloPagina ? $tituloPagina.' | ' : '').cfg('app_name'))?></title>
<meta name="robots" content="noindex">
<meta name="theme-color" content="#101a33">
<link rel="icon" href="<?=asset('assets/img/favicon.svg')?>" type="image/svg+xml">
<link rel="stylesheet" href="<?=asset('assets/css/app.css')?>">
<link rel="stylesheet" href="<?=asset('assets/css/painel.css')?>">
<?php if (!$admin && !empty($u['cliente_cor']) && preg_match('/^#[0-9a-f]{6}$/i', $u['cliente_cor'])): ?><style>:root { --cor-cliente: <?=$u['cliente_cor']?>; }</style><?php endif; ?>
</head>
<body class="painel <?= $admin ? 'painel--admin' : 'painel--cliente' ?> pg-<?=e(str_replace('_', '-', $page))?><?= $telaCheia ? ' painel--tela-cheia' : '' ?>">
<aside class="lateral" id="lateral">
  <div class="lateral__topo">
    <a href="<?=url_pagina(pagina_inicial())?>" aria-label="Início"><?php $logoClaro = true; require __DIR__.'/../partials/logo.php'; ?></a>
    <button class="lateral__fechar" type="button" aria-label="Fechar menu" data-menu-lateral><?=icone('fechar')?></button>
  </div>
  <?php if (!$admin && ($u['cliente_nome'] ?? '') !== ''): ?>
    <div class="lateral__cliente">
      <?php if ($logoCliente): ?><span class="lateral__logo"><img src="<?=$logoCliente?>" alt=""></span><?php else: ?><span class="avatar avatar--g"><?=e(mb_substr($u['cliente_nome'], 0, 1))?></span><?php endif; ?>
      <div><small>Área exclusiva</small><b><?=e($u['cliente_nome'])?></b></div>
    </div>
  <?php elseif ($admin): ?>
    <div class="lateral__cliente lateral__cliente--admin"><span class="avatar avatar--g"><?=icone('chave')?></span><div><small>Administração</small><b>Painel master</b></div></div>
  <?php endif; ?>
  <nav class="lateral__menu" aria-label="Menu">
    <?php foreach ($menu as [$grupo, $itens]): ?>
      <?php if ($grupo): ?><span class="lateral__grupo"><?=e($grupo)?></span><?php endif; ?>
      <?php foreach ($itens as $it): [$p, $rotulo, $ic] = $it; $qtd = (int)($it[3] ?? 0); ?>
        <a href="<?=url_pagina($p)?>"<?= $ativo === $p ? ' class="ativo" aria-current="page"' : '' ?>><?=icone($ic)?><span><?=e($rotulo)?></span><?php if ($qtd): ?><em class="contador"><?=$qtd?></em><?php endif; ?></a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>
  <div class="lateral__rodape">
    <?php if ($admin): ?><a href="<?=url_pagina('inicio')?>" target="_blank" rel="noopener"><?=icone('externo')?> Ver site público</a><?php else: ?>
    <?php if (contato('whatsapp')): ?><a href="<?=e(link_whatsapp('Olá! Preciso de ajuda com a minha área no orcamento.net.'))?>" target="_blank" rel="noopener"><?=icone('whatsapp')?> Suporte</a><?php else: ?><a href="mailto:<?=e(contato('email'))?>"><?=icone('email')?> Suporte</a><?php endif; ?>
    <?php endif; ?>
  </div>
</aside>
<div class="lateral__fundo" data-menu-lateral></div>

<div class="principal">
  <header class="barra">
    <button class="barra__menu" type="button" aria-label="Abrir menu" aria-controls="lateral" data-menu-lateral><?=icone('menu')?></button>
    <div class="barra__titulo"><?=e($tituloPagina ?? '')?></div>
    <details class="app-usuario">
      <summary><span class="avatar"><?=e($iniciais)?></span><span class="app-usuario__nome"><?=e(explode(' ', $u['nome'])[0])?></span></summary>
      <div class="app-usuario__menu">
        <div class="app-usuario__cab"><strong><?=e($u['nome'])?></strong><span><?=e($u['email'])?></span><?php if ($admin): ?><em class="selo-perfil">Administrador master</em><?php endif; ?></div>
        <a href="<?=url_pagina('minha_senha')?>"><?=icone('portal')?> Minha senha</a>
        <form method="post" action="<?=url_pagina('logout')?>"><?=csrf_field()?><button type="submit"><?=icone('sair')?> Sair</button></form>
      </div>
    </details>
  </header>
  <main class="conteudo" id="conteudo">
    <?php foreach (pull_flashes() as $f): ?><div class="aviso aviso--<?=e($f['type'])?>" role="status"><?=e($f['message'])?></div><?php endforeach; ?>
    <?=$contentHtml?>
  </main>
</div>
<script src="<?=asset('assets/js/app.js')?>" defer></script>
</body>
</html>
