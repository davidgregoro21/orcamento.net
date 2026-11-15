<?php
// Validação no servidor: só projetos liberados do próprio cliente (admin vê qualquer um).
$p = registro_do_cliente('projetos', (int)($_GET['id'] ?? 0), 'AND ativo = 1');
if (!$p) { http_response_code(403); flash('erro', 'Esse projeto não está liberado para a sua conta.'); redirect(pagina_inicial()); }
audit('ver_site', $p['slug']);
$tituloPagina = 'Rascunho – Demo do projeto';
$telaCheia = true;
$embed = (int)$p['permitir_embed'] === 1;
$src = url_previa($p, current_user());   // link assinado, válido por poucos minutos
?>
<div class="visualizador">
  <div class="visualizador__barra">
    <a class="btn btn--linha btn--p" href="<?=url_pagina(is_admin() ? 'admin_projeto' : (modo_demo() ? 'projetos' : 'projeto'), is_admin() || !modo_demo() ? ['id' => $p['id']] : [])?>">← <?= modo_demo() ? 'Projetos' : e($p['nome']) ?></a>
    <div class="visualizador__titulo"><span class="selo selo--aviso">Rascunho – Demo do projeto</span><b><?=e($p['nome'])?></b></div>
    <?php if ($embed): ?>
    <div class="dispositivos" role="group" aria-label="Tamanho da tela">
      <button type="button" class="ativo" data-largura="100%" title="Computador"><?=icone('tela')?></button>
      <button type="button" data-largura="820px" title="Tablet"><?=icone('site')?></button>
      <button type="button" data-largura="390px" title="Celular"><?=icone('usuario')?></button>
    </div>
    <?php endif; ?>
    <a class="btn btn--primario btn--p" href="<?=url_pagina('abrir', ['id' => $p['id']])?>" target="_blank" rel="noopener"><?=icone('externo')?> Abrir em nova aba</a>
  </div>
  <?php if ($embed): ?>
    <div class="visualizador__palco"><iframe id="quadro-site" src="<?=e($src)?>" title="<?=e($p['nome'])?>" loading="eager" referrerpolicy="no-referrer"></iframe></div>
    <p class="visualizador__dica mudo">Se o site não aparecer acima, use <b>Abrir em nova aba</b>.</p>
  <?php else: ?>
    <div class="cartao vazio"><?=icone('externo')?><p>Este projeto abre em uma nova aba.</p><a class="btn btn--primario" href="<?=url_pagina('abrir', ['id' => $p['id']])?>" target="_blank" rel="noopener">Abrir o site</a></div>
  <?php endif; ?>
</div>
