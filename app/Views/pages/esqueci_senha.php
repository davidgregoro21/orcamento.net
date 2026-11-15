<?php
if (is_logged()) redirect(pagina_inicial());
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = mb_strtolower(trim((string)($_POST['email'] ?? '')));
    // Resposta sempre igual: não revela quais e-mails têm cadastro.
    if (limite_taxa('esqueci_ip:'.ip_cliente(), 10, 3600) && limite_taxa('esqueci_email:'.$email, 3, 3600) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $st = db()->prepare('SELECT * FROM usuarios WHERE email = ? AND ativo = 1'); $st->execute([$email]);
        if ($u = $st->fetch()) {
            $link = criar_link_redefinicao($u);
            enviar_email($u['email'], 'Redefinição de senha – orcamento.net', email_modelo('Redefinir sua senha',
                '<p>Olá, '.e(explode(' ', $u['nome'])[0]).'.</p><p>Recebemos um pedido para criar uma nova senha de acesso ao portal.</p>'
                .'<p><a href="'.e($link).'" style="display:inline-block;background:#2f5bea;color:#fff;padding:12px 20px;border-radius:999px;text-decoration:none;font-weight:700">Criar nova senha</a></p>'
                .'<p style="font-size:13px;color:#66788a">Se o botão não funcionar, copie este endereço no navegador:<br>'.e($link).'</p>'
                .'<p style="color:#66788a">O link vale por 1 hora e só pode ser usado uma vez. Se não foi você, ignore este e-mail.</p>'));
            audit('senha_reset_pedido', $email, (int)$u['id']);
        }
    }
    flash('info', 'Se o e-mail estiver cadastrado, enviamos um link para criar uma nova senha. Confira também a caixa de spam.');
    redirect('login');
}
?>
<h1 class="guest__titulo">Esqueci minha senha</h1>
<p class="guest__sub">Informe o e-mail do seu acesso. Enviaremos um link para criar uma nova senha.</p>
<form method="post" action="<?=url_pagina('esqueci_senha')?>" class="form">
  <?=csrf_field()?>
  <label class="campo"><span>E-mail</span><input type="email" name="email" required autocomplete="username" autofocus></label>
  <button class="btn btn--primario btn--g btn--bloco" type="submit">Enviar link <?=icone('seta')?></button>
</form>
<p class="guest__ajuda"><a href="<?=url_pagina('login')?>">← Voltar para o login</a></p>
