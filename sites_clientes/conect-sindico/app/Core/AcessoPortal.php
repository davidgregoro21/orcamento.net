<?php
/**
 * Acesso restrito pela orcamento.net (prévia para o cliente).
 *
 * Com 'acesso_restrito' => ['ativo' => true] o site só abre para quem chegou pelo botão "Ver o site" do portal
 * orcamento.net. O portal envia ?acesso=<token> assinado (HMAC-SHA256) com a mesma 'chave' configurada nos dois lados.
 * O token vale poucos minutos; depois de conferido, a sessão do visitante mantém o acesso por 'horas'.
 * Quando o site for ao ar de verdade, basta 'ativo' => false.
 */
function acesso_portal_ativo(): bool { return !empty((cfg('acesso_restrito') ?? [])['ativo']); }

/** Confere assinatura, validade e projeto do token. Retorna os dados ou null. */
function validar_token_portal(string $token): ?array {
    $c = (array)cfg('acesso_restrito');
    $partes = explode('.', $token, 2);
    if (count($partes) !== 2 || empty($c['chave'])) return null;
    [$dados, $assin] = $partes;
    $esperada = rtrim(strtr(base64_encode(hash_hmac('sha256', $dados, (string)$c['chave'], true)), '+/', '-_'), '=');
    if (!hash_equals($esperada, $assin)) return null;
    $j = json_decode((string)base64_decode(strtr($dados, '-_', '+/')), true);
    if (!is_array($j) || (int)($j['exp'] ?? 0) < time() || ($j['p'] ?? '') !== ($c['projeto'] ?? '')) return null;
    return $j;
}

function exigir_acesso_portal(): void {
    if (!acesso_portal_ativo() || PHP_SAPI === 'cli') return;
    $c = (array)cfg('acesso_restrito');
    // Chegou do portal com token: confere, guarda na sessão e limpa o endereço.
    if (isset($_GET['acesso'])) {
        $dados = validar_token_portal((string)$_GET['acesso']);
        if ($dados) {
            session_regenerate_id(true);
            $_SESSION['previa'] = ['nome' => (string)($dados['n'] ?? ''), 'ate' => time() + 3600 * (int)($c['horas'] ?? 8)];
            $q = $_GET; unset($q['acesso']);
            header('Location: '.strtok($_SERVER['REQUEST_URI'] ?? '/', '?').($q ? '?'.http_build_query($q) : '')); exit;
        }
    }
    if (!empty($_SESSION['previa']) && (int)$_SESSION['previa']['ate'] > time()) return;
    unset($_SESSION['previa']);
    // Sem acesso: manda para o login do portal (que, depois de entrar, mostra o botão "Ver o site").
    header('Location: '.login_do_portal()); exit;
}
/** Login do portal. Vazio na configuração = o portal onde este site está hospedado (…/sites/conect-sindico → …/login). */
function login_do_portal(): string {
    $u = (string)((cfg('acesso_restrito') ?? [])['portal_login'] ?? '');
    return $u !== '' ? $u : preg_replace('#/sites/[^/]+$#', '', base_path()).'/login';
}
function visitante_previa(): ?string { return acesso_portal_ativo() && !empty($_SESSION['previa']) ? (string)$_SESSION['previa']['nome'] : null; }
