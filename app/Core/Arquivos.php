<?php
/**
 * Envio e download de arquivos.
 * - Documentos dos clientes: storage/arquivos (fora da web). Download só por index.php?page=baixar, com permissão.
 * - Logos e imagens de prévia: public/uploads/{clientes|projetos} (públicas, só imagens).
 */
const EXTENSOES_PERMITIDAS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'txt', 'csv', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'zip', 'rar', 'psd', 'ai', 'mp4'];
const TAMANHO_MAXIMO = 25 * 1024 * 1024; // 25 MB

function erro_upload(int $codigo): string {
    return match ($codigo) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Arquivo maior que o permitido pela hospedagem.',
        UPLOAD_ERR_PARTIAL => 'O envio foi interrompido. Tente de novo.',
        UPLOAD_ERR_NO_FILE => 'Escolha um arquivo.',
        default => 'Não foi possível receber o arquivo.',
    };
}

/** Grava um documento enviado em storage/arquivos e devolve [nome original, nome gravado, tamanho, mime]. Lança Exception com mensagem amigável. */
function guardar_documento(array $f): array {
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new RuntimeException(erro_upload((int)($f['error'] ?? 4)));
    if (!is_uploaded_file($f['tmp_name'])) throw new RuntimeException('Envio inválido.');
    if ($f['size'] > TAMANHO_MAXIMO) throw new RuntimeException('O arquivo passa de 25 MB.');
    $nome = mb_substr(preg_replace('/[^\pL\pN ._()-]+/u', '_', basename((string)$f['name'])), 0, 200);
    $ext = strtolower(pathinfo($nome, PATHINFO_EXTENSION));
    if (!in_array($ext, EXTENSOES_PERMITIDAS, true)) throw new RuntimeException('Tipo de arquivo não permitido (.'.$ext.').');
    $dir = __DIR__.'/../../storage/arquivos';
    if (!is_dir($dir)) @mkdir($dir, 0770, true);
    $gravado = bin2hex(random_bytes(16)).'.bin';   // sem extensão executável
    if (!move_uploaded_file($f['tmp_name'], $dir.'/'.$gravado)) throw new RuntimeException('Não foi possível gravar o arquivo no servidor.');
    $mime = function_exists('mime_content_type') ? (mime_content_type($dir.'/'.$gravado) ?: 'application/octet-stream') : 'application/octet-stream';
    return [$nome, $gravado, (int)$f['size'], $mime];
}
function caminho_documento(string $gravado): string { return __DIR__.'/../../storage/arquivos/'.basename($gravado); }

/** Grava uma imagem pública (logo do cliente, prévia do projeto). Devolve o caminho relativo a public/. */
function guardar_imagem(array $f, string $pasta): ?string {
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) throw new RuntimeException(erro_upload((int)$f['error']));
    if ($f['size'] > 5 * 1024 * 1024) throw new RuntimeException('A imagem passa de 5 MB.');
    $info = @getimagesize($f['tmp_name']);
    $tipos = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
    if (!$info || !isset($tipos[$info[2]])) throw new RuntimeException('Envie uma imagem JPG, PNG, WEBP ou GIF.');
    $pasta = in_array($pasta, ['clientes', 'projetos'], true) ? $pasta : 'projetos';
    $dir = __DIR__.'/../../public/uploads/'.$pasta;
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $nome = bin2hex(random_bytes(10)).'.'.$tipos[$info[2]];
    if (!move_uploaded_file($f['tmp_name'], $dir.'/'.$nome)) throw new RuntimeException('Não foi possível gravar a imagem.');
    return 'uploads/'.$pasta.'/'.$nome;
}
/** Endereço de uma imagem guardada (uploads/... ou um arquivo de assets/img). */
function url_imagem(?string $img): ?string {
    if (!$img) return null;
    $rel = str_contains($img, '/') ? $img : 'assets/img/'.$img;
    return is_file(__DIR__.'/../../public/'.$rel) ? asset($rel) : null;
}
function tamanho_legivel(int $b): string {
    foreach (['B', 'KB', 'MB', 'GB'] as $u) { if ($b < 1024) return ($u === 'B' ? $b : number_format($b, 1, ',', '.')).' '.$u; $b /= 1024; }
    return number_format($b, 1, ',', '.').' TB';
}
function icone_arquivo(string $nome): string {
    $ext = strtolower(pathinfo($nome, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true) ? 'imagem' : ($ext === 'zip' || $ext === 'rar' ? 'caixa' : 'documento');
}
