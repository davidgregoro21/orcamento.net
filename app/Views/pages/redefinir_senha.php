<?php
if (is_logged()) redirect(pagina_inicial());
$token = (string)($_GET['t'] ?? $_POST['t'] ?? '');
$r = buscar_redefinicao($token);
if (!$r) { flash('erro', 'Link inválido ou expirado. Peça um novo.'); redirect('esqueci_senha'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $nova = (string)($_POST['nova'] ?? ''); $conf = (string)($_POST['confirmar'] ?? '');
    if ($erro = senha_fraca($nova)) { flash('erro', $erro); redirect('redefinir_senha', ['t' => $token]); }
    if ($nova !== $conf) { flash('erro', 'A confirmação não é igual à nova senha.'); redirect('redefinir_senha', ['t' => $token]); }
    db()->prepare('UPDATE usuarios SET senha_hash = ?, deve_trocar_senha = 0 WHERE id = ?')->execute([hash_senha($nova), $r['usuario_id']]);
    db()->prepare('UPDATE senha_reset SET usado_em = ? WHERE usuario_id = ? AND usado_em IS NULL')->execute([agora(), $r['usuario_id']]);   // invalida todos os links pendentes
    audit('senha_redefinida_link', $r['email'], (int)$r['usuario_id']);
    flash('sucesso', 'Senha criada. Entre com o seu e-mail e a nova senha.');
    redirect('login');
}
?>
<h1 class="guest__titulo">Criar nova senha</h1>
<p class="guest__sub">Acesso de <b><?=e($r['email'])?></b>.</p>
<form method="post" action="<?=url_pagina('redefinir_senha')?>" class="form">
  <?=csrf_field()?><input type="hidden" name="t" value="<?=e($token)?>">
  <label class="campo"><span>Nova senha</span><span class="campo-senha"><input type="password" name="nova" required minlength="8" autocomplete="new-password"><button type="button" class="ver-senha" aria-label="Mostrar senha"><?=icone('olho')?></button></span><small>Mínimo de 8 caracteres, com letras e números.</small></label>
  <label class="campo"><span>Confirmar nova senha</span><input type="password" name="confirmar" required minlength="8" autocomplete="new-password"></label>
  <button class="btn btn--primario btn--g btn--bloco" type="submit">Salvar nova senha</button>
</form>
