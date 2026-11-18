<?php
// Validação no servidor: só projetos liberados do próprio cliente (admin vê qualquer um).
$p = registro_do_cliente('projetos', (int)($_GET['id'] ?? 0), 'AND ativo = 1');
if (!$p) { http_response_code(403); flash('erro', 'Esse projeto não está liberado para a sua conta.'); redirect(pagina_inicial()); }
audit('ver_site', $p['slug']);
$tituloPagina = 'Rascunho – Demo do projeto';
$telaCheia = true;   // layout sem menu lateral e sem cabeçalho: o site ocupa a tela
$embed = (int)$p['permitir_embed'] === 1;
$src = url_previa($p, current_user());   // link assinado, válido por poucos minutos
$voltar = url_pagina(is_admin() ? 'admin_projeto' : (modo_demo() ? 'projetos' : 'projeto'), is_admin() || !modo_demo() ? ['id' => $p['id']] : []);
$u = current_user();
?>
<div class="visualizador" id="visualizador">
  <div class="visualizador__barra">
    <a class="vz-btn" href="<?=$voltar?>" title="Voltar"><?=icone('voltar')?><span><?= is_admin() ? 'Projeto' : 'Projetos' ?></span></a>
    <div class="visualizador__titulo"><span class="selo selo--aviso">Rascunho – Demo</span><b><?=e($p['nome'])?></b></div>
    <?php if ($embed): ?>
    <div class="dispositivos" role="group" aria-label="Tamanho da tela">
      <button type="button" class="ativo" data-largura="100%" title="Computador" aria-label="Computador"><?=icone('tela')?></button>
      <button type="button" data-largura="820px" title="Tablet" aria-label="Tablet"><?=icone('tablet')?></button>
      <button type="button" data-largura="390px" title="Celular" aria-label="Celular"><?=icone('celular')?></button>
    </div>
    <button type="button" class="vz-btn" data-tela-cheia title="Tela cheia (Esc para sair)"><?=icone('expandir')?><span>Tela cheia</span></button>
    <button type="button" class="vz-btn" data-ocultar-barra title="Esconder esta barra"><?=icone('recolher')?></button>
    <?php endif; ?>
    <a class="vz-btn vz-btn--primario" href="<?=url_pagina('abrir', ['id' => $p['id']])?>" target="_blank" rel="noopener" title="Abrir em nova aba"><?=icone('externo')?><span>Nova aba</span></a>
    <details class="app-usuario vz-usuario">
      <summary title="<?=e($u['nome'])?>"><span class="avatar"><?=e(mb_strtoupper(mb_substr($u['nome'], 0, 1)))?></span></summary>
      <div class="app-usuario__menu">
        <div class="app-usuario__cab"><strong><?=e($u['nome'])?></strong><span><?=e($u['email'])?></span></div>
        <a href="<?=$voltar?>"><?=icone('voltar')?> Voltar ao portal</a>
        <a href="<?=url_pagina('minha_senha')?>"><?=icone('portal')?> Minha senha</a>
        <form method="post" action="<?=url_pagina('logout')?>"><?=csrf_field()?><button type="submit"><?=icone('sair')?> Sair</button></form>
      </div>
    </details>
  </div>
  <?php if ($embed): ?>
    <button type="button" class="vz-mostrar" data-ocultar-barra title="Mostrar a barra"><?=icone('menu')?> Mostrar barra</button>
    <div class="visualizador__palco"><iframe id="quadro-site" src="<?=e($src)?>" title="<?=e($p['nome'])?>" loading="eager" referrerpolicy="no-referrer"></iframe></div>
  <?php else: ?>
    <div class="cartao vazio vz-vazio"><?=icone('externo')?><p>Este projeto abre em uma nova aba.</p><a class="btn btn--primario" href="<?=url_pagina('abrir', ['id' => $p['id']])?>" target="_blank" rel="noopener">Abrir o site</a></div>
  <?php endif; ?>
</div>
