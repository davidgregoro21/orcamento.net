<section class="cab-pagina">
  <div class="container">
    <span class="sobretitulo sobretitulo--claro">Sobre nós</span>
    <h1>Uma nova forma de gerir o seu condomínio</h1>
    <p>Nascemos com um objetivo: profissionalizar a gestão condominial em <?=e(empresa('regiao'))?>.</p>
  </div>
</section>

<section class="secao">
  <div class="container secao__dupla">
    <div class="texto">
      <h2>Quem somos</h2>
      <p>A <strong><?=e(empresa('nome'))?></strong> surgiu para atender a procura crescente por uma gestão condominial profissional. Somos uma empresa moderna, preparada para lidar com as situações de convivência mais complexas e com as demandas que todo ambiente coletivo gera.</p>
      <p>Atendemos condomínios residenciais, comerciais, mistos e em construção. O objetivo é sempre o mesmo: mais segurança e bem-estar para os condôminos e um patrimônio cada vez mais valorizado.</p>
      <p>Com atuação em Caxias do Sul e região, estamos sempre alinhados às ferramentas inteligentes mais modernas, que geram informação rápida e clara a todos os clientes, e tomamos decisões ágeis dentro da lei e das normas de segurança, respeitando as particularidades de cada condomínio.</p>
    </div>
    <div class="cartoes cartoes--2">
      <div class="cartao"><span class="cartao__ico"><?=icone('escudo')?></span><h3>Segurança jurídica</h3><p>Decisões fundamentadas no Código Civil, na convenção e nas normas técnicas.</p></div>
      <div class="cartao"><span class="cartao__ico"><?=icone('grafico')?></span><h3>Transparência</h3><p>Prestação de contas clara e relatórios que qualquer condômino entende.</p></div>
      <div class="cartao"><span class="cartao__ico"><?=icone('relogio')?></span><h3>Agilidade</h3><p>Atendimento 24 horas por telefone e presença semanal no condomínio.</p></div>
      <div class="cartao"><span class="cartao__ico"><?=icone('pessoas')?></span><h3>Boa convivência</h3><p>Mediação de conflitos com equilíbrio, sem desgastar a relação entre vizinhos.</p></div>
    </div>
  </div>
</section>

<section class="secao secao--clara">
  <div class="container secao__dupla secao__dupla--centro">
    <div class="gestor gestor--claro">
      <div class="gestor__foto" aria-hidden="true"><?=icone('usuario')?></div>
      <div>
        <span class="sobretitulo">Gestor</span>
        <h2><?=e(empresa('gestor'))?></h2>
        <p>Atua como síndico há mais de sete anos e conhece de perto as rotinas, necessidades e desafios de um condomínio. Nesse tempo, formou uma rede seleta de fornecedores e prestadores de serviço.</p>
        <p>O diferencial é conhecer pessoalmente boa parte dos produtos e serviços que indica: estrutura, sede e perfil dos profissionais. Isso garante segurança e qualidade em cada contratação.</p>
        <p>Antes disso, foram mais de vinte anos em funções comerciais, de consultoria e de gestão, em empresas de pequeno a grande porte.</p>
      </div>
    </div>
    <div class="painel">
      <h3 class="titulo-lista">Formação</h3>
      <ul class="lista-check">
        <?php foreach (conteudo('formacao') as $f): ?><li><?=icone('check')?><span><?=e($f)?></span></li><?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

<section class="secao" id="atribuicoes">
  <div class="container secao__dupla">
    <div>
      <span class="sobretitulo">Responsabilidades</span>
      <h2>Você sabe tudo o que um síndico responde?</h2>
      <p>A responsabilidade do síndico é objetiva: basta estar no cargo para responder, mesmo sem intenção ou culpa. Ela alcança seis áreas, e pode atingir o patrimônio pessoal.</p>
      <div class="cartoes cartoes--3 cartoes--mini">
        <?php foreach (conteudo('responsabilidades') as $r): ?><div class="cartao cartao--mini"><?=icone($r['icone'])?><span><?=e($r['titulo'])?></span></div><?php endforeach; ?>
      </div>
    </div>
    <div class="painel">
      <h3 class="titulo-lista">Atribuições do síndico (Código Civil, art. 1.348)</h3>
      <ol class="lista-numerada">
        <li>Convocar a assembleia dos condôminos.</li>
        <li>Representar o condomínio, em juízo ou fora dele, na defesa dos interesses comuns.</li>
        <li>Informar imediatamente à assembleia sobre processos judiciais ou administrativos de interesse do condomínio.</li>
        <li>Cumprir e fazer cumprir a convenção, o regimento interno e as decisões da assembleia.</li>
        <li>Cuidar da conservação e guarda das partes comuns e zelar pelos serviços de interesse dos moradores.</li>
        <li>Elaborar o orçamento anual de receitas e despesas.</li>
        <li>Cobrar as contribuições dos condôminos e aplicar e cobrar as multas devidas.</li>
        <li>Prestar contas à assembleia todo ano e sempre que solicitado.</li>
        <li>Contratar o seguro da edificação.</li>
      </ol>
    </div>
  </div>
</section>

<section class="secao secao--clara">
  <div class="container secao__dupla">
    <div class="painel">
      <h3 class="titulo-lista">Com a Conect Síndico, seu condomínio terá:</h3>
      <ul class="lista-check">
        <?php foreach (conteudo('beneficios') as $d): ?><li><?=icone('check')?><span><?=e($d)?></span></li><?php endforeach; ?>
      </ul>
    </div>
    <div class="painel">
      <h3 class="titulo-lista">Conte com a Conect Síndico para ter uma gestão diferenciada</h3>
      <ul class="lista-check">
        <?php foreach (conteudo('diferenciais') as $d): ?><li><?=icone('check')?><span><?=e($d)?></span></li><?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

<section class="faixa-cta">
  <div class="container faixa-cta__linha">
    <h2>Conte com a Conect Síndico para uma gestão diferenciada.</h2>
    <a class="btn btn--laranja btn--grande" href="<?=url_pagina('contato')?>">Solicitar proposta <?=icone('seta')?></a>
  </div>
</section>
