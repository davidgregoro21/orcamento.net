<section class="cab-pub">
  <div class="container"><span class="sobre sobre--claro">Sobre</span><h1>Tecnologia feita com cuidado, do pedido ao suporte</h1><p>A orcamento.net desenvolve sites e sistemas para empresas que querem resultado, com preço claro e acompanhamento de perto.</p></div>
</section>
<section class="pub-secao">
  <div class="container sobre-grade">
    <div class="texto-pub">
      <h2>Quem somos</h2>
      <p>Somos desenvolvedores com experiência em sistemas críticos: agendas, atendimentos, protocolos, auditoria e segurança de dados. Levamos esse mesmo cuidado para sites e sistemas de empresas de todos os portes.</p>
      <p>Cada projeto começa com uma proposta por escrito, com escopo e investimento fechados. Durante o desenvolvimento, o cliente acompanha tudo pelo nosso portal, com login próprio, e só aprova quando estiver do jeito que precisa.</p>
      <p>Depois da entrega, seguimos por perto: garantia de correções e, se o cliente quiser, manutenção mensal sem fidelidade.</p>
      <?php if (contato('responsavel')): ?><p class="assinatura"><b><?=e(contato('responsavel'))?></b><br><span class="mudo">Fundador · orcamento.net</span></p><?php endif; ?>
    </div>
    <div class="numeros-sobre">
      <div><strong>100%</strong><span>código e dados do cliente</span></div>
      <div><strong>Garantia</strong><span>de correções após a entrega</span></div>
      <div><strong>Orçamento grátis</strong><span>e sem compromisso</span></div>
      <div><strong>LGPD</strong><span>segurança em todos os projetos</span></div>
    </div>
  </div>
</section>
<!-- ============ DIFERENCIAIS ============ -->
<section class="pub-secao pub-secao--suave">
  <div class="container">
    <div class="pub-cab"><span class="sobre">Por que a orcamento.net</span><h2>Compromissos que vão para o contrato</h2></div>
    <div class="grade-3">
      <?php foreach (conteudo('diferenciais') as $df): ?>
        <div class="diferencial"><span class="ico-box ico-box--escuro"><?=icone($df['icone'])?></span><div><h3><?=e($df['titulo'])?></h3><p><?=e($df['texto'])?></p></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__.'/../partials/faixa_cta.php'; ?>
