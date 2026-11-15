<?php
$id = (int)($_GET['id'] ?? 0);
$buscar = function (int $id): ?array { $st = db()->prepare('SELECT * FROM clientes WHERE id = ?'); $st->execute([$id]); return $st->fetch() ?: null; };
$c = $id ? $buscar($id) : null;
if ($id && !$c) { flash('erro', 'Cliente não encontrado.'); redirect('admin_clientes'); }
$campos = ['nome', 'documento', 'localizacao', 'tipo_projeto', 'categoria', 'contato_nome', 'email', 'telefone', 'site_atual', 'cor', 'observacoes'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $acao = post_text('acao');
    if ($acao === 'salvar') {
        $d = []; foreach ($campos as $k) $d[$k] = mb_substr(post_text($k), 0, $k === 'observacoes' ? 5000 : 255);
        if (mb_strlen($d['nome']) < 2) { flash('erro', 'Informe o nome do cliente.'); redirect('admin_cliente', $id ? ['id' => $id] : ['novo' => 1]); }
        if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) { flash('erro', 'E-mail do cliente inválido.'); redirect('admin_cliente', $id ? ['id' => $id] : ['novo' => 1]); }
        if ($d['cor'] !== '' && !preg_match('/^#[0-9a-f]{6}$/i', $d['cor'])) $d['cor'] = '';
        if ($d['site_atual'] !== '' && !url_valida($d['site_atual'])) $d['site_atual'] = 'https://'.preg_replace('#^https?://#i', '', $d['site_atual']);
        try { $logo = guardar_imagem($_FILES['logo'] ?? [], 'clientes'); } catch (RuntimeException $e) { flash('erro', $e->getMessage()); redirect('admin_cliente', $id ? ['id' => $id] : ['novo' => 1]); }
        $vals = array_map(fn($k) => $d[$k] !== '' ? $d[$k] : null, $campos);
        if ($id) {
            db()->prepare('UPDATE clientes SET '.implode(', ', array_map(fn($k) => "$k = ?", $campos)).($logo ? ', logo = ?' : '').' WHERE id = ?')->execute([...$vals, ...($logo ? [$logo] : []), $id]);
            audit('cliente_editado', $d['nome']);
        } else {
            db()->prepare('INSERT INTO clientes ('.implode(', ', $campos).', logo, ativo, criado_em) VALUES ('.implode(', ', array_fill(0, count($campos), '?')).', ?, 1, ?)')->execute([...$vals, $logo, agora()]);
            $id = (int)db()->lastInsertId();
            audit('cliente_criado', $d['nome']);
        }
        flash('sucesso', 'Cliente salvo.');
        redirect('admin_cliente', ['id' => $id]);
    }
    if (!$c) redirect('admin_clientes');
    if ($acao === 'alternar') {
        db()->prepare('UPDATE clientes SET ativo = ? WHERE id = ?')->execute([(int)$c['ativo'] ? 0 : 1, $id]);
        audit((int)$c['ativo'] ? 'cliente_desativado' : 'cliente_ativado', $c['nome']);
        flash('sucesso', (int)$c['ativo'] ? 'Cliente desativado: os usuários dele não conseguem mais entrar.' : 'Cliente reativado.');
        redirect('admin_cliente', ['id' => $id]);
    }
    if ($acao === 'excluir') {
        $st = db()->prepare('SELECT (SELECT COUNT(*) FROM projetos WHERE cliente_id = ?) + (SELECT COUNT(*) FROM orcamentos WHERE cliente_id = ?) + (SELECT COUNT(*) FROM arquivos WHERE cliente_id = ?)'); $st->execute([$id, $id, $id]);
        if ((int)$st->fetchColumn() > 0) { flash('erro', 'Este cliente tem projetos, orçamentos ou arquivos. Desative em vez de excluir (o histórico é preservado).'); redirect('admin_cliente', ['id' => $id]); }
        db()->prepare('DELETE FROM usuarios WHERE cliente_id = ?')->execute([$id]);
        db()->prepare('DELETE FROM clientes WHERE id = ?')->execute([$id]);
        audit('cliente_excluido', $c['nome']);
        flash('sucesso', 'Cliente excluído.');
        redirect('admin_clientes');
    }
    if ($acao === 'novo_usuario') {
        $nome = mb_substr(post_text('nome'), 0, 120); $email = mb_strtolower(mb_substr(post_text('email'), 0, 160));
        if (mb_strlen($nome) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL)) { flash('erro', 'Informe nome e e-mail válidos para o usuário.'); redirect('admin_cliente', ['id' => $id, 'aba' => 'usuarios']); }
        $dup = db()->prepare('SELECT id FROM usuarios WHERE email = ?'); $dup->execute([$email]);
        if ($dup->fetch()) { flash('erro', 'Já existe um usuário com esse e-mail.'); redirect('admin_cliente', ['id' => $id, 'aba' => 'usuarios']); }
        $senha = senha_provisoria();
        db()->prepare('INSERT INTO usuarios (cliente_id, nome, email, empresa, senha_hash, perfil, ativo, deve_trocar_senha, criado_em) VALUES (?,?,?,?,?,?,1,1,?)')
            ->execute([$id, $nome, $email, $c['nome'], hash_senha($senha), 'cliente', agora()]);
        audit('usuario_criado', $email.' ('.$c['nome'].')');
        $_SESSION['credencial'] = ['nome' => $nome, 'email' => $email, 'senha' => $senha, 'url' => url_absoluta(url_pagina('login')), 'cliente' => $c['nome']];
        redirect('admin_cliente', ['id' => $id, 'aba' => 'usuarios']);
    }
    if (in_array($acao, ['senha_usuario', 'alternar_usuario', 'excluir_usuario'], true)) {
        $st = db()->prepare('SELECT * FROM usuarios WHERE id = ? AND cliente_id = ?'); $st->execute([(int)post_text('usuario_id'), $id]); $alvo = $st->fetch();
        if (!$alvo) redirect('admin_cliente', ['id' => $id, 'aba' => 'usuarios']);
        if ($acao === 'senha_usuario') {
            $senha = senha_provisoria();
            db()->prepare('UPDATE usuarios SET senha_hash = ?, deve_trocar_senha = 1 WHERE id = ?')->execute([hash_senha($senha), $alvo['id']]);
            audit('senha_redefinida', $alvo['email']);
            $_SESSION['credencial'] = ['nome' => $alvo['nome'], 'email' => $alvo['email'], 'senha' => $senha, 'url' => url_absoluta(url_pagina('login')), 'cliente' => $c['nome']];
        } elseif ($acao === 'alternar_usuario') {
            db()->prepare('UPDATE usuarios SET ativo = ? WHERE id = ?')->execute([(int)$alvo['ativo'] ? 0 : 1, $alvo['id']]);
            audit((int)$alvo['ativo'] ? 'usuario_bloqueado' : 'usuario_liberado', $alvo['email']);
            flash('sucesso', (int)$alvo['ativo'] ? 'Acesso bloqueado.' : 'Acesso liberado.');
        } else {
            db()->prepare('DELETE FROM usuarios WHERE id = ?')->execute([$alvo['id']]);
            audit('usuario_excluido', $alvo['email']);
            flash('sucesso', 'Usuário excluído.');
        }
        redirect('admin_cliente', ['id' => $id, 'aba' => 'usuarios']);
    }
    redirect('admin_cliente', ['id' => $id]);
}

