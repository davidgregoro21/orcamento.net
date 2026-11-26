<?php
/**
 * AMANDA RODRIGUES ADVOCACIA — Envio do formulário de contato
 * Funciona na hospedagem Hostinger (PHP com função mail()).
 *
 * CONFIGURAR:
 *  - $destino: e-mail que recebe as mensagens.
 *  - $remetente: use um e-mail DO SEU DOMÍNIO (ex.: contato@seudominio.com.br),
 *    criado no painel da Hostinger. Isso evita que a mensagem caia no spam.
 */
header('Content-Type: application/json; charset=utf-8');

$destino   = 'faj.atendimento@gmail.com';
$remetente = 'contato@seudominio.com.br'; // SUBSTITUIR pelo e-mail do domínio
$assunto   = 'Novo contato pelo site - Amanda Rodrigues Advocacia';

function responder($ok, $erro = '') {
    echo json_encode(['ok' => $ok, 'erro' => $erro]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); responder(false, 'Método inválido.'); }

// Anti-spam: o campo oculto "site" deve vir vazio
if (!empty($_POST['site'])) { responder(true); }

// Limite simples: 1 envio a cada 30 segundos por sessão
if (session_status() !== PHP_SESSION_ACTIVE) session_start();   // dentro do portal a sessão já vem aberta
if (isset($_SESSION['ultimo_envio']) && time() - $_SESSION['ultimo_envio'] < 30) {
    responder(false, 'Aguarde alguns segundos antes de enviar novamente.');
}

$limpar = function ($v, $max) { return mb_substr(trim(strip_tags((string)$v)), 0, $max); };
$nome     = $limpar($_POST['nome'] ?? '', 120);
$telefone = $limpar($_POST['telefone'] ?? '', 20);
$email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$mensagem = $limpar($_POST['mensagem'] ?? '', 3000);
$lgpd     = !empty($_POST['lgpd']);

if (mb_strlen($nome) < 2 || strlen(preg_replace('/\D/', '', $telefone)) < 10 || !$email || mb_strlen($mensagem) < 5 || !$lgpd) {
    responder(false, 'Dados inválidos.');
}

// Impede injeção de cabeçalhos
$nome  = str_replace(["\r", "\n"], ' ', $nome);
$email = str_replace(["\r", "\n"], '', $email);

$corpo  = "Nova mensagem recebida pelo site\n\n";
$corpo .= "Nome: {$nome}\nTelefone: {$telefone}\nE-mail: {$email}\n\n";
$corpo .= "Mensagem:\n{$mensagem}\n\n";
$corpo .= "Consentimento LGPD: sim\nData: " . date('d/m/Y H:i') . "\n";

$cabecalhos  = "From: Site Amanda Rodrigues <{$remetente}>\r\n";
$cabecalhos .= "Reply-To: {$nome} <{$email}>\r\n";
$cabecalhos .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";

$enviado = mail($destino, '=?UTF-8?B?' . base64_encode($assunto) . '?=', $corpo, $cabecalhos);

if ($enviado) { $_SESSION['ultimo_envio'] = time(); responder(true); }
responder(false, 'Falha no envio.');
