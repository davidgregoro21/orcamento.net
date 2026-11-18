<?php
/**
 * Funções de apoio do site (mesmo padrão dos sistemas: cfg, e, csrf, flash, redirect).
 */

function cfg(?string $key = null) {
    static $config;
    if ($config === null) {
        $config = require __DIR__ . '/../../config/config.php';
        // Configurações do servidor (senha do e-mail etc.) ficam em config.local.php, fora do Git.
        $local = __DIR__ . '/../../config/config.local.php';
        if (is_file($local)) $config = array_replace_recursive($config, require $local);
    }
    return $key ? ($config[$key] ?? null) : $config;
}
use App\Core\Database;

/** Listas de conteúdo do site (app/Core/conteudo.php). Ex.: conteudo('servicos'). */
function conteudo(string $chave): array {
    static $c; $c ??= require __DIR__.'/conteudo.php';
    return $c[$chave] ?? [];
}
function contato(string $campo): string { return (string)((cfg('contato') ?? [])[$campo] ?? ''); }
function db(): PDO { return Database::connection(); }
function agora(): string { return date('Y-m-d H:i:s'); }
function ambiente(): string { return (string)(cfg('ambiente') ?: 'desenvolvimento'); }
function e(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// --- Segurança -------------------------------------------------------------------------------------------------
function iniciar_tratamento_erros(): void {
    $producao = ambiente() === 'producao';
    ini_set('display_errors', $producao ? '0' : '1');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
    if ($producao) {
        set_exception_handler(function (Throwable $e) {
            error_log('[orcamento.net] '.$e->getMessage().' em '.$e->getFile().':'.$e->getLine());
            if (!headers_sent()) http_response_code(500);
            echo 'Ocorreu um erro inesperado. Tente novamente em instantes.';
        });
    }
}
function conexao_https(): bool {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (int)($_SERVER['SERVER_PORT'] ?? 0) === 443;
}
function eh_localhost(): bool {
    $h = strtolower(explode(':', (string)($_SERVER['HTTP_HOST'] ?? 'localhost'))[0]);
    return in_array($h, ['localhost', '127.0.0.1', '::1'], true) || str_ends_with($h, '.test');
}
function exigir_https(): void {
    if (!cfg('forcar_https') || eh_localhost() || PHP_SAPI === 'cli') return;
    if (!conexao_https()) {
        header('Location: https://'.($_SERVER['HTTP_HOST'] ?? '').($_SERVER['REQUEST_URI'] ?? '/'), true, 301); exit;
    }
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
function cabecalhos_seguranca(): void {
    if (headers_sent()) return;
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header_remove('X-Powered-By');
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="'.e(csrf_token()).'">'; }
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', (string)$_POST['csrf']))) {
        http_response_code(419); exit('Sessão expirada. Volte, atualize a página e envie novamente.');
    }
}
function ip_cliente(): string { return (string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'); }
/** Limite simples por chave (ex.: envios do formulário por IP), gravado em storage/limites. Retorna false se estourou. */
function limite_taxa(string $chave, int $max, int $segundos, bool $registrar = true): bool {
    $dir = __DIR__.'/../../storage/limites';
    if (!is_dir($dir)) @mkdir($dir, 0770, true);
    $arq = $dir.'/'.hash('sha256', $chave).'.json';
    $agora = time();
    $marcas = is_file($arq) ? (array)json_decode((string)file_get_contents($arq), true) : [];
    $marcas = array_values(array_filter($marcas, fn($t) => (int)$t > $agora - $segundos));
    if (count($marcas) >= $max) return false;
    if (!$registrar) return true;   // só consulta, sem contar esta tentativa
    $marcas[] = $agora;
    @file_put_contents($arq, json_encode($marcas), LOCK_EX);
    return true;
}
/** Endereço http(s) válido. Aceita nomes com sublinhado (ex.: conect_sindico.localhost), que o filter_var recusa. */
function url_valida(string $u): bool { return (bool)preg_match('#^https?://[a-z0-9._-]+(:\d+)?(/[^\s]*)?$#i', $u); }
function post_text(string $key, string $default = ''): string { return trim((string)($_POST[$key] ?? $default)); }

// --- Avisos e redirecionamento ---------------------------------------------------------------------------------
function flash(string $type, string $message): void { $_SESSION['flash'][] = compact('type', 'message'); }
function pull_flashes(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

// --- Endereços -------------------------------------------------------------------------------------------------
/** Pasta pública do site no servidor (ex.: '' na raiz do domínio, '/ConectSindico/public' no XAMPP). */
function base_path(): string {
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
    if ($dir === '.') $dir = '';
    // Hospedagem sem acesso à pasta do domínio (ex.: Hostinger): o .htaccess da raiz encaminha tudo para public/.
    // Nesse caso o visitante não digitou "/public", então os links também não devem ter.
    $uri = (string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (str_ends_with($dir, '/public') && !str_starts_with($uri, $dir.'/') && $uri !== $dir) $dir = substr($dir, 0, -7);
    return $dir;
}
/** Endereço de uma página do site. Ex.: url_pagina('servicos'), url_pagina('usuarios', ['editar' => 3]). */
function url_pagina(string $page = 'inicio', array $params = []): string {
    $b = base_path();
    if (!cfg('urls_amigaveis')) {
        $q = $page === 'inicio' ? $params : ['page' => $page] + $params;
        return $b.'/index.php'.($q ? '?'.http_build_query($q) : '');
    }
    if ($page === 'inicio') $path = '/';
    else $path = '/'.str_replace('_', '-', $page);
    return $b.$path.($params ? '?'.http_build_query($params) : '');
}
/** Endereço completo (com domínio), para canonical, Open Graph, sitemap e e-mails. */
function url_absoluta(string $caminho = '/'): string {
    $base = rtrim((string)(cfg('app_url') ?? ''), '/');
    if ($base === '') $base = (conexao_https() ? 'https' : 'http').'://'.($_SERVER['HTTP_HOST'] ?? 'localhost');
    return $base.$caminho;
}
/** Arquivo estático com versão (?v=data de modificação) para o navegador atualizar o cache quando o arquivo mudar. */
function asset(string $arq): string {
    $p = __DIR__.'/../../public/'.$arq;
    return base_path().'/'.$arq.'?v='.(is_file($p) ? filemtime($p) : '0');
}
function redirect(string $page = 'inicio', array $params = []): never {
    header('Location: '.url_pagina($page, $params)); exit;
}

// --- Contato ---------------------------------------------------------------------------------------------------
function so_digitos(string $v): string { return preg_replace('/\D+/', '', $v); }
function link_whatsapp(string $mensagem = ''): string {
    $n = so_digitos(contato('whatsapp')); if ($n === '') return '';
    return 'https://wa.me/55'.$n.($mensagem !== '' ? '?text='.rawurlencode($mensagem) : '');
}

// --- Texto -----------------------------------------------------------------------------------------------------
function data_br(string $data): string {
    $meses = ['janeiro','fevereiro','março','abril','maio','junho','julho','agosto','setembro','outubro','novembro','dezembro'];
    $t = strtotime($data); if (!$t) return '';
    return date('j', $t).' de '.$meses[(int)date('n', $t) - 1].' de '.date('Y', $t);
}
/** Ícones SVG do site (sem depender de biblioteca externa). */
function icone(string $nome, string $classe = 'ico'): string {
    $p = [
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'seta' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'whatsapp' => '<path d="M3 21l1.7-5A8.5 8.5 0 1 1 8 19.3L3 21z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5l1.2-1.4-2-1-1 .8a4 4 0 0 1-2.1-2.1l.8-1-1-2L9 9.5z"/>',
        'telefone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.8 2z"/>',
        'email' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/>',
        'local' => '<path d="M12 22s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/>',
        'escudo' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'balanca' => '<path d="M12 3v18M5 21h14M6 7h12M6 7l-3 7a3 3 0 0 0 6 0L6 7zM18 7l-3 7a3 3 0 0 0 6 0l-3-7z"/>',
        'grafico' => '<path d="M3 3v18h18"/><path d="m7 15 4-4 3 3 5-6"/>',
        'chave' => '<circle cx="7.5" cy="15.5" r="4.5"/><path d="m10.7 12.3 9.3-9.3M17 6l3 3M15 8l2 2"/>',
        'pessoas' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
        'ferramenta' => '<path d="M14.7 6.3a4 4 0 0 0 5 5L22 14l-8 8-2.3-2.3a4 4 0 0 0-5-5L4 12l8-8 2.7 2.3z"/>',
        'documento' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h5"/>',
        'calendario' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'relogio' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'predio' => '<path d="M4 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18M16 10h2a2 2 0 0 1 2 2v10M2 22h20M8 6h4M8 10h4M8 14h4M8 18h4"/>',
        'folha' => '<path d="M11 20A7 7 0 0 1 4 13c0-6 5-10 16-10 0 11-4 16-10 16"/><path d="M4 21c3-6 7-9 12-11"/>',
        'site' => '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4M2 7h20"/>',
        'sistema' => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
        'portal' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'nuvem' => '<path d="M18 10h-1.3A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/>',
        'engrenagem' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 0 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 0 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 0 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 0 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
        'sair' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'olho' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>',
        'externo' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>',
        'mais' => '<path d="M12 5v14M5 12h14"/>',
        'caixa' => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5.1 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.5-6.9A2 2 0 0 0 16.8 4H7.2a2 2 0 0 0-1.7 1.1z"/>',
        'estrela' => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/>',
        'imagem' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/>',
        'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>',
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>',
        'mensagem' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        'painel' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        'atividade' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
        'lixeira' => '<path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>',
        'editar' => '<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
        'tela' => '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>',
        'foguete' => '<path d="M4.5 16.5c-1.5 1.3-2 5-2 5s3.7-.5 5-2c.7-.8.7-2.1-.1-2.9a2.2 2.2 0 0 0-2.9-.1zM12 15l-3-3a22 22 0 0 1 2-3.9A12.9 12.9 0 0 1 22 2c0 2.7-.8 7.5-6 11a22.4 22.4 0 0 1-4 2z"/><path d="M9 12H4s.6-3 2-4c1.6-1.1 5 0 5 0M12 15v5s3-.6 4-2c1.1-1.6 0-5 0-5"/>',
        'ia' => '<path d="M12 2a4 4 0 0 1 4 4v1a3 3 0 0 1 3 3v1a3 3 0 0 1-1 2.2V15a4 4 0 0 1-4 4h-4a4 4 0 0 1-4-4v-1.8A3 3 0 0 1 5 11v-1a3 3 0 0 1 3-3V6a4 4 0 0 1 4-4z"/><path d="M9 13h.01M15 13h.01M10 16h4"/>',
        'cifrao' => '<path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'api' => '<path d="m16 18 6-6-6-6M8 6l-6 6 6 6"/>',
        'menu' => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'fechar' => '<path d="M18 6 6 18M6 6l12 12"/>',
        'usuario' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'instagram' => '<rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h0"/>',
        'facebook' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
        'expandir' => '<path d="M8 3H5a2 2 0 0 0-2 2v3M21 8V5a2 2 0 0 0-2-2h-3M3 16v3a2 2 0 0 0 2 2h3M16 21h3a2 2 0 0 0 2-2v-3"/>',
        'recolher' => '<path d="M18 15l-6-6-6 6"/>',
        'voltar' => '<path d="M19 12H5M12 19l-7-7 7-7"/>',
        'celular' => '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>',
        'tablet' => '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M11 18h2"/>',
        'linkedin' => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/>',
    ];
    return '<svg class="'.e($classe).'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($p[$nome] ?? '').'</svg>';
}

/** Projetos marcados para o portfólio público. Nunca expõe o link de demonstração: só o endereço publicado. */
function projetos_portfolio(int $limite = 0): array {
    try {
        $sql = 'SELECT p.id, p.nome, p.tipo, p.situacao, p.portfolio_resumo, p.descricao, p.funcionalidades, p.imagem, p.url_publicacao,
                       c.nome AS cliente_nome, c.logo AS cliente_logo, c.localizacao AS cliente_local
                FROM projetos p LEFT JOIN clientes c ON c.id = p.cliente_id
                WHERE p.portfolio = 1 ORDER BY p.ordem, p.criado_em DESC'.($limite > 0 ? ' LIMIT '.(int)$limite : '');
        return db()->query($sql)->fetchAll();
    } catch (Throwable $e) { return []; }
}