$credencial = $_SESSION['credencial'] ?? null; unset($_SESSION['credencial']);
$editando = !$c || isset($_GET['editar']);
$aba = (string)($_GET['aba'] ?? ($credencial ? 'usuarios' : 'resumo'));
if ($c) {
    $tituloPagina = $c['nome'];
    $q = function (string $sql) use ($id) { $st = db()->prepare($sql); $st->execute([$id]); return $st->fetchAll(); };
    $usuarios = $q('SELECT * FROM usuarios WHERE cliente_id = ? ORDER BY nome');
    $projetos = $q('SELECT * FROM projetos WHERE cliente_id = ? ORDER BY ordem, nome');
    $orcamentos = $q('SELECT * FROM orcamentos WHERE cliente_id = ? ORDER BY criado_em DESC');
    $solicitacoes = $q('SELECT * FROM solicitacoes WHERE cliente_id = ? ORDER BY criado_em DESC');
    $arquivos = $q('SELECT * FROM arquivos WHERE cliente_id = ? ORDER BY criado_em DESC');
    $atividades = $q('SELECT a.*, u.nome FROM auditoria a JOIN usuarios u ON u.id = a.usuario_id WHERE u.cliente_id = ? ORDER BY a.id DESC LIMIT 50');
}
$v = fn(string $k) => e((string)($c[$k] ?? ''));
?>
<?php if ($editando): ?>
<div class="pagina-cab"><div><h1><?= $c ? 'Editar '.e($c['nome']) : 'Novo cliente' ?></h1><p>Dados da empresa. As observações internas nunca aparecem para o cliente.</p></div></div>
<form class="cartao form" method="post" enctype="multipart/form-data" action="<?=url_pagina('admin_cliente', $c ? ['id' => $id] : [])?>">
  <?=csrf_field()?><input type="hidden" name="acao" value="salvar">
  <div class="form__grade">
    <label class="campo"><span>Nome do cliente *</span><input name="nome" required value="<?=$v('nome')?>"></label>
    <label class="campo"><span>CNPJ / CPF</span><input name="documento" value="<?=$v('documento')?>"></label>
    <label class="campo"><span>Localização</span><input name="localizacao" placeholder="Cidade/UF" value="<?=$v('localizacao')?>"></label>
    <label class="campo"><span>Tipo de projeto</span><input name="tipo_projeto" list="tipos" value="<?=$v('tipo_projeto')?>"><datalist id="tipos"><option value="Site institucional"><option value="Site comercial"><option value="Sistema administrativo"><option value="Portal com login"><option value="Automação"></datalist></label>
    <label class="campo campo--inteiro"><span>Categoria / ramo</span><input name="categoria" value="<?=$v('categoria')?>"></label>
    <label class="campo"><span>Pessoa de contato</span><input name="contato_nome" value="<?=$v('contato_nome')?>"></label>
    <label class="campo"><span>E-mail</span><input type="email" name="email" value="<?=$v('email')?>"></label>
    <label class="campo"><span>Telefone / WhatsApp</span><input name="telefone" data-mascara="telefone" value="<?=$v('telefone')?>"></label>
    <label class="campo"><span>Site atual</span><input name="site_atual" placeholder="https://" value="<?=$v('site_atual')?>"></label>
    <label class="campo"><span>Logo do cliente</span><input type="file" name="logo" accept="image/png,image/jpeg,image/webp"><?php if ($c && url_imagem($c['logo'])): ?><small>Atual: <img class="mini-logo" src="<?=url_imagem($c['logo'])?>" alt=""> (envie outra para trocar)</small><?php endif; ?></label>
    <label class="campo"><span>Cor da marca</span><input type="color" name="cor" value="<?=e($c['cor'] ?? '#2f5bea')?>"></label>
    <label class="campo campo--inteiro"><span>Observações internas</span><textarea name="observacoes" rows="3"><?=$v('observacoes')?></textarea></label>
  </div>
  <div class="botoes"><button class="btn btn--primario">Salvar</button><a class="btn btn--linha" href="<?=url_pagina($c ? 'admin_cliente' : 'admin_clientes', $c ? ['id' => $id] : [])?>">Cancelar</a></div>
