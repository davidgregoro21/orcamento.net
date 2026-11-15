<?php
/** Formulário de pedido de orçamento (usado em Contato e na página de cada sistema). Processado em pages/contato.php. */
$antigo = $_SESSION['form_orcamento'] ?? []; $erroForm = $_SESSION['form_orcamento_erro'] ?? null;
unset($_SESSION['form_orcamento'], $_SESSION['form_orcamento_erro']);
if (!$antigo && !empty($preForm)) $antigo = $preForm;   // pré-preenchimento (ex.: sistema escolhido)
$v = fn(string $k) => e((string)($antigo[$k] ?? ''));
?>
<form class="cartao form" method="post" action="<?=url_pagina('contato')?>" novalidate>
      <h3 class="cartao__titulo">Pedido de orçamento</h3>
      <?php if ($erroForm): ?><div class="aviso aviso--erro" role="alert"><?=e($erroForm)?></div><?php endif; ?>
      <?=csrf_field()?>
      <input type="hidden" name="voltar" value="<?=e($voltarForm ?? '')?>">
      <div class="isca" aria-hidden="true"><label>Não preencha<input type="text" name="site" tabindex="-1" autocomplete="off"></label></div>
      <div class="form__grade">
        <label class="campo"><span>Nome *</span><input name="nome" required maxlength="120" autocomplete="name" value="<?=$v('nome')?>"></label>
        <label class="campo"><span>Empresa</span><input name="empresa" maxlength="120" autocomplete="organization" value="<?=$v('empresa')?>"></label>
        <label class="campo"><span>E-mail *</span><input type="email" name="email" required maxlength="160" autocomplete="email" value="<?=$v('email')?>"></label>
        <label class="campo"><span>Telefone / WhatsApp *</span><input type="tel" name="telefone" required maxlength="20" data-mascara="telefone" autocomplete="tel" value="<?=$v('telefone')?>"></label>
        <label class="campo"><span>Serviço</span><select name="servico" id="campo-servico"><?php foreach (conteudo('opcoes_servico') as $o): ?><option<?= ($antigo['servico'] ?? '') === $o ? ' selected' : '' ?>><?=e($o)?></option><?php endforeach; ?></select></label>
        <label class="campo"><span>Para quando?</span><select name="prazo"><?php foreach (conteudo('opcoes_prazo') as $o): ?><option<?= ($antigo['prazo'] ?? '') === $o ? ' selected' : '' ?>><?=e($o)?></option><?php endforeach; ?></select></label>
        <label class="campo campo--inteiro"><span>Descreva o projeto</span><textarea name="mensagem" rows="4" maxlength="3000" placeholder="Ex.: site para minha empresa com 5 páginas e formulário de contato."><?=$v('mensagem')?></textarea></label>
      </div>
      <label class="aceite"><input type="checkbox" name="aceite" value="1" required<?= !empty($antigo['aceite']) ? ' checked' : '' ?>> <span>Autorizo o contato com os dados acima (LGPD). Não compartilhamos seus dados.</span></label>
      <button class="btn btn--primario btn--g btn--bloco" type="submit">Enviar pedido de orçamento <?=icone('seta')?></button>
    </form>
