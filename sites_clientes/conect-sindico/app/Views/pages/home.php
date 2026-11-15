<?php $artigos = array_slice(conteudo('artigos'), 0, 3); ?>
<section class="hero">
  <div class="container hero__grade">
    <div class="hero__texto">
      <span class="selo"><?=icone('local')?> <?=e(empresa('regiao'))?></span>
      <h1>Chegou a hora de <em>profissionalizar a gestão</em> do seu condomínio.</h1>
      <p class="hero__sub">Síndico profissional com presença semanal, contas transparentes e decisões dentro da lei. Menos dor de cabeça para o conselho, mais tranquilidade para quem mora.</p>
      <div class="hero__botoes">
        <a class="btn btn--laranja btn--grande" href="<?=url_pagina('contato')?>">Solicitar proposta <?=icone('seta')?></a>
        <a class="btn btn--vazado btn--grande" href="<?=e(link_whatsapp())?>" target="_blank" rel="noopener"><?=icone('whatsapp')?> Falar no WhatsApp</a>
      </div>
    </div>
    <div class="hero__arte" aria-hidden="true">
      <svg viewBox="0 0 420 380" class="skyline">
        <g fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
          <path d="M40 340V170l70-50v220M110 340V90l80-40v290M190 340V130l70 30v180M260 340V200l60-30 60 30v140"/>
          <path d="M20 340h390"/>
          <path d="M70 200h20M70 230h20M70 260h20M70 290h20M135 120h30M135 155h30M135 190h30M135 225h30M135 260h30M135 295h30M210 175h30M210 210h30M210 245h30M210 280h30M285 225h20M335 225h20M285 260h20M335 260h20M285 295h20M335 295h20"/>
        </g>
        <g class="skyline__rede" stroke="currentColor" stroke-width="2" fill="none">
          <path d="M150 50V18h60M190 50V30M260 160V60h50v-28M320 170V100h40V60M110 90V40H70"/>
          <circle cx="214" cy="18" r="5"/><circle cx="190" cy="26" r="4"/><circle cx="310" cy="28" r="5"/><circle cx="360" cy="56" r="5"/><circle cx="66" cy="40" r="5"/>
        </g>
        <path class="skyline__curva" d="M10 360c120-60 260-70 400-30" fill="none" stroke-width="10" stroke-linecap="round"/>
      </svg>
    </div>
  </div>
  <div class="container">
    <ul class="numeros">
      <?php foreach (conteudo('numeros') as $n): ?><li><strong><?=e($n['valor'])?></strong><span><?=e($n['texto'])?></span></li><?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="secao">
  <div class="container secao__dupla">
    <div>
      <span class="sobretitulo">Por que um síndico profissional?</span>
      <h2>Você já parou para pensar em todas as responsabilidades que um síndico tem?</h2>
      <p>Pelo Código Civil (art. 1.348 e seguintes), o síndico responde pelo condomínio só por ocupar o cargo, inclusive com o próprio patrimônio. Ter um profissional preparado protege o síndico, o conselho e todos os condôminos.</p>
      <a class="link-seta" href="<?=url_pagina('sobre')?>#atribuicoes">Ver as atribuições do síndico <?=icone('seta')?></a>
    </div>
    <div class="cartoes cartoes--3">
      <?php foreach (conteudo('responsabilidades') as $r): ?>
        <div class="cartao cartao--compacto"><span class="cartao__ico"><?=icone($r['icone'])?></span><h3><?=e($r['titulo'])?></h3><p><?=e($r['texto'])?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="secao secao--clara">
  <div class="container">
    <div class="secao__cab">
      <span class="sobretitulo">O que fazemos</span>
      <h2>Projetos exclusivos para cada condomínio</h2>
      <p>Mapeamos as características do prédio e montamos um plano sob medida, da rotina diária às obras de grande porte.</p>
    </div>
    <div class="cartoes cartoes--3">
      <?php foreach (conteudo('servicos') as $s): ?>
        <article class="cartao">
          <span class="cartao__ico cartao__ico--grande"><?=icone($s['icone'])?></span>
          <h3><?=e($s['titulo'])?></h3>
          <p><?=e($s['resumo'])?></p>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="centro"><a class="btn btn--azul" href="<?=url_pagina('servicos')?>">Ver todos os serviços <?=icone('seta')?></a></div>
  </div>
</section>

<section class="secao">
  <div class="container">
    <div class="secao__cab">
      <span class="sobretitulo">Como funciona</span>
      <h2>Da primeira conversa à gestão do dia a dia</h2>
    </div>
    <ol class="etapas">
      <?php foreach (conteudo('etapas') as $i => $et): ?>
        <li class="etapa"><span class="etapa__num"><?=$i + 1?></span><h3><?=e($et['titulo'])?></h3><p><?=e($et['texto'])?></p></li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="secao secao--escura">
  <div class="container secao__dupla secao__dupla--centro">
    <div class="gestor">
      <div class="gestor__foto" aria-hidden="true"><?=icone('usuario')?></div>
      <div>
        <span class="sobretitulo sobretitulo--claro">Quem cuida do seu condomínio</span>
        <h2><?=e(empresa('gestor'))?></h2>
        <p>Síndico há mais de sete anos e com mais de vinte anos em gestão e consultoria, de pequenas a grandes empresas. Formado em Direito, com MBA em Gestão Profissional de Condomínios.</p>
        <p>Conhece pessoalmente os fornecedores que indica: estrutura, equipe e qualidade do serviço. Isso faz diferença na segurança e no custo de cada contratação.</p>
        <a class="link-seta link-seta--claro" href="<?=url_pagina('sobre')?>">Conheça a trajetória <?=icone('seta')?></a>
      </div>
    </div>
    <div>
      <h3 class="titulo-lista">Com a Conect Síndico, seu condomínio terá:</h3>
      <ul class="lista-check lista-check--claro">
        <?php foreach (conteudo('beneficios') as $d): ?><li><?=icone('check')?><span><?=e($d)?></span></li><?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

<section class="secao">
  <div class="container">
    <div class="secao__cab secao__cab--linha">
      <div><span class="sobretitulo">Blog</span><h2>Informação para síndicos e condôminos</h2></div>
      <a class="link-seta" href="<?=url_pagina('blog')?>">Ver todos os artigos <?=icone('seta')?></a>
    </div>
    <div class="cartoes cartoes--3">
      <?php foreach ($artigos as $a): require __DIR__.'/../partials/cartao_artigo.php'; endforeach; ?>
    </div>
  </div>
</section>

<section class="secao secao--cta" id="proposta">
  <div class="container secao__dupla">
    <div>
      <span class="sobretitulo">Proposta sem compromisso</span>
      <h2>Vamos conversar sobre o seu condomínio?</h2>
      <p>Preencha o formulário e retornamos em até um dia útil. Se preferir, ligue ou chame no WhatsApp.</p>
      <ul class="contato-rapido">
        <li><?=icone('telefone')?><a href="<?=e(link_telefone(empresa('telefone')))?>"><?=e(empresa('telefone'))?></a></li>
        <li><?=icone('whatsapp')?><a href="<?=e(link_whatsapp())?>" target="_blank" rel="noopener"><?=e(empresa('whatsapp'))?></a></li>
      </ul>
    </div>
    <div class="painel"><?php $origemForm = 'home'; require __DIR__.'/../partials/form_contato.php'; ?></div>
  </div>
</section>
