<?php
$buscar = function (int $id): ?array { $st = db()->prepare('SELECT * FROM sistemas WHERE id = ?'); $st->execute([$id]); return $st->fetch() ?: null; };
$icones = ['sistema' => 'Painel', 'predio' => 'Prédio', 'calendario' => 'Agenda', 'documento' => 'Documento', 'portal' => 'Cadeado', 'caixa' => 'Caixa de entrada', 'ferramenta' => 'Ferramenta', 'pessoas' => 'Pessoas', 'grafico' => 'Gráfico', 'site' => 'Site', 'nuvem' => 'Nuvem', 'engrenagem' => 'Engrenagem', 'escudo' => 'Escudo', 'balanca' => 'Balança'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)post_text('id');
    if (post_text('acao') === 'limpar_valores') {
        // Deixa todos os sistemas "Sob consulta" (sem implantação/mensalidade) e tira imagens de clientes do catálogo público.
        db()->exec("UPDATE sistemas SET preco = NULL, mensalidade = NULL");
        db()->exec("UPDATE sistemas SET imagem = NULL WHERE imagem = 'previa-conect-sindico.jpg'");
        audit('catalogo_valores_limpos');
        flash('sucesso', 'Todos os sistemas agora aparecem como "Sob consulta".');
        redirect('admin_catalogo');
    }
    if (post_text('acao') === 'alternar') {
        $s = $buscar($id); if ($s) { db()->prepare('UPDATE sistemas SET ativo = ? WHERE id = ?')->execute([(int)$s['ativo'] ? 0 : 1, $id]); audit('sistema_alternado', $s['slug']); }
        redirect('admin_catalogo');
    }
    $d = [
        'nome' => mb_substr(post_text('nome'), 0, 120),
        'slug' => trim(preg_replace('/[^a-z0-9]+/', '-', strtr(mb_strtolower(post_text('slug') ?: post_text('nome')), ['á'=>'a','à'=>'a','â'=>'a','ã'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c'])), '-'),
        'categoria' => mb_substr(post_text('categoria'), 0, 60) ?: 'Outros',
        'icone' => array_key_exists(post_text('icone'), $icones) ? post_text('icone') : 'sistema',
        'resumo' => mb_substr(post_text('resumo'), 0, 255), 'descricao' => mb_substr(post_text('descricao'), 0, 4000),
        'para_quem' => mb_substr(post_text('para_quem'), 0, 255), 'recursos' => mb_substr(str_replace("\r", '', post_text('recursos')), 0, 4000),
        'preco' => mb_substr(post_text('preco'), 0, 60), 'mensalidade' => mb_substr(post_text('mensalidade'), 0, 60), 'prazo' => mb_substr(post_text('prazo'), 0, 60),
        'imagem' => basename(post_text('imagem')), 'destaque' => post_text('destaque') === '1' ? 1 : 0, 'ordem' => (int)post_text('ordem'),
    ];
    $erros = [];
    if (mb_strlen($d['nome']) < 3) $erros[] = 'informe o nome';
    if (mb_strlen($d['resumo']) < 10) $erros[] = 'escreva um resumo';
    $dup = db()->prepare('SELECT id FROM sistemas WHERE slug = ? AND id <> ?'); $dup->execute([$d['slug'], $id]);
    if ($dup->fetch()) $erros[] = 'já existe um sistema com esse endereço';
    if ($erros) { flash('erro', 'Confira: '.implode(', ', $erros).'.'); redirect('admin_catalogo', $id ? ['editar' => $id] : ['novo' => 1]); }
    $campos = ['slug', 'nome', 'categoria', 'icone', 'resumo', 'descricao', 'para_quem', 'recursos', 'preco', 'mensalidade', 'prazo', 'imagem', 'destaque', 'ordem'];
    $vals = array_map(fn($c) => is_string($d[$c]) && $d[$c] === '' && !in_array($c, ['slug', 'nome', 'categoria', 'icone', 'resumo'], true) ? null : $d[$c], $campos);
    if ($id) {
        db()->prepare('UPDATE sistemas SET '.implode(', ', array_map(fn($c) => "$c = ?", $campos)).' WHERE id = ?')->execute([...$vals, $id]);
        audit('sistema_editado', $d['slug']);
    } else {
        db()->prepare('INSERT INTO sistemas ('.implode(', ', $campos).', ativo, criado_em) VALUES ('.implode(', ', array_fill(0, count($campos), '?')).', 1, ?)')->execute([...$vals, agora()]);
        audit('sistema_criado', $d['slug']);
    }
    flash('sucesso', 'Sistema salvo no catálogo.');
    redirect('admin_catalogo');
}
$editar = isset($_GET['editar']) ? $buscar((int)$_GET['editar']) : null;
$novo = isset($_GET['novo']);
$lista = db()->query('SELECT * FROM sistemas ORDER BY ativo DESC, destaque DESC, ordem, nome')->fetchAll();
$val = fn(string $k) => e((string)($editar[$k] ?? ''));
?>
<div class="pagina-cab">
  <div><h1>Catálogo de soluções</h1><p>Sistemas à venda que aparecem na página pública <a href="<?=url_pagina('sistemas')?>" target="_blank" rel="noopener">/sistemas</a>.</p></div>
  <?php if (!$novo && !$editar): ?><div class="botoes" style="margin:0">
    <form method="post" action="<?=url_pagina('admin_catalogo')?>" data-confirmar="Apagar os valores de implantação e mensalidade de TODOS os sistemas do catálogo?"><?=csrf_field()?><input type="hidden" name="acao" value="limpar_valores"><button class="btn btn--linha">Deixar todos sob consulta</button></form>
    <a class="btn btn--primario" href="<?=url_pagina('admin_catalogo', ['novo' => 1])?>"><?=icone('mais')?> Novo sistema</a>
  </div><?php endif; ?>
</div>

<?php if ($novo || $editar): ?>
  <form class="cartao form" method="post" action="<?=url_pagina('admin_catalogo')?>">
    <?=csrf_field()?><input type="hidden" name="id" value="<?=(int)($editar['id'] ?? 0)?>">
    <h2 class="cartao__titulo"><?= $editar ? 'Editar '.e($editar['nome']) : 'Novo sistema' ?></h2>
    <div class="form__grade">
      <label class="campo"><span>Nome *</span><input name="nome" required value="<?=$val('nome')?>"></label>
      <label class="campo"><span>Endereço (slug)</span><input name="slug" placeholder="gerado a partir do nome" value="<?=$val('slug')?>"></label>
      <label class="campo"><span>Categoria</span><input name="categoria" list="cats" value="<?=$val('categoria')?>"><datalist id="cats"><?php foreach (conteudo('categorias_sistema') as $c): ?><option value="<?=e($c)?>"><?php endforeach; ?></datalist></label>
      <label class="campo"><span>Ícone</span><select name="icone"><?php foreach ($icones as $k => $r): ?><option value="<?=$k?>"<?= ($editar['icone'] ?? 'sistema') === $k ? ' selected' : '' ?>><?=e($r)?></option><?php endforeach; ?></select></label>
      <label class="campo campo--inteiro"><span>Resumo (aparece no cartão) *</span><input name="resumo" maxlength="255" required value="<?=$val('resumo')?>"></label>
      <label class="campo campo--inteiro"><span>Descrição</span><textarea name="descricao" rows="3"><?=$val('descricao')?></textarea></label>
      <label class="campo campo--inteiro"><span>Para quem é</span><input name="para_quem" maxlength="255" value="<?=$val('para_quem')?>"></label>
      <label class="campo campo--inteiro"><span>Recursos (um por linha)</span><textarea name="recursos" rows="6"><?=$val('recursos')?></textarea></label>
      <label class="campo"><span>Implantação a partir de</span><input name="preco" placeholder="R$ 9.500" value="<?=$val('preco')?>"></label>
      <label class="campo"><span>Mensalidade</span><input name="mensalidade" placeholder="R$ 290/mês" value="<?=$val('mensalidade')?>"></label>
      <label class="campo"><span>Prazo</span><input name="prazo" placeholder="30 a 45 dias" value="<?=$val('prazo')?>"></label>
      <label class="campo"><span>Imagem (arquivo em public/assets/img)</span><input name="imagem" value="<?=$val('imagem')?>"></label>
      <label class="campo"><span>Ordem</span><input type="number" name="ordem" value="<?=(int)($editar['ordem'] ?? 0)?>"></label>
      <label class="opcao"><input type="checkbox" name="destaque" value="1"<?= !empty($editar['destaque']) ? ' checked' : '' ?>> <span>Destaque na página inicial</span></label>
    </div>
    <div class="botoes"><button class="btn btn--primario" type="submit">Salvar</button><a class="btn btn--linha" href="<?=url_pagina('admin_catalogo')?>">Cancelar</a></div>
  </form>
<?php endif; ?>

<div class="cartao tabela-wrap">
  <table class="tabela">
    <thead><tr><th>Sistema</th><th>Categoria</th><th>A partir de</th><th>Mensalidade</th><th>Situação</th><th class="direita">Ações</th></tr></thead>
    <tbody>
    <?php foreach ($lista as $s): ?>
      <tr class="<?= (int)$s['ativo'] ? '' : 'inativo' ?>">
        <td><b><?=e($s['nome'])?></b><?= (int)$s['destaque'] ? ' <span class="selo selo--admin">Destaque</span>' : '' ?><br><small class="mudo">/sistema?s=<?=e($s['slug'])?></small></td>
        <td><?=e($s['categoria'])?></td>
        <td><?= $s['preco'] ? e($s['preco']) : '<span class="mudo">sob consulta</span>' ?></td>
        <td><?= $s['mensalidade'] ? e($s['mensalidade']) : '<span class="mudo">—</span>' ?></td>
        <td><span class="selo selo--<?= (int)$s['ativo'] ? 'ok' : 'off' ?>"><?= (int)$s['ativo'] ? 'Publicado' : 'Oculto' ?></span></td>
        <td class="direita acoes">
          <a class="btn btn--linha btn--p" href="<?=url_pagina('sistema', ['s' => $s['slug']])?>" target="_blank" rel="noopener">Ver</a>
          <a class="btn btn--linha btn--p" href="<?=url_pagina('admin_catalogo', ['editar' => $s['id']])?>">Editar</a>
          <form method="post" action="<?=url_pagina('admin_catalogo')?>"><?=csrf_field()?><input type="hidden" name="acao" value="alternar"><input type="hidden" name="id" value="<?=(int)$s['id']?>"><button class="btn btn--linha btn--p"><?= (int)$s['ativo'] ? 'Ocultar' : 'Publicar' ?></button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$lista): ?><tr><td colspan="6" class="mudo">Nenhum sistema no catálogo.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
