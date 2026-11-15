<?php
$id = (int)($_GET['id'] ?? 0);
$buscar = function (int $id): ?array { $st = db()->prepare('SELECT p.*, c.nome AS cliente_nome FROM projetos p LEFT JOIN clientes c ON c.id = p.cliente_id WHERE p.id = ?'); $st->execute([$id]); return $st->fetch() ?: null; };
$p = $id ? $buscar($id) : null;
if ($id && !$p) { flash('erro', 'Projeto não encontrado.'); redirect('admin_projetos'); }
$clientes = db()->query('SELECT id, nome FROM clientes ORDER BY nome')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $acao = post_text('acao');
    if ($acao === 'salvar') {
        $d = [
            'cliente_id' => (int)post_text('cliente_id') ?: null, 'nome' => mb_substr(post_text('nome'), 0, 120),
            'slug' => trim(preg_replace('/[^a-z0-9]+/', '-', strtr(mb_strtolower(post_text('slug') ?: post_text('nome')), ['á'=>'a','à'=>'a','â'=>'a','ã'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c'])), '-'),
            'tipo' => mb_substr(post_text('tipo'), 0, 60), 'situacao' => array_key_exists(post_text('situacao'), situacoes_projeto()) ? post_text('situacao') : 'desenvolvimento',
            'chamada' => mb_substr(post_text('chamada'), 0, 200), 'descricao' => mb_substr(post_text('descricao'), 0, 5000),
            'funcionalidades' => mb_substr(post_text('funcionalidades'), 0, 5000), 'futuras' => mb_substr(post_text('futuras'), 0, 5000),
            'url_previa' => post_text('url_previa'), 'url_publicacao' => post_text('url_publicacao'), 'url_proposta' => post_text('url_proposta'),
            'permitir_embed' => post_text('permitir_embed') === '1' ? 1 : 0, 'ativo' => post_text('ativo') === '1' ? 1 : 0, 'ordem' => (int)post_text('ordem'),
        ];
        // url_previa aceita também um site hospedado no próprio portal: sites/nome-do-site/
        if (preg_match('#^/?sites/[a-z0-9-]+/?$#', $d['url_previa'])) $d['url_previa'] = rtrim(ltrim($d['url_previa'], '/'), '/').'/';
        foreach (['url_previa', 'url_publicacao', 'url_proposta'] as $k) if ($d[$k] !== '' && !preg_match('#^https?://#i', $d[$k]) && !($k === 'url_previa' && str_starts_with($d[$k], 'sites/'))) $d[$k] = 'https://'.$d[$k];
        $erros = [];
        if (!$d['cliente_id']) $erros[] = 'escolha o cliente';
        if (mb_strlen($d['nome']) < 2) $erros[] = 'informe o nome';
        if (!str_starts_with($d['url_previa'], 'sites/') && !url_valida($d['url_previa'])) $erros[] = 'endereço de demonstração inválido';
        foreach (['url_publicacao', 'url_proposta'] as $k) if ($d[$k] !== '' && !url_valida($d[$k])) $erros[] = 'link inválido em '.str_replace('url_', '', $k);
        $dup = db()->prepare('SELECT id FROM projetos WHERE slug = ? AND id <> ?'); $dup->execute([$d['slug'], $id]);
        if ($dup->fetch()) $erros[] = 'já existe um projeto com esse identificador';
        if ($erros) { flash('erro', 'Confira: '.implode(', ', $erros).'.'); redirect('admin_projeto', $id ? ['id' => $id] : ['novo' => 1]); }
        try { $img = guardar_imagem($_FILES['imagem'] ?? [], 'projetos'); } catch (RuntimeException $e) { flash('erro', $e->getMessage()); redirect('admin_projeto', $id ? ['id' => $id] : ['novo' => 1]); }
        $campos = array_keys($d);
        $vals = array_map(fn($k) => $d[$k] === '' ? null : $d[$k], $campos);
        if ($id) {
            db()->prepare('UPDATE projetos SET '.implode(', ', array_map(fn($k) => "$k = ?", $campos)).', atualizado_em = ?'.($img ? ', imagem = ?' : '').' WHERE id = ?')->execute([...$vals, agora(), ...($img ? [$img] : []), $id]);
            audit('projeto_editado', $d['slug']);
        } else {
            db()->prepare('INSERT INTO projetos ('.implode(', ', $campos).', imagem, criado_em, atualizado_em) VALUES ('.implode(', ', array_fill(0, count($campos), '?')).', ?, ?, ?)')->execute([...$vals, $img, agora(), agora()]);
            $id = (int)db()->lastInsertId();
            audit('projeto_criado', $d['slug']);
        }
        flash('sucesso', 'Projeto salvo.');
        redirect('admin_projeto', ['id' => $id]);
    }
    if (!$p) redirect('admin_projetos');
    if ($acao === 'atualizacao') {
        $titulo = mb_substr(post_text('titulo'), 0, 160); $url = post_text('url');
        if ($url !== '' && !preg_match('#^https?://#i', $url)) $url = 'https://'.$url;
        if (mb_strlen($titulo) < 3 || ($url !== '' && !url_valida($url))) { flash('erro', 'Informe o título da atualização (e um link válido, se houver).'); redirect('admin_projeto', ['id' => $id]); }
        db()->prepare('INSERT INTO projeto_atualizacoes (projeto_id, versao, titulo, descricao, url, visivel_cliente, criado_em) VALUES (?,?,?,?,?,?,?)')
            ->execute([$id, mb_substr(post_text('versao'), 0, 30) ?: null, $titulo, mb_substr(post_text('descricao'), 0, 3000) ?: null, $url ?: null, post_text('visivel') === '1' ? 1 : 0, agora()]);
        db()->prepare('UPDATE projetos SET atualizado_em = ? WHERE id = ?')->execute([agora(), $id]);
        audit('projeto_atualizacao', $p['slug'].': '.$titulo);
        flash('sucesso', 'Atualização registrada no histórico do projeto.');
    }
    if ($acao === 'excluir_atualizacao') {
        db()->prepare('DELETE FROM projeto_atualizacoes WHERE id = ? AND projeto_id = ?')->execute([(int)post_text('atualizacao_id'), $id]);
        flash('sucesso', 'Atualização removida.');
    }
    redirect('admin_projeto', ['id' => $id]);
}
$preCliente = (int)($_GET['cliente'] ?? 0);
$v = fn(string $k, string $padrao = '') => e((string)($p[$k] ?? $padrao));
$atualizacoes = [];
if ($p) { $tituloPagina = $p['nome']; $st = db()->prepare('SELECT * FROM projeto_atualizacoes WHERE projeto_id = ? ORDER BY criado_em DESC, id DESC'); $st->execute([$id]); $atualizacoes = $st->fetchAll(); }
$img = $p ? url_imagem($p['imagem']) : null;
?>
<div class="pagina-cab">
  <div><h1><?= $p ? e($p['nome']) : 'Novo projeto' ?></h1><p><?= $p ? e($p['cliente_nome'] ?? 'Sem cliente').' · criado em '.data_curta($p['criado_em']) : 'Cadastre o site ou sistema e o endereço de demonstração.' ?></p></div>
  <?php if ($p): ?><div class="botoes botoes--sem-margem"><a class="btn btn--linha" href="<?=url_pagina('visualizar', ['id' => $id])?>"><?=icone('olho')?> Ver como o cliente</a><a class="btn btn--linha" href="<?=url_pagina('admin_cliente', ['id' => $p['cliente_id'], 'aba' => 'usuarios'])?>"><?=icone('pessoas')?> Logins do cliente</a></div><?php endif; ?>
</div>

<div class="grade-form">
<form class="cartao form" method="post" enctype="multipart/form-data" action="<?=url_pagina('admin_projeto', $p ? ['id' => $id] : [])?>">
  <?=csrf_field()?><input type="hidden" name="acao" value="salvar">
  <div class="form__grade">
    <label class="campo"><span>Cliente *</span><select name="cliente_id" required><option value="">Escolha...</option><?php foreach ($clientes as $cl): ?><option value="<?=$cl['id']?>"<?= (int)($p['cliente_id'] ?? $preCliente) === (int)$cl['id'] ? ' selected' : '' ?>><?=e($cl['nome'])?></option><?php endforeach; ?></select></label>
    <label class="campo"><span>Nome do projeto *</span><input name="nome" required value="<?=$v('nome')?>"></label>
    <label class="campo"><span>Tipo</span><input name="tipo" list="tipos-p" value="<?=$v('tipo')?>"><datalist id="tipos-p"><option value="Site institucional"><option value="Site comercial"><option value="Sistema administrativo"><option value="Portal com login"><option value="Dashboard"><option value="Automação"></datalist></label>
    <label class="campo"><span>Situação</span><select name="situacao"><?php foreach (situacoes_projeto() as $k => $r): ?><option value="<?=$k?>"<?= ($p['situacao'] ?? 'desenvolvimento') === $k ? ' selected' : '' ?>><?=e($r)?></option><?php endforeach; ?></select></label>
    <label class="campo campo--inteiro"><span>Chamada (título que o cliente vê)</span><input name="chamada" maxlength="200" value="<?=$v('chamada')?>" placeholder="Ex.: O novo site da sua empresa está pronto para você ver."></label>
    <label class="campo campo--inteiro"><span>Descrição</span><textarea name="descricao" rows="3"><?=$v('descricao')?></textarea></label>
    <label class="campo"><span>Funcionalidades implementadas (uma por linha)</span><textarea name="funcionalidades" rows="7"><?=$v('funcionalidades')?></textarea></label>
    <label class="campo"><span>Possibilidades de expansão (uma por linha)</span><textarea name="futuras" rows="7"><?=$v('futuras')?></textarea><small>Aparecem para o cliente como "Próximos passos possíveis".</small></label>
    <label class="campo"><span>Endereço de demonstração *</span><input name="url_previa" required placeholder="sites/conect-sindico/ ou https://..." value="<?=$v('url_previa')?>"><small>Site dentro do portal: <code>sites/nome-do-site/</code>. Também aceita um endereço externo (https://...). Aberto com link assinado; o cliente não precisa de outra senha.</small></label>
    <label class="campo"><span>Endereço publicado (definitivo)</span><input name="url_publicacao" placeholder="https://" value="<?=$v('url_publicacao')?>"></label>
    <label class="campo"><span>Link da proposta (opcional)</span><input name="url_proposta" placeholder="https://" value="<?=$v('url_proposta')?>"></label>
    <label class="campo"><span>Imagem de apresentação</span><input type="file" name="imagem" accept="image/png,image/jpeg,image/webp"><?php if ($img): ?><small>Atual: <a href="<?=$img?>" target="_blank" rel="noopener">ver imagem</a></small><?php endif; ?></label>
    <label class="campo"><span>Ordem</span><input type="number" name="ordem" value="<?=(int)($p['ordem'] ?? 0)?>"></label>
    <div class="campo"><span>Opções</span>
      <label class="opcao"><input type="checkbox" name="ativo" value="1"<?= (int)($p['ativo'] ?? 1) ? ' checked' : '' ?>> Liberado para o cliente ver</label>
      <label class="opcao"><input type="checkbox" name="permitir_embed" value="1"<?= (int)($p['permitir_embed'] ?? 1) ? ' checked' : '' ?>> Mostrar o site dentro do portal (iframe)</label>
    </div>
  </div>
  <div class="botoes"><button class="btn btn--primario">Salvar projeto</button><a class="btn btn--linha" href="<?=url_pagina('admin_projetos')?>">Voltar</a></div>
</form>

<?php if ($p): ?>
<aside>
  <section class="cartao">
    <h2 class="cartao__titulo">Registrar versão / atualização</h2>
    <form class="form" method="post" action="<?=url_pagina('admin_projeto', ['id' => $id])?>"><?=csrf_field()?><input type="hidden" name="acao" value="atualizacao">
      <div class="form__grade">
        <label class="campo"><span>Versão</span><input name="versao" placeholder="1.1"></label>
        <label class="campo"><span>Título *</span><input name="titulo" required placeholder="Ex.: Ajustes na página de serviços"></label>
        <label class="campo campo--inteiro"><span>O que mudou</span><textarea name="descricao" rows="3"></textarea></label>
        <label class="campo campo--inteiro"><span>Link desta versão (opcional)</span><input name="url" placeholder="https://"></label>
      </div>
      <label class="opcao"><input type="checkbox" name="visivel" value="1" checked> Mostrar para o cliente</label>
      <button class="btn btn--primario btn--bloco">Registrar</button>
    </form>
  </section>
  <section class="cartao">
    <h2 class="cartao__titulo">Histórico do projeto</h2>
    <ul class="linha-tempo-v">
      <?php foreach ($atualizacoes as $a): ?>
        <li><b><?= $a['versao'] ? 'v'.e($a['versao']).' · ' : '' ?><?=e($a['titulo'])?></b><?= (int)$a['visivel_cliente'] ? '' : ' <span class="selo selo--off">interno</span>' ?><small class="mudo"><?=data_hora($a['criado_em'])?></small><?php if ($a['descricao']): ?><p><?=nl2br(e($a['descricao']))?></p><?php endif; ?><?php if ($a['url']): ?><a href="<?=e($a['url'])?>" target="_blank" rel="noopener"><?=e($a['url'])?></a><?php endif; ?>
          <form method="post" action="<?=url_pagina('admin_projeto', ['id' => $id])?>" data-confirmar="Remover esta atualização do histórico?"><?=csrf_field()?><input type="hidden" name="acao" value="excluir_atualizacao"><input type="hidden" name="atualizacao_id" value="<?=(int)$a['id']?>"><button class="link-perigo link-perigo--p">remover</button></form></li>
      <?php endforeach; ?>
      <?php if (!$atualizacoes): ?><li class="mudo">Nenhuma atualização registrada.</li><?php endif; ?>
    </ul>
  </section>
</aside>
<?php endif; ?>
</div>
