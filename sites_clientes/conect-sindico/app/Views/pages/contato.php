<?php
// ---------------------------------------------------------------------------------------------------------------
// Envio do formulário (POST). Valida, aplica anti-spam, envia e-mail para a empresa e redireciona (PRG).
// ---------------------------------------------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $dados = [
        'nome' => mb_substr(post_text('nome'), 0, 120),
        'telefone' => mb_substr(post_text('telefone'), 0, 20),
        'email' => mb_strtolower(mb_substr(post_text('email'), 0, 160)),
        'perfil' => mb_substr(post_text('perfil'), 0, 40),
        'condominio' => mb_substr(post_text('condominio'), 0, 120),
        'unidades' => (string)max(0, min(5000, (int)post_text('unidades'))),
        'mensagem' => mb_substr(post_text('mensagem'), 0, 3000),
        'aceite' => post_text('aceite') === '1',
    ];
    $origem = in_array(post_text('origem'), ['home', 'servicos', 'contato'], true) ? post_text('origem') : 'contato';
    // Em caso de erro, volta para a página onde o formulário foi preenchido (rolando até ele).
    $voltar = function () use ($origem): never { header('Location: '.url_pagina($origem).($origem === 'contato' ? '' : '#proposta')); exit; };

    // Campo-isca preenchido = robô. Responde como sucesso para não dar pista.
    if (post_text('site') !== '') { flash('sucesso', 'Recebemos sua mensagem! Em breve entraremos em contato.'); redirect('contato'); }

    $erros = [];
    if (mb_strlen($dados['nome']) < 3) $erros[] = 'informe seu nome';
    if (strlen(so_digitos($dados['telefone'])) < 10) $erros[] = 'informe um telefone com DDD';
    if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) $erros[] = 'informe um e-mail válido';
    if (!$dados['aceite']) $erros[] = 'marque a autorização de contato';
    if ($erros) {
        $_SESSION['form_contato'] = $dados;
        $_SESSION['form_contato_erro'] = 'Confira o formulário: '.implode(', ', $erros).'.';
        $voltar();
    }
    $lim = (array)cfg('limite_contato');
    if (!limite_taxa('contato:'.ip_cliente(), (int)($lim['quantidade'] ?? 5), 60 * (int)($lim['minutos'] ?? 30))) {
        $_SESSION['form_contato'] = $dados;
        $_SESSION['form_contato_erro'] = 'Muitos envios em pouco tempo. Aguarde alguns minutos ou fale conosco pelo WhatsApp.';
        $voltar();
    }

    $linhas = [
        'Nome' => $dados['nome'], 'Telefone' => $dados['telefone'], 'E-mail' => $dados['email'], 'Perfil' => $dados['perfil'],
        'Condomínio' => $dados['condominio'] ?: '—', 'Unidades' => $dados['unidades'] !== '0' ? $dados['unidades'] : '—',
        'Página' => $origem, 'Data' => date('d/m/Y H:i'),
    ];
    $html = '<table style="border-collapse:collapse;width:100%">';
    foreach ($linhas as $k => $val) $html .= '<tr><td style="padding:6px 10px;color:#66788a;width:120px">'.e($k).'</td><td style="padding:6px 10px;font-weight:600">'.e($val).'</td></tr>';
    $html .= '</table>';
    if ($dados['mensagem'] !== '') $html .= '<p style="margin-top:18px;color:#66788a">Mensagem</p><p style="white-space:pre-wrap">'.nl2br(e($dados['mensagem'])).'</p>';
    $html .= '<p style="margin-top:20px"><a href="'.e(link_whatsapp('Olá '.$dados['nome'].', aqui é da Conect Síndico. Recebemos seu contato pelo site.')).'" style="color:#1e8a98">Responder pelo WhatsApp da empresa</a></p>';

    $destino = (string)((cfg('mail') ?? [])['destino'] ?? empresa('email'));
    $ok = enviar_email($destino, 'Novo contato pelo site – '.$dados['nome'], email_modelo('Novo pedido de contato', $html), $dados['email']);

    // Cópia de segurança dos contatos (storage/contatos.csv, fora da web e do Git), caso o e-mail falhe.
    $csv = __DIR__.'/../../../storage/contatos.csv';
    $novo = !is_file($csv);
    if ($fh = @fopen($csv, 'a')) {
        if ($novo) { fwrite($fh, "\xEF\xBB\xBF"); } // BOM: o Excel abre os acentos corretamente
        if ($novo) fputcsv($fh, array_merge(array_keys($linhas), ['Mensagem', 'E-mail enviado']), ';');
        fputcsv($fh, array_merge(array_values($linhas), [$dados['mensagem'], $ok ? 'sim' : 'não']), ';');
        fclose($fh);
    }
    if (!$ok) error_log('[Conect Sindico][Contato] Falha ao enviar e-mail do formulário; contato salvo em storage/contatos.csv.');

    flash('sucesso', 'Recebemos sua mensagem, '.explode(' ', $dados['nome'])[0].'! Retornaremos em até um dia útil.');
    redirect('contato');
}
?>
<section class="cab-pagina">
  <div class="container">
    <span class="sobretitulo sobretitulo--claro">Contato</span>
    <h1>Será um prazer atender você</h1>
    <p>Peça uma proposta, tire dúvidas ou agende uma visita ao seu condomínio.</p>
  </div>
</section>

<section class="secao">
  <div class="container contato">
    <div class="painel">
      <h2 class="titulo-secao">Envie uma mensagem</h2>
      <?php $origemForm = 'contato'; require __DIR__.'/../partials/form_contato.php'; ?>
    </div>
    <aside class="contato__info">
      <a class="contato__item" href="<?=e(link_whatsapp())?>" target="_blank" rel="noopener"><span class="cartao__ico cartao__ico--whats"><?=icone('whatsapp')?></span><span><strong>WhatsApp</strong><?=e(empresa('whatsapp'))?></span></a>
      <a class="contato__item" href="<?=e(link_telefone(empresa('telefone')))?>"><span class="cartao__ico"><?=icone('telefone')?></span><span><strong>Telefone</strong><?=e(empresa('telefone'))?> · atendimento 24h</span></a>
      <a class="contato__item" href="mailto:<?=e(empresa('email'))?>"><span class="cartao__ico"><?=icone('email')?></span><span><strong>E-mail</strong><?=e(empresa('email'))?></span></a>
      <div class="contato__item"><span class="cartao__ico"><?=icone('local')?></span><span><strong>Endereço</strong><?=e(empresa('endereco'))?><br><?=e(empresa('bairro'))?> · <?=e(empresa('cidade'))?>/<?=e(empresa('uf'))?></span></div>
      <a class="contato__mapa" href="https://www.google.com/maps/search/?api=1&amp;query=<?=rawurlencode(endereco_completo())?>" target="_blank" rel="noopener"><?=icone('local')?> Ver no Google Maps</a>
    </aside>
  </div>
</section>
