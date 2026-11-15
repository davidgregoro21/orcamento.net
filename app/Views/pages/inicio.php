<?php
$temImg = fn(string $f) => $f !== '' && is_file(__DIR__.'/../../../public/assets/img/'.$f);
$destaques = db()->query('SELECT * FROM sistemas WHERE ativo = 1 ORDER BY destaque DESC, ordem, nome LIMIT 3')->fetchAll();
?>
<!-- ============ TOPO ============ -->
<section class="lp-hero">
  <div class="lp-hero__brilho" aria-hidden="true"></div>
  <div class="container lp-hero__grade">
    <div class="lp-hero__texto">
      <span class="pilula"><span class="ponto-vivo"></span> Agenda aberta para novos projetos</span>
      <h1>Seu negócio merece mais do que um site. <em>Merece uma solução digital.</em></h1>
      <p class="lp-hero__sub">Do primeiro site à gestão completa da sua empresa. Desenvolvemos ferramentas digitais para simplificar, organizar e automatizar o seu negócio, com proposta clara e acompanhamento em uma área exclusiva.</p>
      <div class="botoes">
        <a class="btn btn--primario btn--g" href="<?=url_pagina('contato')?>">Quero meu orçamento grátis <?=icone('seta')?></a>
        <a class="btn btn--vazado btn--g" href="<?=url_pagina('portfolio')?>">Ver o portfólio</a>
      </div>
      <ul class="lp-hero__checks">
        <li><?=icone('check')?> Resposta em 1 dia útil</li>
        <li><?=icone('check')?> Proposta por escrito</li>
        <li><?=icone('check')?> 90 dias de garantia</li>
      </ul>
    </div>
    <div class="lp-hero__visual" aria-hidden="true">
      <div class="navegador navegador--hero">
        <div class="navegador__barra"><i></i><i></i><i></i><span>painel · sua-empresa.com.br</span></div>
        <div class="tela-modelo tela-modelo--hero">
          <div class="tela-modelo__lateral"><span class="ativo"></span><span></span><span></span><span></span><span></span><span></span></div>
          <div class="tela-modelo__corpo">
            <div class="tela-modelo__titulo"><?=icone('sistema')?> Painel administrativo</div>
            <div class="tela-modelo__cards"><b></b><b></b><b></b></div>
            <div class="tela-modelo__tabela">
              <div class="tela-modelo__cab"><span>Usuários</span><em><?=icone('mais')?> Novo usuário</em></div>
              <div class="tela-modelo__linha"><i class="av"></i><i></i><u class="ok">Ativo</u></div>
              <div class="tela-modelo__linha"><i class="av"></i><i></i><u class="ok">Ativo</u></div>
              <div class="tela-modelo__linha"><i class="av"></i><i></i><u>Convite</u></div>
            </div>
          </div>
        </div>
      </div>
      <div class="flutua flutua--proposta">
        <div class="flutua__cab"><span>Proposta</span><b>aprovada</b></div>
        <div class="flutua__linha"><span>Escopo</span><b>fechado</b></div>
        <div class="flutua__linha"><span>Prazo</span><b>no contrato</b></div>
      </div>
      <div class="flutua flutua--contato"><span class="flutua__ico"><?=icone('usuario')?></span><div><b>Novo usuário cadastrado</b><small>perfil: cliente</small></div></div>
      <div class="flutua flutua--seguro"><?=icone('portal')?> HTTPS · LGPD · Backup</div>
    </div>
  </div>
  <div class="container">
    <ul class="lp-faixa">
      <?php foreach (conteudo('garantias_topo') as $g): ?><li><strong><?=e($g['valor'])?></strong><span><?=e($g['texto'])?></span></li><?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- ============ PORTFÓLIO DE SERVIÇOS ============ -->
