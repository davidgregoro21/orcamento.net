<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $acao = post_text('acao');
    if ($acao === 'novo_admin') {
        $nome = mb_substr(post_text('nome'), 0, 120); $email = mb_strtolower(mb_substr(post_text('email'), 0, 160));
        if (mb_strlen($nome) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL)) { flash('erro', 'Informe nome e e-mail válidos.'); redirect('admin_usuarios'); }
        $dup = db()->prepare('SELECT id FROM usuarios WHERE email = ?'); $dup->execute([$email]);
        if ($dup->fetch()) { flash('erro', 'Já existe um usuário com esse e-mail.'); redirect('admin_usuarios'); }
        $senha = senha_provisoria();
        db()->prepare('INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo, deve_trocar_senha, criado_em) VALUES (?,?,?,?,1,1,?)')->execute([$nome, $email, hash_senha($senha), 'admin', agora()]);
        audit('admin_criado', $email);
        $_SESSION['credencial_admin'] = [$nome, $email, $senha];
        redirect('admin_usuarios');
    }
    $st = db()->prepare('SELECT * FROM usuarios WHERE id = ?'); $st->execute([(int)post_text('id')]); $alvo = $st->fetch();
    if (!$alvo) redirect('admin_usuarios');
    $proprio = (int)$alvo['id'] === (int)current_user()['id'];
    if ($acao === 'nova_senha') {
        $senha = senha_provisoria();
        db()->prepare('UPDATE usuarios SET senha_hash = ?, deve_trocar_senha = 1 WHERE id = ?')->execute([hash_senha($senha), $alvo['id']]);
        audit('senha_redefinida', $alvo['email']);
        $_SESSION['credencial_admin'] = [$alvo['nome'], $alvo['email'], $senha];
    } elseif ($acao === 'alternar' && !$proprio) {
        db()->prepare('UPDATE usuarios SET ativo = ? WHERE id = ?')->execute([(int)$alvo['ativo'] ? 0 : 1, $alvo['id']]);
        audit((int)$alvo['ativo'] ? 'usuario_bloqueado' : 'usuario_liberado', $alvo['email']);
        flash('sucesso', (int)$alvo['ativo'] ? 'Acesso bloqueado.' : 'Acesso liberado.');
    } elseif ($proprio) flash('erro', 'Você não pode bloquear o seu próprio usuário.');
    redirect('admin_usuarios');
}
$cred = $_SESSION['credencial_admin'] ?? null; unset($_SESSION['credencial_admin']);
$lista = db()->query('SELECT u.*, c.nome AS cliente FROM usuarios u LEFT JOIN clientes c ON c.id = u.cliente_id ORDER BY u.perfil, c.nome, u.nome')->fetchAll();
?>
<div class="pagina-cab"><div><h1>Usuários</h1><p>Todos os logins da plataforma. Logins de clientes são criados na ficha de cada cliente.</p></div></div>
<?php if ($cred): ?><div class="aviso aviso--sucesso">Senha provisória de <b><?=e($cred[0])?></b> (<?=e($cred[1])?>): <code class="senha-mostrar"><?=e($cred[2])?></code> — aparece só agora. No primeiro acesso será pedida uma senha nova.</div><?php endif; ?>
<section class="cartao">
  <h2 class="cartao__titulo">Novo administrador</h2>
  <form class="form-linha" method="post" action="<?=url_pagina('admin_usuarios')?>"><?=csrf_field()?><input type="hidden" name="acao" value="novo_admin"><input name="nome" placeholder="Nome" required><input type="email" name="email" placeholder="E-mail" required><button class="btn btn--primario"><?=icone('mais')?> Criar</button></form>
  <small class="mudo">Administradores têm acesso total ao painel master.</small>
</section>
<div class="cartao tabela-wrap"><table class="tabela">
  <thead><tr><th>Nome</th><th>E-mail</th><th>Perfil / cliente</th><th>Último acesso</th><th>Situação</th><th class="direita">Ações</th></tr></thead>
  <tbody>
  <?php foreach ($lista as $l): ?>
    <tr class="<?= (int)$l['ativo'] ? '' : 'inativo' ?>"><td><b><?=e($l['nome'])?></b></td><td><?=e($l['email'])?></td>
      <td><?= $l['perfil'] === 'admin' ? '<span class="selo selo--admin">Administrador</span>' : ($l['cliente_id'] ? '<a href="'.url_pagina('admin_cliente', ['id' => $l['cliente_id'], 'aba' => 'usuarios']).'">'.e($l['cliente'] ?? '').'</a>' : '<span class="mudo">sem cliente</span>') ?></td>
      <td><?= $l['ultimo_acesso_em'] ? data_hora($l['ultimo_acesso_em']) : '<span class="mudo">nunca</span>' ?></td>
      <td><span class="selo selo--<?= (int)$l['ativo'] ? 'ok' : 'off' ?>"><?= (int)$l['ativo'] ? 'Ativo' : 'Bloqueado' ?></span></td>
      <td class="direita acoes">
        <form method="post" action="<?=url_pagina('admin_usuarios')?>" data-confirmar="Gerar nova senha provisória para <?=e($l['nome'])?>?"><?=csrf_field()?><input type="hidden" name="acao" value="nova_senha"><input type="hidden" name="id" value="<?=(int)$l['id']?>"><button class="btn btn--linha btn--p">Nova senha</button></form>
        <?php if ((int)$l['id'] !== (int)current_user()['id']): ?><form method="post" action="<?=url_pagina('admin_usuarios')?>"><?=csrf_field()?><input type="hidden" name="acao" value="alternar"><input type="hidden" name="id" value="<?=(int)$l['id']?>"><button class="btn btn--linha btn--p"><?= (int)$l['ativo'] ? 'Bloquear' : 'Liberar' ?></button></form><?php endif; ?>
      </td></tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