</form>
<?php return; endif; ?>

<?php $logo = url_imagem($c['logo']); ?>
<div class="cliente-cab">
  <?php if ($logo): ?><span class="cliente-cab__logo"><img src="<?=$logo?>" alt=""></span><?php else: ?><span class="avatar avatar--xg"><?=e(mb_substr($c['nome'], 0, 1))?></span><?php endif; ?>
  <div class="cliente-cab__info">
    <h1><?=e($c['nome'])?> <?= (int)$c['ativo'] ? '' : '<span class="selo selo--off">Desativado</span>' ?></h1>
    <p><?=e(implode(' · ', array_filter([$c['localizacao'], $c['tipo_projeto'], $c['categoria']])))?></p>
  </div>
  <div class="botoes botoes--sem-margem">
    <a class="btn btn--linha" href="<?=url_pagina('admin_cliente', ['id' => $id, 'editar' => 1])?>"><?=icone('editar')?> Editar</a>
    <form method="post" action="<?=url_pagina('admin_cliente', ['id' => $id])?>" data-confirmar="<?= (int)$c['ativo'] ? 'Desativar o cliente? Os usuários dele perdem o acesso.' : 'Reativar o cliente?' ?>"><?=csrf_field()?><input type="hidden" name="acao" value="alternar"><button class="btn btn--linha"><?= (int)$c['ativo'] ? 'Desativar' : 'Reativar' ?></button></form>
  </div>
