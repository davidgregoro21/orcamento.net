<?php
/**
 * Login, perfis, auditoria e links de prévia assinados.
 * Perfis: 'admin' (você: vê tudo e gerencia usuários, projetos e pedidos) e 'cliente' (vê só os projetos liberados).
 */

function current_user(): ?array { return $_SESSION['user'] ?? null; }
function is_logged(): bool { return !empty($_SESSION['user']['id']); }
function is_admin(): bool { return (current_user()['perfil'] ?? '') === 'admin'; }
function modo_demo(): bool { return is_logged() && !is_admin() && (string)(cfg('area_cliente') ?? 'demo') === 'demo'; }
function pagina_inicial(): string { return is_logged() ? (is_admin() ? 'admin' : (modo_demo() ? 'projetos' : 'painel')) : 'inicio'; }
/** Para onde vai o usuário depois de entrar: no modo demo, cliente com um só projeto já abre o site dele. */
function ir_para_inicio(): never {
    if (modo_demo()) { $ps = projetos_do_usuario(); if (count($ps) === 1) redirect('visualizar', ['id' => $ps[0]['id']]); }
    redirect(pagina_inicial());
}

function hash_senha(string $s): string { return password_hash($s, PASSWORD_DEFAULT); }
/** Senha provisória legível (sem 0/O/1/l), entregue ao cliente e trocada no primeiro acesso. */
function senha_provisoria(): string {
    $c = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'; $s = '';
    for ($i = 0; $i < 10; $i++) $s .= $c[random_int(0, strlen($c) - 1)];
    return $s;
}
function senha_fraca(string $s): ?string {
    if (mb_strlen($s) < 8) return 'A senha precisa ter pelo menos 8 caracteres.';
    if (!preg_match('/[A-Za-z]/', $s) || !preg_match('/\d/', $s)) return 'Use letras e números na senha.';
    return null;
}

function audit(string $acao, ?string $detalhe = null, ?int $usuarioId = null): void {
    try {
        db()->prepare('INSERT INTO auditoria (usuario_id, acao, detalhe, ip, criado_em) VALUES (?,?,?,?,?)')
            ->execute([$usuarioId ?? (current_user()['id'] ?? null), $acao, $detalhe !== null ? mb_substr($detalhe, 0, 255) : null, ip_cliente(), agora()]);
    } catch (Throwable $e) { error_log('[orcamento.net][auditoria] '.$e->getMessage()); }
}

function build_session_user(array $u): array {
    $cliente = null;
    if (!empty($u['cliente_id'])) { $st = db()->prepare('SELECT id, nome, logo, cor, ativo FROM clientes WHERE id = ?'); $st->execute([$u['cliente_id']]); $cliente = $st->fetch() ?: null; }
    return ['id' => (int)$u['id'], 'nome' => $u['nome'], 'email' => $u['email'], 'empresa' => $u['empresa'] ?? '',
            'perfil' => $u['perfil'], 'deve_trocar_senha' => (int)$u['deve_trocar_senha'] === 1,
            'cliente_id' => $cliente ? (int)$cliente['id'] : null, 'cliente_nome' => $cliente['nome'] ?? '',
            'cliente_logo' => $cliente['logo'] ?? '', 'cliente_cor' => $cliente['cor'] ?? '', 'cliente_ativo' => $cliente ? (int)$cliente['ativo'] === 1 : true];
}
/** Cliente (empresa) do usuário logado. Toda consulta da área do cliente filtra por este id no servidor. */
function cliente_atual(): int { return (int)(current_user()['cliente_id'] ?? 0); }
/** A cada requisição: confere se o usuário continua ativo e se a sessão não expirou por inatividade. */
function refresh_logged_user(): void {
    if (!is_logged()) return;
    $limite = 60 * (int)(cfg('session_idle_minutes') ?: 120);
    if (time() - (int)($_SESSION['last_activity'] ?? 0) > $limite) {
        $_SESSION = []; session_regenerate_id(true);
        flash('info', 'Sua sessão expirou por inatividade. Entre novamente.');
        return;
    }
    $st = db()->prepare('SELECT * FROM usuarios WHERE id = ? AND ativo = 1'); $st->execute([$_SESSION['user']['id']]);
    $u = $st->fetch();
    if (!$u) { $_SESSION = []; session_regenerate_id(true); return; }
    $_SESSION['user'] = build_session_user($u);
    // Cliente desativado pelo administrador: todos os usuários dele perdem o acesso na hora.
    if ($u['perfil'] !== 'admin' && !$_SESSION['user']['cliente_ativo']) { $_SESSION = []; session_regenerate_id(true); flash('erro', 'O acesso da sua empresa está suspenso. Fale com o suporte.'); return; }
    $_SESSION['last_activity'] = time();
}
function exigir_login(): void {
    if (!is_logged()) { $_SESSION['voltar_para'] = $_SERVER['REQUEST_URI'] ?? ''; redirect('login'); }
}
function exigir_admin(): void {
    exigir_login();
    if (!is_admin()) { http_response_code(403); audit('acesso_negado', (string)($_GET['page'] ?? '')); flash('erro', 'Seu acesso não permite abrir essa página.'); redirect(pagina_inicial()); }
}
/** Bloqueio por excesso de tentativas para um e-mail (15 min após 8 erros). */
function login_bloqueado(string $email): bool { return !limite_taxa('login_email:'.$email, 8, 900, false); }
function registrar_falha_login(string $email): void { limite_taxa('login_email:'.$email, 8, 900); }