<section class="pub-secao" id="portfolio">
  <div class="container">
    <div class="pub-cab"><span class="sobre">Portfólio de serviços</span><h2>Sites, sistemas e automações para qualquer tipo de negócio</h2><p>Do primeiro site à empresa funcionando no automático. Escolha uma área para ver tudo o que fazemos.</p></div>
    <div class="pf-areas">
      <?php foreach (conteudo('portfolio') as $g): ?>
        <a class="pf-area" href="<?=url_pagina('portfolio')?>#<?=e($g['id'])?>">
          <span class="ico-box"><?=icone($g['icone'])?></span>
          <h3><?=e($g['titulo'])?></h3>
          <p><?=e($g['texto'])?></p>
          <ul class="mini-lista"><?php foreach (array_slice($g['itens'], 0, 4) as $it): ?><li><?=icone('check')?> <?=e($it['titulo'])?></li><?php endforeach; ?></ul>
          <span class="link-seta">Ver <?=count($g['itens'])?> serviços <?=icone('seta')?></span>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if ($trabalhos = projetos_portfolio(3)): ?>
      <div class="pub-cab pf-cab-trabalhos"><span class="sobre">Trabalhos realizados</span><h2>Alguns projetos entregues</h2></div>
      <div class="pf-trabalhos pf-trabalhos--claro"><?php foreach ($trabalhos as $t) require __DIR__.'/../partials/cartao_trabalho.php'; ?></div>
    <?php endif; ?>
    <div class="centro botoes botoes--centro"><a class="btn btn--primario" href="<?=url_pagina('portfolio')?>">Ver o portfólio completo <?=icone('seta')?></a></div>
  </div>
</section>

<!-- ============ PARA QUEM ============ -->
<section class="lp-segmentos">
  <div class="container">
    <p class="lp-segmentos__titulo">Projetos para empresas de todos os portes</p>
    <ul>
      <?php foreach (conteudo('segmentos') as $sg): ?><li><?=icone($sg['icone'])?><span><?=e($sg['titulo'])?></span></li><?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- ============ SISTEMAS EM DESTAQUE ============ -->
<section class="pub-secao pub-secao--suave" id="sistemas">
  <div class="container">
    <div class="pub-cab"><span class="sobre">Soluções prontas</span><h2>Comece por uma base testada e adapte ao seu negócio</h2><p>Soluções testadas que personalizamos com a sua marca e as suas regras. Cada cliente acessa o seu sistema com login próprio.</p></div>
    <div class="grade-3">
      <?php foreach ($destaques as $si) require __DIR__.'/../partials/cartao_sistema.php'; ?>
    </div>
    <div class="centro botoes botoes--centro"><a class="btn btn--primario" href="<?=url_pagina('sistemas')?>">Ver todas as soluções <?=icone('seta')?></a></div>
  </div>
</section>
<!-- ============ PROBLEMA → SOLUÇÃO ============ -->
<section class="pub-secao">
  <div class="container">
    <div class="pub-cab"><span class="sobre">Isso acontece com você?</span><h2>Tecnologia deveria ajudar, não atrapalhar</h2><p>Os problemas mais comuns que resolvemos, e como resolvemos cada um.</p></div>
    <div class="lp-problemas">
      <?php foreach (conteudo('problemas') as $pr): ?>
        <div class="lp-problema">
          <div class="lp-problema__dor"><span class="marca-x" aria-hidden="true">×</span><p><?=e($pr['dor'])?></p></div>
          <div class="lp-problema__seta" aria-hidden="true"><?=icone('seta')?></div>
          <div class="lp-problema__solucao"><span class="marca-ok" aria-hidden="true"><?=icone('check')?></span><p><?=e($pr['solucao'])?></p></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ MÓDULOS ============ -->
<section class="pub-secao" id="modulos">
  <div class="container">
    <div class="pub-cab"><span class="sobre">Recursos</span><h2>Tudo o que um sistema profissional precisa</h2><p>Escolha os módulos do seu projeto. Todos funcionam no computador e no celular.</p></div>
    <div class="modulos">
      <?php foreach (conteudo('modulos') as $md): ?><div class="modulo"><span class="ico-box"><?=icone($md['icone'])?></span><h3><?=e($md['titulo'])?></h3><p><?=e($md['texto'])?></p></div><?php endforeach; ?>
    </div>
    <div class="centro botoes botoes--centro"><a class="btn btn--primario" href="<?=url_pagina('servicos')?>#pacotes">Ver as opções de escolha <?=icone('seta')?></a></div>
  </div>
</section>

