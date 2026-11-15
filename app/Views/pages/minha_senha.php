<?php
$u = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $st = db()->prepare('SELECT senha_hash FROM usuarios WHERE id = ?'); $st->execute([$u['id']]); $hash = (string)$st->fetchColumn();
    $atual = (string)($_POST['atual'] ?? ''); $nova = (string)($_POST['nova'] ?? ''); $conf = (string)($_POST['confirmar'] ?? '');
    if (!password_verify($atual, $hash)) { flash('erro', 'A senha atual não confere.'); redirect('minha_senha'); }
    if ($erro = senha_fraca($nova)) { flash('erro', $erro); redirect('minha_senha'); }
    if ($nova !== $conf) { flash('erro', 'A confirmação não é igual à nova senha.'); redirect('minha_senha'); }
    if (password_verify($nova, $hash)) { flash('erro', 'Escolha uma senha diferente da atual.'); redirect('minha_senha'); }
    db()->prepare('UPDATE usuarios SET senha_hash = ?, deve_trocar_senha = 0 WHERE id = ?')->execute([hash_senha($nova), $u['id']]);
    session_regenerate_id(true);
    audit('senha_alterada');
    flash('sucesso', 'Senha alterada com sucesso.');
    ir_para_inicio();
}
?>
<div class="pagina-cab"><div><h1>Minha senha</h1><p><?= !empty($u['deve_trocar_senha']) ? 'Você entrou com uma senha provisória. Crie uma senha pessoal para continuar.' : 'Altere a senha de acesso ao portal.' ?></p></div></div>
<form class="cartao form form--estreito" method="post" action="<?=url_pagina('minha_senha')?>">
  <?=csrf_field()?>
  <label class="campo"><span>Senha atual<?= !empty($u['deve_trocar_senha']) ? ' (a provisória que você recebeu)' : '' ?></span><input type="password" name="atual" required autocomplete="current-password"></label>
  <label class="campo"><span>Nova senha</span><input type="password" name="nova" required minlength="8" autocomplete="new-password"><small>Mínimo de 8 caracteres, com letras e números.</small></label>
  <label class="campo"><span>Confirmar nova senha</span><input type="password" name="confirmar" required minlength="8" autocomplete="new-password"></label>
  <button class="btn btn--primario btn--bloco" type="submit">Salvar nova senha</button>
</form>