</div>

<nav class="abas">
  <?php foreach (['resumo' => 'Resumo', 'usuarios' => 'Usuários ('.count($usuarios).')', 'projetos' => 'Projetos ('.count($projetos).')', 'orcamentos' => 'Orçamentos ('.count($orcamentos).')', 'solicitacoes' => 'Solicitações ('.count($solicitacoes).')', 'arquivos' => 'Arquivos ('.count($arquivos).')', 'atividades' => 'Acessos'] as $k => $r): ?>
    <a href="<?=url_pagina('admin_cliente', ['id' => $id, 'aba' => $k])?>"<?= $aba === $k ? ' class="ativo"' : '' ?>><?=e($r)?></a>
  <?php endforeach; ?>
</nav>

<?php if ($aba === 'resumo'): ?>
  <div class="grade-2">
    <section class="cartao">
      <h2 class="cartao__titulo">Dados</h2>
      <dl class="dados">
        <dt>Contato</dt><dd><?=e($c['contato_nome'] ?: '—')?></dd>
        <dt>E-mail</dt><dd><?= $c['email'] ? '<a href="mailto:'.e($c['email']).'">'.e($c['email']).'</a>' : '—' ?></dd>
        <dt>Telefone</dt><dd><?=e($c['telefone'] ?: '—')?></dd>
        <dt>Documento</dt><dd><?=e($c['documento'] ?: '—')?></dd>
        <dt>Site atual</dt><dd><?= $c['site_atual'] ? '<a href="'.e($c['site_atual']).'" target="_blank" rel="noopener">'.e($c['site_atual']).'</a>' : '—' ?></dd>
        <dt>Cliente desde</dt><dd><?=data_curta($c['criado_em'])?></dd>
      </dl>
    </section>
    <section class="cartao">
      <h2 class="cartao__titulo">Observações internas</h2>
      <p class="pre"><?= $c['observacoes'] ? nl2br(e($c['observacoes'])) : '<span class="mudo">Nenhuma observação.</span>' ?></p>
      <h2 class="cartao__titulo">Atalhos</h2>
      <div class="botoes botoes--sem-margem">
        <a class="btn btn--linha btn--p" href="<?=url_pagina('admin_projeto', ['novo' => 1, 'cliente' => $id])?>"><?=icone('mais')?> Projeto</a>
        <a class="btn btn--linha btn--p" href="<?=url_pagina('admin_orcamento', ['novo' => 1, 'cliente' => $id])?>"><?=icone('mais')?> Orçamento</a>
        <a class="btn btn--linha btn--p" href="<?=url_pagina('admin_arquivos', ['cliente' => $id])?>"><?=icone('upload')?> Enviar arquivo</a>
      </div>
    </section>
  </div>
  <?php if (!$projetos && !$orcamentos): ?>
    <form method="post" action="<?=url_pagina('admin_cliente', ['id' => $id])?>" data-confirmar="Excluir este cliente e os usuários dele? Não dá para desfazer."><?=csrf_field()?><input type="hidden" name="acao" value="excluir"><button class="link-perigo"><?=icone('lixeira')?> Excluir cliente</button></form>
  <?php endif; ?>