<!-- ============ COMO FUNCIONA + PORTAL ============ -->
<section class="pub-secao" id="como-funciona">
  <div class="container">
    <div class="pub-cab"><span class="sobre">Como funciona</span><h2>Do pedido ao site no ar em 5 passos</h2></div>
    <ol class="linha-tempo">
      <?php foreach (conteudo('etapas') as $i => $et): ?>
        <li><span class="linha-tempo__ico"><?=icone($et['icone'])?></span><b class="linha-tempo__num">Passo <?=$i + 1?></b><h3><?=e($et['titulo'])?></h3><p><?=e($et['texto'])?></p></li>
      <?php endforeach; ?>
    </ol>
    <div class="lp-portal">
      <div class="lp-portal__texto">
        <span class="sobre">Exclusivo orcamento.net</span>
        <h2>Cada cliente tem uma área exclusiva</h2>
        <p>Com login próprio, o cliente acompanha o projeto, visualiza o site ou sistema antes de ir ao ar, consulta e aprova orçamentos, pede alterações e baixa arquivos.</p>
        <ul class="mini-lista mini-lista--grande">
          <li><?=icone('check')?> Acesso exclusivo, protegido por senha</li>
          <li><?=icone('check')?> Prévia funcionando no celular e no computador</li>
          <li><?=icone('check')?> Ajustes até você aprovar</li>
        </ul>
        <a class="btn btn--primario" href="<?=url_pagina('login')?>"><?=icone('portal')?> Já sou cliente: entrar</a>
      </div>
      <div class="lp-portal__visual" aria-hidden="true">
        <div class="navegador"><div class="navegador__barra"><i></i><i></i><i></i><span>orcamento.net/portal</span></div>
          <div class="portal-modelo">
            <div class="portal-modelo__ola">Olá, cliente!</div>
            <div class="portal-modelo__vitrine">
              <div><span class="portal-modelo__selo">Prévia exclusiva</span><b>Seu sistema está pronto para você ver.</b><em><?=icone('olho')?> Abrir</em></div>
              <div class="portal-modelo__tela"><i></i><i></i><i></i></div>
            </div>
            <div class="portal-modelo__lista"><span><?=icone('site')?> Site</span><span><?=icone('sistema')?> Sistema</span><span><?=icone('documento')?> Proposta</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ PLANOS ============ -->
<section class="pub-secao pub-secao--escura" id="planos">
  <div class="container">
    <div class="pub-cab pub-cab--claro"><span class="sobre">Planos e soluções</span><h2>Escolha por onde começar</h2><p>Os planos são pontos de partida: também criamos projetos totalmente personalizados.</p></div>
    <div class="pacotes">
      <?php foreach (conteudo('pacotes') as $pc): ?>
        <article class="pacote<?= $pc['destaque'] ? ' pacote--destaque' : '' ?>">
          <?php if ($pc['destaque']): ?><span class="pacote__selo">Mais procurado</span><?php endif; ?>
          <h3><?=e($pc['nome'])?></h3><p class="pacote__para"><?=e($pc['para'])?></p>
          <ul><?php foreach ($pc['itens'] as $it): ?><li><?=icone('check')?> <?=e($it)?></li><?php endforeach; ?></ul>
          <a class="btn <?= $pc['destaque'] ? 'btn--primario' : 'btn--linha' ?> btn--bloco" href="<?=url_pagina('contato', ['servico' => $pc['nome']])?>">Quero o <?=e($pc['nome'])?></a>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="centro botoes botoes--centro"><a class="btn btn--vazado" href="<?=url_pagina('contato', ['servico' => 'Projeto personalizado'])?>">Preciso de um projeto personalizado <?=icone('seta')?></a></div>
  </div>
</section>

<!-- ============ SERVIÇOS RESUMO ============ -->
<section class="pub-secao">
  <div class="container">
    <div class="pub-cab"><span class="sobre">Serviços</span><h2>Também fazemos sob medida</h2></div>
    <div class="servicos-resumo">
      <?php foreach (conteudo('servicos') as $s): ?><a class="servico-mini" href="<?=url_pagina('servicos')?>"><span class="ico-box"><?=icone($s['icone'])?></span><span><b><?=e($s['titulo'])?></b><small><?= $s['a_partir'] !== '' ? 'a partir de '.e($s['a_partir']) : e($s['texto']) ?></small></span></a><?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__.'/../partials/faixa_cta.php'; ?>