/** Projetos que o usuário logado pode ver: cliente = só os liberados do próprio cliente; admin = todos. */
function projetos_do_usuario(): array {
    if (!is_logged()) return [];
    if (is_admin()) return db()->query('SELECT * FROM projetos ORDER BY ordem, nome')->fetchAll();
    if (!cliente_atual()) return [];
    $st = db()->prepare('SELECT * FROM projetos WHERE cliente_id = ? AND ativo = 1 ORDER BY ordem, nome');
    $st->execute([cliente_atual()]);
    return $st->fetchAll();
}
/** Busca um registro do cliente logado (ou qualquer um, se admin). Retorna null se não pertencer a ele. */
function registro_do_cliente(string $tabela, int $id, string $extra = ''): ?array {
    $permitidas = ['orcamentos', 'solicitacoes', 'arquivos', 'projetos'];
    if (!in_array($tabela, $permitidas, true)) return null;
    if (is_admin()) { $st = db()->prepare("SELECT * FROM $tabela WHERE id = ?"); $st->execute([$id]); }
    else { $st = db()->prepare("SELECT * FROM $tabela WHERE id = ? AND cliente_id = ? $extra"); $st->execute([$id, cliente_atual()]); }
    return $st->fetch() ?: null;
}
function projeto_liberado(string $slug): ?array {
    foreach (projetos_do_usuario() as $p) if ($p['slug'] === $slug) return $p;
    return null;
}

// --- Link de prévia assinado ------------------------------------------------------------------------------------
// Formato: base64url(json {p: projeto, u: usuário, n: nome, exp}) . "." . base64url(HMAC-SHA256)
// O site do cliente confere a assinatura com a mesma chave (config 'acesso_restrito' => ['chave' => ...]).
function b64url(string $s): string { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }
function token_previa(array $projeto, array $usuario): string {
    $dados = b64url(json_encode(['p' => $projeto['slug'], 'u' => (int)$usuario['id'], 'n' => $usuario['nome'],
        'exp' => time() + 60 * (int)(cfg('previa_validade_min') ?: 10)], JSON_UNESCAPED_UNICODE));
    return $dados.'.'.b64url(hash_hmac('sha256', $dados, (string)cfg('previa_chave'), true));
}
/** Sites hospedados dentro do próprio portal são gravados como caminho interno (ex.: sites/conect-sindico/). */
const SITE_INTERNO = 'sites/conect-sindico/';
function endereco_interno(string $url): bool { return $url !== '' && !preg_match('#^https?://#i', $url); }
function endereco_site(string $url): string { return endereco_interno($url) ? base_path().'/'.ltrim($url, '/') : $url; }
function url_previa(array $projeto, array $usuario): string {
    $url = endereco_site((string)$projeto['url_previa']);
    return $url.(str_contains($url, '?') ? '&' : '?').'acesso='.rawurlencode(token_previa($projeto, $usuario));
}

// --- Recuperação de senha --------------------------------------------------------------------------------------
/** Cria o link de redefinição (válido por 1 hora). O token só é guardado como hash. */
function criar_link_redefinicao(array $u): string {
    $token = bin2hex(random_bytes(32));
    db()->prepare('INSERT INTO senha_reset (usuario_id, token_hash, expira_em, criado_em) VALUES (?,?,?,?)')
        ->execute([$u['id'], hash('sha256', $token), date('Y-m-d H:i:s', time() + 3600), agora()]);
    return url_absoluta(url_pagina('redefinir_senha', ['t' => $token]));
}
function buscar_redefinicao(string $token): ?array {
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) return null;
    $st = db()->prepare('SELECT r.*, u.email, u.nome FROM senha_reset r JOIN usuarios u ON u.id = r.usuario_id WHERE r.token_hash = ? AND r.usado_em IS NULL AND r.expira_em > ? AND u.ativo = 1');
    $st->execute([hash('sha256', $token), agora()]);
    return $st->fetch() ?: null;
}

// --- Rótulos -----------------------------------------------------------------------------------------------------
function situacoes_projeto(): array { return ['planejamento' => 'Planejamento', 'desenvolvimento' => 'Em desenvolvimento', 'aprovacao' => 'Em aprovação', 'publicado' => 'Publicado', 'pausado' => 'Pausado']; }
function status_orcamento(): array { return ['elaboracao' => 'Em elaboração', 'enviado' => 'Enviado', 'aguardando' => 'Aguardando aprovação', 'aprovado' => 'Aprovado', 'recusado' => 'Recusado', 'concluido' => 'Concluído']; }
function status_solicitacao(): array { return ['aberta' => 'Aberta', 'andamento' => 'Em andamento', 'concluida' => 'Concluída', 'recusada' => 'Não será feita']; }
function tipos_solicitacao(): array { return ['alteracao' => 'Alteração', 'melhoria' => 'Melhoria', 'erro' => 'Correção de erro', 'duvida' => 'Dúvida / comentário']; }
function selo(string $chave, array $mapa, string $classe = ''): string { return '<span class="selo selo--'.e($chave).($classe ? ' '.$classe : '').'">'.e($mapa[$chave] ?? $chave).'</span>'; }
function linhas(?string $t): array { return array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", '', (string)$t))))); }
function moeda(?float $v): string { return $v === null ? '—' : 'R$ '.number_format($v, 2, ',', '.'); }
function data_hora(?string $d): string { return $d ? date('d/m/Y H:i', strtotime($d)) : '—'; }
function data_curta(?string $d): string { return $d ? date('d/m/Y', strtotime($d)) : '—'; }
/** Total do orçamento. Retorna null se algum item ainda não tiver valor. */
function total_orcamento(array $itens): ?float {
    $t = 0.0; foreach ($itens as $i) { if ($i['valor_unitario'] === null || $i['valor_unitario'] === '') return null; $t += (float)$i['quantidade'] * (float)$i['valor_unitario']; }
    return $t;
}
