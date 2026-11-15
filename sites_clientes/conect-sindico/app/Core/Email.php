<?php
/**
 * Envio de e-mail do site (formulário de contato). Mesmo cliente SMTP usado nos sistemas.
 *
 * Configure em config/config.local.php:
 *   'mail' => ['host'=>'smtp.provedor.com.br','port'=>587,'seguranca'=>'tls','usuario'=>'...','senha'=>'...',
 *              'remetente'=>'site@conectsindico.com.br','nome'=>'Site Conect Síndico','destino'=>'contato@conectsindico.com.br'],
 *
 * Sem servidor configurado (ambiente de teste), a mensagem é gravada em storage/emails/ em vez de enviada.
 */

/** Corpo HTML padrão dos e-mails do site. */
function email_modelo(string $titulo, string $conteudoHtml): string {
    return '<!doctype html><html lang="pt-BR"><body style="margin:0;background:#eef3f5;font-family:Segoe UI,Arial,sans-serif;color:#22303a">'
        .'<div style="max-width:600px;margin:24px auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #d9e4e8">'
        .'<div style="background:#0f3d47;color:#fff;padding:18px 24px"><div style="font-size:12px;letter-spacing:.12em;color:#faa61b;font-weight:700">CONECT SÍNDICO · SITE</div>'
        .'<div style="font-size:20px;font-weight:700;margin-top:4px">'.e($titulo).'</div></div>'
        .'<div style="padding:24px;font-size:15px;line-height:1.55">'.$conteudoHtml.'</div>'
        .'<div style="padding:14px 24px;background:#f5f8f9;font-size:12px;color:#66788a">Mensagem enviada pelo formulário do site conectsindico.com.br.</div>'
        .'</div></body></html>';
}

/**
 * Envia um e-mail. Retorna true se foi entregue ao servidor (ou gravado em arquivo no modo de teste).
 */
function enviar_email(string $para, string $assunto, string $html, ?string $responderPara = null): bool {
    if (!filter_var($para, FILTER_VALIDATE_EMAIL)) return false;
    $cfg = (array)(cfg('mail') ?? []);
    $de = (string)($cfg['remetente'] ?? 'site@conectsindico.com.br');
    $nome = (string)($cfg['nome'] ?? 'Site Conect Síndico');
    $texto = trim(html_entity_decode(strip_tags(str_replace(['<br>', '</p>', '</div>', '</li>'], "\n", $html)), ENT_QUOTES, 'UTF-8'));
    $fronteira = 'cn'.bin2hex(random_bytes(8));
    $cab = [
        'Date: '.date('r'),
        'From: =?UTF-8?B?'.base64_encode($nome).'?= <'.$de.'>',
        'To: <'.$para.'>',
        'Subject: =?UTF-8?B?'.base64_encode($assunto).'?=',
        'MIME-Version: 1.0',
    ];
    if ($responderPara && filter_var($responderPara, FILTER_VALIDATE_EMAIL)) $cab[] = 'Reply-To: <'.$responderPara.'>';
    $cab = array_merge($cab, [
        'Message-ID: <'.bin2hex(random_bytes(12)).'@'.(explode('@', $de)[1] ?? 'conectsindico.com.br').'>',
        'Content-Type: multipart/alternative; boundary="'.$fronteira.'"',
    ]);
    $corpo = "--$fronteira\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n".chunk_split(base64_encode($texto))
           ."--$fronteira\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n".chunk_split(base64_encode($html))
           ."--$fronteira--\r\n";
    $mensagem = implode("\r\n", $cab)."\r\n\r\n".$corpo;

    if (empty($cfg['host'])) {
        // Modo de teste: grava a mensagem para conferência (storage/emails, fora do Git e bloqueada para a web).
        $dir = __DIR__.'/../../storage/emails';
        if (!is_dir($dir)) @mkdir($dir, 0770, true);
        $ok = @file_put_contents($dir.'/'.date('Ymd_His').'_'.bin2hex(random_bytes(3)).'.eml', $mensagem) !== false;
        if (!$ok) error_log('[Conect Sindico][E-mail] Não foi possível gravar o e-mail em storage/emails.');
        return $ok;
    }
    try { return smtp_enviar($cfg, $de, $para, $mensagem); }
    catch (Throwable $e) { error_log('[Conect Sindico][E-mail] '.$e->getMessage()); return false; }
}

/** Cliente SMTP mínimo (STARTTLS ou SSL, AUTH LOGIN). */
function smtp_enviar(array $cfg, string $de, string $para, string $mensagem): bool {
    $seg = strtolower((string)($cfg['seguranca'] ?? 'tls'));
    $porta = (int)($cfg['port'] ?? ($seg === 'ssl' ? 465 : 587));
    $host = ($seg === 'ssl' ? 'ssl://' : 'tcp://').$cfg['host'].':'.$porta;
    $s = @stream_socket_client($host, $errno, $errstr, 15);
    if (!$s) throw new RuntimeException("SMTP: falha ao conectar em {$cfg['host']}:$porta ($errstr)");
    stream_set_timeout($s, 15);
    $ler = function () use ($s): string { $r = ''; while (($l = fgets($s, 515)) !== false) { $r .= $l; if (isset($l[3]) && $l[3] === ' ') break; } return $r; };
    $cmd = function (string $c, array $esperado) use ($s, $ler): string { if ($c !== '') fwrite($s, $c."\r\n"); $r = $ler(); if (!in_array((int)substr($r, 0, 3), $esperado, true)) throw new RuntimeException('SMTP: resposta inesperada a "'.explode(' ', $c)[0].'": '.trim($r)); return $r; };
    $cmd('', [220]);
    $eu = gethostname() ?: 'localhost';
    $cmd("EHLO $eu", [250]);
    if ($seg === 'tls') { $cmd('STARTTLS', [220]); if (!stream_socket_enable_crypto($s, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) throw new RuntimeException('SMTP: falha no STARTTLS'); $cmd("EHLO $eu", [250]); }
    if (!empty($cfg['usuario'])) { $cmd('AUTH LOGIN', [334]); $cmd(base64_encode((string)$cfg['usuario']), [334]); $cmd(base64_encode((string)($cfg['senha'] ?? '')), [235]); }
    $cmd("MAIL FROM:<$de>", [250]);
    $cmd("RCPT TO:<$para>", [250, 251]);
    $cmd('DATA', [354]);
    fwrite($s, preg_replace('/^\./m', '..', $mensagem)."\r\n.\r\n");
    $cmd('', [250]);
    @fwrite($s, "QUIT\r\n"); fclose($s);
    return true;
}
