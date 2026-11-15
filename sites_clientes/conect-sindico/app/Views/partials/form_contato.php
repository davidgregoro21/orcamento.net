<?php
/** Formulário de contato/proposta. Envia sempre para a página Contato, que processa e redireciona. */
$antigo = $_SESSION['form_contato'] ?? []; $erroForm = $_SESSION['form_contato_erro'] ?? null;
unset($_SESSION['form_contato'], $_SESSION['form_contato_erro']);
$v = fn(string $k) => e((string)($antigo[$k] ?? ''));
$origemForm = $origemForm ?? $page;
?>
<form class="form" method="post" action="<?=url_pagina('contato')?>" novalidate>
  <?php if ($erroForm): ?><div class="aviso aviso--erro" role="alert"><?=e($erroForm)?></div><?php endif; ?>
  <?=csrf_field()?>
  <input type="hidden" name="origem" value="<?=e($origemForm)?>">
  <div class="form__isca" aria-hidden="true"><label>Não preencha<input type="text" name="site" tabindex="-1" autocomplete="off"></label></div>
  <div class="form__grade">
    <label class="campo"><span>Nome *</span><input type="text" name="nome" required maxlength="120" autocomplete="name" value="<?=$v('nome')?>"></label>
    <label class="campo"><span>Telefone / WhatsApp *</span><input type="tel" name="telefone" required maxlength="20" autocomplete="tel" inputmode="tel" data-mascara="telefone" value="<?=$v('telefone')?>"></label>
    <label class="campo"><span>E-mail *</span><input type="email" name="email" required maxlength="160" autocomplete="email" value="<?=$v('email')?>"></label>
    <label class="campo"><span>Você é</span>
      <select name="perfil">
        <?php foreach (['Síndico(a)', 'Membro do conselho', 'Condômino(a)', 'Construtora / incorporadora', 'Outro'] as $op): ?>
          <option<?= ($antigo['perfil'] ?? '') === $op ? ' selected' : '' ?>><?=e($op)?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label class="campo"><span>Nome do condomínio</span><input type="text" name="condominio" maxlength="120" value="<?=$v('condominio')?>"></label>
    <label class="campo"><span>Nº de unidades</span><input type="number" name="unidades" min="1" max="5000" inputmode="numeric" value="<?=$v('unidades')?>"></label>
    <label class="campo campo--inteiro"><span>Mensagem</span><textarea name="mensagem" rows="4" maxlength="3000" placeholder="Conte um pouco sobre o condomínio e o que você precisa."><?=$v('mensagem')?></textarea></label>
  </div>
  <label class="form__aceite"><input type="checkbox" name="aceite" value="1" required<?= !empty($antigo['aceite']) ? ' checked' : '' ?>>
    <span>Autorizo o contato da Conect Síndico com os dados acima, conforme a <a href="<?=url_pagina('privacidade')?>">política de privacidade</a>.</span></label>
  <button class="btn btn--laranja btn--grande" type="submit">Quero receber uma proposta <?=icone('seta')?></button>
</form>
