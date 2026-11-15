<?php
// ---------------------------------------------------------------------------------------------------------------
// Pedido de orçamento (POST): valida, anti-spam, grava no banco, avisa por e-mail e redireciona.
// ---------------------------------------------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    // Em caso de erro, volta para a página onde o formulário estava (Contato ou a página do sistema).
    $voltarSlug = preg_replace('/[^a-z0-9-]/', '', post_text('voltar'));
    $destinoErro = $voltarSlug !== '' ? url_pagina('sistema', ['s' => $voltarSlug]).'#orcamento' : url_pagina('contato').'#orcamento';
    if (post_text('site') !== '') { flash('sucesso', 'Pedido recebido! Retornaremos em breve.'); redirect('contato'); } // campo-isca: robô
    $d = [
        'nome' => mb_substr(post_text('nome'), 0, 120), 'email' => mb_strtolower(mb_substr(post_text('email'), 0, 160)),
        'telefone' => mb_substr(post_text('telefone'), 0, 20), 'empresa' => mb_substr(post_text('empresa'), 0, 120),
        'servico' => post_text('servico'), 'prazo' => post_text('prazo'), 'mensagem' => mb_substr(post_text('mensagem'), 0, 3000),
        'aceite' => post_text('aceite') === '1',
    ];
    if (!in_array($d['servico'], conteudo('opcoes_servico'), true)) $d['servico'] = 'Outro';
    if (!in_array($d['prazo'], conteudo('opcoes_prazo'), true)) $d['prazo'] = '';
    $erros = [];
    if (mb_strlen($d['nome']) < 3) $erros[] = 'informe seu nome';
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $erros[] = 'informe um e-mail válido';
    if (strlen(so_digitos($d['telefone'])) < 10) $erros[] = 'informe um telefone com DDD';
    if (!$d['aceite']) $erros[] = 'marque a autorização de contato';
    if ($erros) { $_SESSION['form_orcamento'] = $d; $_SESSION['form_orcamento_erro'] = 'Confira: '.implode(', ', $erros).'.'; header("Location: ".$destinoErro); exit; }
    if (!limite_taxa('orcamento:'.ip_cliente(), 5, 1800)) { $_SESSION['form_orcamento'] = $d; $_SESSION['form_orcamento_erro'] = 'Muitos pedidos em pouco tempo. Tente novamente mais tarde.'; header("Location: ".$destinoErro); exit; }

    db()->prepare('INSERT INTO pedidos_orcamento (nome, email, telefone, empresa, servico, prazo, mensagem, status, ip, criado_em) VALUES (?,?,?,?,?,?,?,?,?,?)')
        ->execute([$d['nome'], $d['email'], $d['telefone'], $d['empresa'] ?: null, $d['servico'], $d['prazo'] ?: null, $d['mensagem'] ?: null, 'novo', ip_cliente(), agora()]);
    $html = '<p><b>'.e($d['nome']).'</b>'.($d['empresa'] ? ' · '.e($d['empresa']) : '').'</p><p>'.e($d['servico']).($d['prazo'] ? ' · '.e($d['prazo']) : '').'</p>'
          .'<p>'.e($d['email']).' · '.e($d['telefone']).'</p>'.($d['mensagem'] ? '<p style="white-space:pre-wrap">'.nl2br(e($d['mensagem'])).'</p>' : '');
    enviar_email((string)((cfg('mail') ?? [])['destino'] ?? contato('email')), 'Novo pedido de orçamento – '.$d['servico'], email_modelo('Novo pedido de orçamento', $html), $d['email']);
    flash('sucesso', 'Pedido recebido, '.explode(' ', $d['nome'])[0].'! Retornaremos em até um dia útil.');
    redirect('contato');
}
// Pré-preenche quando vem de um botão (ex.: /contato?servico=Site%20institucional ou ?sistema=portal-do-condominio).
$preForm = [];
if (isset($_GET['servico']) && in_array($_GET['servico'], conteudo('opcoes_servico'), true)) $preForm['servico'] = $_GET['servico'];
// Vindo do portfólio (?interesse=Nome do item): escolhe o serviço do grupo e já escreve o interesse na mensagem.
if (isset($_GET['interesse'])) {
    $grupoServico = ['sites' => 'Projeto personalizado', 'automacoes' => 'Integrações e automação', 'sistemas' => 'Sistema web sob medida', 'suporte' => 'Manutenção e evolução'];
    foreach (conteudo('portfolio') as $g) foreach ($g['itens'] as $it) if ($it['titulo'] === $_GET['interesse']) $preForm = ['servico' => $grupoServico[$g['id']] ?? 'Outro', 'mensagem' => 'Tenho interesse em: '.$it['titulo'].'.'];
}
if (isset($_GET['sistema'])) { $st = db()->prepare('SELECT nome FROM sistemas WHERE slug = ? AND ativo = 1'); $st->execute([(string)$_GET['sistema']]); if ($n = $st->fetchColumn()) $preForm = ['servico' => 'Um sistema do catálogo', 'mensagem' => 'Tenho interesse no sistema '.$n.'.']; }
?>
<section class="cab-pub">
  <div class="container"><span class="sobre sobre--claro">Contato</span><h1>Peça seu orçamento</h1><p>Conte o que você precisa. Respondemos em até um dia útil, sem compromisso.</p></div>
</section>
<section class="pub-secao" id="orcamento">
  <div class="container pub-form">
    <div class="contato-lado">
      <h2>Fale com a gente</h2>
      <p class="mudo">Prefere conversar antes? Use o canal que for mais prático para você.</p>
      <div class="contato-canais">
        <?php if (contato('whatsapp')): ?><a class="contato-canal" href="<?=e(link_whatsapp('Olá! Quero um orçamento.'))?>" target="_blank" rel="noopener"><span class="ico-box ico-box--verde"><?=icone('whatsapp')?></span><span><b>WhatsApp</b><?=e(contato('whatsapp'))?></span></a><?php endif; ?>
        <a class="contato-canal" href="mailto:<?=e(contato('email'))?>"><span class="ico-box"><?=icone('email')?></span><span><b>E-mail</b><?=e(contato('email'))?></span></a>
        <?php if (contato('cidade')): ?><div class="contato-canal"><span class="ico-box"><?=icone('local')?></span><span><b>Atendimento</b><?=e(contato('cidade'))?> e todo o Brasil, online</span></div><?php endif; ?>
      </div>
      <ul class="mini-lista mini-lista--grande">
        <li><?=icone('check')?> Proposta por escrito, com escopo e prazo</li>
        <li><?=icone('check')?> Valores definidos para o seu projeto</li>
        <li><?=icone('check')?> Seus dados protegidos (LGPD)</li>
      </ul>
    </div>
    <?php require __DIR__.'/../partials/form_orcamento.php'; ?>
  </div>
</section>
<!-- ============ FAQ ============ -->
<section class="pub-secao" id="duvidas">
  <div class="container lp-faq">
    <div>
      <span class="sobre">Dúvidas frequentes</span>
      <h2>Perguntas antes de começar</h2>
      <p class="mudo">Não achou sua dúvida? Escreva no formulário abaixo que respondemos.</p>
    </div>
    <div class="faq">
      <?php foreach (conteudo('faq') as $i => $f): ?>
        <details<?= $i === 0 ? ' open' : '' ?>><summary><?=e($f['p'])?><span class="faq__mais" aria-hidden="true"></span></summary><p><?=e($f['r'])?></p></details>
      <?php endforeach; ?>
    </div>
  </div>
</section>

