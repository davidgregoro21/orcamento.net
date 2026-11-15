<?php
if (is_logged()) redirect(pagina_inicial());
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (!limite_taxa('login_ip:'.ip_cliente(), (int)(cfg('limite_login_ip') ?: 30), 300)) { flash('erro', 'Muitas tentativas. Aguarde alguns minutos.'); redirect('login'); }
    $email = mb_strtolower(trim((string)($_POST['email'] ?? ''))); $senha = (string)($_POST['senha'] ?? '');
    if (login_bloqueado($email)) { audit('login_bloqueado', $email); flash('erro', 'Muitas tentativas sem sucesso para este e-mail. Aguarde 15 minutos.'); redirect('login'); }
    $st = db()->prepare('SELECT * FROM usuarios WHERE email = ? AND ativo = 1'); $st->execute([$email]); $u = $st->fetch();
    // Mesmo tempo de resposta com e-mail inexistente: não revela quais e-mails existem.
    $ok = $u ? password_verify($senha, $u['senha_hash']) : (password_verify($senha, password_hash('senha-inexistente', PASSWORD_DEFAULT)) && false);
    if (!$ok) { registrar_falha_login($email); audit('login_falhou', $email); $_SESSION['login_email'] = $email; flash('erro', 'E-mail ou senha inválidos.'); redirect('login'); }
    if (password_needs_rehash($u['senha_hash'], PASSWORD_DEFAULT)) db()->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')->execute([hash_senha($senha), $u['id']]);
    session_regenerate_id(true);
    $voltar = (string)($_SESSION['voltar_para'] ?? '');
    $_SESSION = ['user' => build_session_user($u), 'last_activity' => time()];
    db()->prepare('UPDATE usuarios SET ultimo_acesso_em = ? WHERE id = ?')->execute([agora(), $u['id']]);
    audit('login');
    if (!empty($_SESSION['user']['deve_trocar_senha'])) { flash('info', 'Bem-vindo! Crie uma senha pessoal para continuar.'); redirect('minha_senha'); }
    // Volta para a página que pediu login (só endereços internos).
    if (preg_match('#^/[^/\\\\]#', $voltar) && str_starts_with($voltar, base_path().'/')) { header('Location: '.$voltar); exit; }
    ir_para_inicio();
}
$emailAntigo = (string)($_SESSION['login_email'] ?? ''); unset($_SESSION['login_email']);
?>
<h1 class="guest__titulo">Área exclusiva</h1>
<p class="guest__sub">Entre com o e-mail e a senha que você recebeu.</p>
<form method="post" action="<?=url_pagina('login')?>" class="form">
  <?=csrf_field()?>
  <label class="campo"><span>E-mail</span><input type="email" name="email" required autocomplete="username" autofocus value="<?=e($emailAntigo)?>"></label>
  <label class="campo"><span>Senha</span>
    <span class="campo-senha"><input type="password" name="senha" required autocomplete="current-password"><button type="button" class="ver-senha" aria-label="Mostrar senha"><?=icone('olho')?></button></span>
  </label>
  <button class="btn btn--primario btn--g btn--bloco" type="submit">Entrar <?=icone('seta')?></button>
</form>
<p class="guest__ajuda"><a href="<?=url_pagina('esqueci_senha')?>">Esqueci minha senha</a> · Não recebeu o acesso? Fale com <a href="mailto:<?=e(contato('email'))?>"><?=e(contato('email'))?></a>.</p>
