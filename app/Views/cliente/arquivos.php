<?php
$st = db()->prepare('SELECT a.*, p.nome AS projeto FROM arquivos a LEFT JOIN projetos p ON p.id = a.projeto_id WHERE a.cliente_id = ? AND a.visivel_cliente = 1 ORDER BY a.criado_em DESC');
$st->execute([cliente_atual()]);
$lista = $st->fetchAll();
?>
<div class="pagina-cab"><div><h1>Meus arquivos</h1><p>Documentos, imagens, propostas e materiais do seu projeto.</p></div></div>
<div class="arquivos">
  <?php foreach ($lista as $a): ?>
    <a class="arquivo" href="<?=url_pagina('baixar', ['id' => $a['id']])?>">
      <span class="arquivo__ico"><?=icone(icone_arquivo($a['nome']))?></span>
      <div><b><?=e($a['nome'])?></b><small class="mudo"><?=e(implode(' · ', array_filter([$a['descricao'], $a['projeto'], tamanho_legivel((int)$a['tamanho']), data_curta($a['criado_em'])])))?></small></div>
      <span class="arquivo__baixar"><?=icone('download')?></span>
    </a>
  <?php endforeach; ?>
  <?php if (!$lista): ?><div class="cartao vazio"><?=icone('documento')?><p>Nenhum arquivo disponível ainda.</p></div><?php endif; ?>
</div>