<?php elseif ($aba === 'usuarios'): ?>
  <?php if ($credencial):
    $msg = "Olá, ".explode(' ', $credencial['nome'])[0]."! Sua área exclusiva no orcamento.net está liberada. Lá você acompanha o projeto, visualiza o seu site e consulta as propostas.\n\nAcesse: ".$credencial['url']."\nE-mail: ".$credencial['email']."\nSenha provisória: ".$credencial['senha']."\n\nNo primeiro acesso o portal pede para você criar uma senha pessoal."; ?>
    <div class="cartao credencial">
      <h2><?=icone('portal')?> Acesso de <?=e($credencial['nome'])?></h2>
      <p class="credencial__aviso">A senha aparece só agora. Copie a mensagem e envie ao cliente.</p>
      <textarea class="credencial__texto" rows="8" readonly id="msg-credencial"><?=e($msg)?></textarea>
      <div class="botoes"><button type="button" class="btn btn--primario" data-copiar="#msg-credencial"><?=icone('documento')?> Copiar mensagem</button><a class="btn btn--whats" href="https://wa.me/<?=e(so_digitos($c['telefone'] ?? '') ? '55'.so_digitos($c['telefone']) : '')?>?text=<?=rawurlencode($msg)?>" target="_blank" rel="noopener"><?=icone('whatsapp')?> Enviar pelo WhatsApp</a></div>
    </div>
  <?php endif; ?>
  <section class="cartao">
    <h2 class="cartao__titulo">Criar login para este cliente</h2>
    <form class="form-linha" method="post" action="<?=url_pagina('admin_cliente', ['id' => $id])?>"><?=csrf_field()?><input type="hidden" name="acao" value="novo_usuario">
      <input name="nome" placeholder="Nome da pessoa" required><input type="email" name="email" placeholder="E-mail (será o login)" required><button class="btn btn--primario"><?=icone('mais')?> Criar acesso</button>
    </form>
    <small class="mudo">Uma senha provisória é gerada e mostrada uma única vez. O usuário só vê os projetos, orçamentos e arquivos deste cliente.</small>
  </section>
  <div class="cartao tabela-wrap"><table class="tabela">
    <thead><tr><th>Nome</th><th>E-mail</th><th>Último acesso</th><th>Situação</th><th class="direita">Ações</th></tr></thead>
    <tbody>
    <?php foreach ($usuarios as $us): ?>
      <tr class="<?= (int)$us['ativo'] ? '' : 'inativo' ?>"><td><b><?=e($us['nome'])?></b></td><td><?=e($us['email'])?></td><td><?= $us['ultimo_acesso_em'] ? data_hora($us['ultimo_acesso_em']) : '<span class="mudo">nunca</span>' ?><?= (int)$us['deve_trocar_senha'] ? '<br><small class="mudo">senha provisória</small>' : '' ?></td><td><span class="selo selo--<?= (int)$us['ativo'] ? 'ok' : 'off' ?>"><?= (int)$us['ativo'] ? 'Ativo' : 'Bloqueado' ?></span></td>
      <td class="direita acoes">
        <?php foreach ([['senha_usuario', 'Nova senha', 'Gerar nova senha provisória?'], ['alternar_usuario', (int)$us['ativo'] ? 'Bloquear' : 'Liberar', ''], ['excluir_usuario', 'Excluir', 'Excluir este usuário?']] as [$ac, $rot, $conf]): ?>
          <form method="post" action="<?=url_pagina('admin_cliente', ['id' => $id])?>"<?= $conf ? ' data-confirmar="'.e($conf).'"' : '' ?>><?=csrf_field()?><input type="hidden" name="acao" value="<?=$ac?>"><input type="hidden" name="usuario_id" value="<?=(int)$us['id']?>"><button class="btn btn--linha btn--p"><?=e($rot)?></button></form>
        <?php endforeach; ?>
      </td></tr>
    <?php endforeach; ?>
    <?php if (!$usuarios): ?><tr><td colspan="5" class="mudo">Nenhum login criado para este cliente.</td></tr><?php endif; ?>
    </tbody></table></div>

<?php elseif ($aba === 'projetos'): ?>
  <div class="botoes botoes--sem-margem botoes--direita"><a class="btn btn--primario" href="<?=url_pagina('admin_projeto', ['novo' => 1, 'cliente' => $id])?>"><?=icone('mais')?> Novo projeto</a></div>
  <div class="grade-3">
    <?php foreach ($projetos as $p): require __DIR__.'/_projeto_card.php'; endforeach; ?>
  </div>
  <?php if (!$projetos): ?><div class="cartao vazio"><?=icone('tela')?><p>Nenhum projeto para este cliente.</p></div><?php endif; ?>

