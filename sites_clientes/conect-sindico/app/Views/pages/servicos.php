<section class="cab-pagina">
  <div class="container">
    <span class="sobretitulo sobretitulo--claro">Serviços</span>
    <h1>Projetos exclusivos para cada condomínio</h1>
    <p>Trabalhamos de forma estratégica: mapeamos as características de cada condomínio e montamos um plano de gestão sob medida.</p>
  </div>
</section>

<section class="secao">
  <div class="container">
    <div class="servicos">
      <?php foreach (conteudo('servicos') as $s): ?>
        <article class="servico">
          <div class="servico__cab">
            <span class="cartao__ico cartao__ico--grande"><?=icone($s['icone'])?></span>
            <div><h2><?=e($s['titulo'])?></h2><p><?=e($s['resumo'])?></p></div>
          </div>
          <ul class="lista-check">
            <?php foreach ($s['itens'] as $it): ?><li><?=icone('check')?><span><?=e($it)?></span></li><?php endforeach; ?>
          </ul>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="secao secao--clara">
  <div class="container">
    <div class="pilares">
      <div class="pilar"><?=icone('pessoas')?><h3>Profissionais e parceiros experientes</h3></div>
      <div class="pilar"><?=icone('chave')?><h3>Método exclusivo de gestão</h3></div>
      <div class="pilar"><?=icone('escudo')?><h3>Segurança e bem-estar de todos</h3></div>
    </div>
  </div>
</section>

<section class="secao secao--cta" id="proposta">
  <div class="container secao__dupla">
    <div>
      <span class="sobretitulo">Ficou interessado?</span>
      <h2>Receba uma proposta para o seu condomínio</h2>
      <p>Informe seus dados e entramos em contato para entender a realidade do prédio e montar a proposta.</p>
    </div>
    <div class="painel"><?php $origemForm = 'servicos'; require __DIR__.'/../partials/form_contato.php'; ?></div>
  </div>
</section>