<?php elseif ($aba === 'orcamentos'): ?>
  <div class="botoes botoes--sem-margem botoes--direita"><a class="btn btn--primario" href="<?=url_pagina('admin_orcamento', ['novo' => 1, 'cliente' => $id])?>"><?=icone('mais')?> Novo orçamento</a></div>
  <div class="cartao tabela-wrap"><table class="tabela">
    <thead><tr><th>Número</th><th>Título</th><th>Situação</th><th>Visível</th><th>Emissão</th></tr></thead><tbody>
    <?php foreach ($orcamentos as $o): ?><tr><td><a href="<?=url_pagina('admin_orcamento', ['id' => $o['id']])?>"><b><?=e($o['numero'])?></b></a></td><td><?=e($o['titulo'])?></td><td><?=selo($o['status'], status_orcamento())?></td><td><?= (int)$o['visivel_cliente'] ? 'Sim' : '<span class="mudo">Não</span>' ?></td><td><?=data_curta($o['emitido_em'])?></td></tr><?php endforeach; ?>
    <?php if (!$orcamentos): ?><tr><td colspan="5" class="mudo">Nenhum orçamento.</td></tr><?php endif; ?>
  </tbody></table></div>

<?php elseif ($aba === 'solicitacoes'): ?>
  <div class="cartao tabela-wrap"><table class="tabela">
    <thead><tr><th>Solicitação</th><th>Tipo</th><th>Situação</th><th>Aberta em</th></tr></thead><tbody>
    <?php foreach ($solicitacoes as $s): ?><tr><td><a href="<?=url_pagina('admin_solicitacao', ['id' => $s['id']])?>"><b><?=e($s['titulo'])?></b></a></td><td><?=e(tipos_solicitacao()[$s['tipo']] ?? $s['tipo'])?></td><td><?=selo($s['status'], status_solicitacao())?></td><td><?=data_hora($s['criado_em'])?></td></tr><?php endforeach; ?>
    <?php if (!$solicitacoes): ?><tr><td colspan="4" class="mudo">Nenhuma solicitação.</td></tr><?php endif; ?>
  </tbody></table></div>

<?php elseif ($aba === 'arquivos'): ?>
  <div class="botoes botoes--sem-margem botoes--direita"><a class="btn btn--primario" href="<?=url_pagina('admin_arquivos', ['cliente' => $id])?>"><?=icone('upload')?> Enviar arquivo</a></div>
  <div class="cartao tabela-wrap"><table class="tabela">
    <thead><tr><th>Arquivo</th><th>Tamanho</th><th>Visível</th><th>Enviado em</th></tr></thead><tbody>
    <?php foreach ($arquivos as $a): ?><tr><td><a href="<?=url_pagina('baixar', ['id' => $a['id']])?>"><?=icone(icone_arquivo($a['nome']))?> <?=e($a['nome'])?></a><?= $a['descricao'] ? '<br><small class="mudo">'.e($a['descricao']).'</small>' : '' ?></td><td><?=tamanho_legivel((int)$a['tamanho'])?></td><td><?= (int)$a['visivel_cliente'] ? 'Sim' : '<span class="mudo">Não</span>' ?></td><td><?=data_hora($a['criado_em'])?></td></tr><?php endforeach; ?>
    <?php if (!$arquivos): ?><tr><td colspan="4" class="mudo">Nenhum arquivo.</td></tr><?php endif; ?>
  </tbody></table></div>

<?php else: ?>
  <div class="cartao"><ul class="linha-atividade">
    <?php foreach ($atividades as $a): ?><li><span class="ponto"></span><div><b><?=e($a['nome'])?></b> · <?=e(str_replace('_', ' ', $a['acao']))?><?= $a['detalhe'] ? ' <small>('.e($a['detalhe']).')</small>' : '' ?><small class="mudo"><?=data_hora($a['criado_em'])?> · IP <?=e($a['ip'] ?? '')?></small></div></li><?php endforeach; ?>
    <?php if (!$atividades): ?><li class="mudo">Nenhum acesso registrado.</li><?php endif; ?>
  </ul></div>
<?php endif; ?>
