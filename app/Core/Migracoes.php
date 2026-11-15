<?php
/**
 * Atualizações do banco de dados, aplicadas uma única vez e registradas na tabela `migracoes`.
 * Roda sozinha no primeiro acesso depois de enviar uma versão nova (bootstrap.php) e na instalação.
 * Nunca apaga dados: só cria tabelas/colunas que faltam e copia informações para a estrutura nova.
 */
const VERSAO_BANCO = '4';

/** Converte o SQL do MySQL para SQLite (usado só quando 'driver' => 'sqlite', para testes). */
function sql_para_sqlite(string $sql): string {
    $sql = preg_replace('/INT UNSIGNED AUTO_INCREMENT PRIMARY KEY/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
    $sql = preg_replace('/\)\s*ENGINE=[^;]+;/i', ');', $sql);
    $sql = preg_replace('/,\s*INDEX \([^)]*\)(, INDEX \([^)]*\))*/i', '', $sql);
    return str_ireplace(['INT UNSIGNED', 'TINYINT(1)'], ['INTEGER', 'INTEGER'], $sql);
}
function executar_arquivo_sql(string $arquivo): void {
    $sql = (string)file_get_contents($arquivo);
    if (App\Core\Database::driver() === 'sqlite') $sql = sql_para_sqlite($sql);
    $sql = preg_replace('/--[^\n]*/', '', $sql);   // tira comentários (inclusive no fim da linha)
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $cmd) db()->exec($cmd);
}
function tabela_existe(string $t): bool {
    if (App\Core\Database::driver() === 'sqlite') { $st = db()->prepare("SELECT 1 FROM sqlite_master WHERE type='table' AND name=?"); $st->execute([$t]); return (bool)$st->fetchColumn(); }
    $st = db()->prepare('SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'); $st->execute([$t]); return (bool)$st->fetchColumn();
}
function coluna_existe(string $t, string $c): bool {
    if (App\Core\Database::driver() === 'sqlite') { foreach (db()->query("PRAGMA table_info($t)")->fetchAll() as $col) if ($col['name'] === $c) return true; return false; }
    $st = db()->prepare('SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'); $st->execute([$t, $c]); return (bool)$st->fetchColumn();
}
function adicionar_coluna(string $t, string $c, string $def): void {
    if (!coluna_existe($t, $c)) db()->exec("ALTER TABLE $t ADD COLUMN $c ".(App\Core\Database::driver() === 'sqlite' ? str_ireplace(['INT UNSIGNED', 'TINYINT(1)'], ['INTEGER', 'INTEGER'], $def) : $def));
}
function migracao_aplicada(string $v): bool {
    $st = db()->prepare('SELECT 1 FROM migracoes WHERE versao = ?'); $st->execute([$v]); return (bool)$st->fetchColumn();
}
function registrar_migracao(string $v): void { db()->prepare('INSERT INTO migracoes (versao, aplicada_em) VALUES (?, ?)')->execute([$v, agora()]); }

/** Leva o banco até a versão atual. $instalacaoNova = true pula a cópia de dados do v1 (não há). */
function aplicar_migracoes(bool $instalacaoNova = false): array {
    $feito = [];
    // Tabelas novas (CREATE TABLE IF NOT EXISTS não mexe nas que já existem).
    executar_arquivo_sql(__DIR__.'/../../database/orcamento_net.sql');

    if (!migracao_aplicada('002_v2_multiprojeto')) {
        // Colunas novas em tabelas do v1.
        adicionar_coluna('usuarios', 'cliente_id', 'INT UNSIGNED NULL');
        adicionar_coluna('projetos', 'cliente_id', 'INT UNSIGNED NULL');
        adicionar_coluna('projetos', 'tipo', 'VARCHAR(60) NULL');
        adicionar_coluna('projetos', 'situacao', "VARCHAR(30) NOT NULL DEFAULT 'desenvolvimento'");
        adicionar_coluna('projetos', 'funcionalidades', 'TEXT NULL');
        adicionar_coluna('projetos', 'futuras', 'TEXT NULL');
        adicionar_coluna('projetos', 'url_publicacao', 'VARCHAR(255) NULL');
        adicionar_coluna('projetos', 'permitir_embed', 'TINYINT(1) NOT NULL DEFAULT 1');
        adicionar_coluna('projetos', 'atualizado_em', 'DATETIME NULL');

        if (!$instalacaoNova) {
            // v1 → v2: cada usuário "cliente" vira (ou entra em) um cliente; os projetos liberados para ele passam a ser desse cliente.
            $criarCliente = db()->prepare('INSERT INTO clientes (nome, email, ativo, criado_em) VALUES (?, ?, 1, ?)');
            $buscarCliente = db()->prepare('SELECT id FROM clientes WHERE nome = ?');
            $usuarios = db()->query("SELECT * FROM usuarios WHERE perfil = 'cliente' AND cliente_id IS NULL")->fetchAll();
            foreach ($usuarios as $u) {
                $nome = trim((string)($u['empresa'] ?: $u['nome']));
                $buscarCliente->execute([$nome]); $cid = (int)$buscarCliente->fetchColumn();
                if (!$cid) { $criarCliente->execute([$nome, $u['email'], agora()]); $cid = (int)db()->lastInsertId(); }
                db()->prepare('UPDATE usuarios SET cliente_id = ? WHERE id = ?')->execute([$cid, $u['id']]);
                $st = db()->prepare('SELECT projeto_id FROM usuario_projeto WHERE usuario_id = ?'); $st->execute([$u['id']]);
                foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $pid) db()->prepare('UPDATE projetos SET cliente_id = ? WHERE id = ? AND cliente_id IS NULL')->execute([$cid, $pid]);
                $feito[] = 'Cliente "'.$nome.'" criado a partir do usuário '.$u['email'];
            }
            // Projetos ainda sem cliente, mas com o nome do cliente digitado no v1.
            $pConect = db()->query("SELECT id FROM projetos WHERE slug = 'conect-sindico' AND cliente_id IS NULL")->fetchColumn();
            if ($pConect) db()->prepare("UPDATE projetos SET cliente = 'Conect Síndico' WHERE id = ?")->execute([$pConect]);
            foreach (db()->query("SELECT id, cliente FROM projetos WHERE cliente_id IS NULL AND cliente IS NOT NULL AND cliente <> ''")->fetchAll() as $p) {
                $buscarCliente->execute([$p['cliente']]); $cid = (int)$buscarCliente->fetchColumn();
                if (!$cid) { $criarCliente->execute([$p['cliente'], null, agora()]); $cid = (int)db()->lastInsertId(); }
                db()->prepare('UPDATE projetos SET cliente_id = ? WHERE id = ?')->execute([$cid, $p['id']]);
            }
            completar_conect_existente();   // dados reais da Conect no projeto que já existia
        }
        registrar_migracao('002_v2_multiprojeto');
        $feito[] = 'Banco atualizado para a versão 2 (clientes, projetos, orçamentos, solicitações e arquivos).';
    }
    if (!migracao_aplicada('003_conect_dentro_do_portal')) {
        // O site da Conect agora roda dentro do portal (sites/conect-sindico/), sem subdomínio separado.
        $n = db()->exec("UPDATE projetos SET url_previa = '".SITE_INTERNO."' WHERE slug = 'conect-sindico' AND url_previa NOT LIKE 'sites/%' AND (url_previa LIKE '%conect%sindico%' OR url_previa = '')");
        registrar_migracao('003_conect_dentro_do_portal');
        if ($n) $feito[] = 'Projeto Conect Síndico passou a abrir o site dentro do portal (sites/conect-sindico/).';
    }
    if (!migracao_aplicada('004_usuario_daniel')) {
        if (criar_usuario_daniel()) $feito[] = 'Acesso do Daniel (Conect Síndico) criado com a senha padrão.';
        registrar_migracao('004_usuario_daniel');
    }
    @file_put_contents(__DIR__.'/../../storage/.versao_banco', VERSAO_BANCO);
    return $feito;
}

/** Chamado a cada requisição: se o banco estiver numa versão antiga, atualiza (uma vez). */
function garantir_banco_atualizado(): void {
    $marca = __DIR__.'/../../storage/.versao_banco';
    if (is_file($marca) && trim((string)file_get_contents($marca)) === VERSAO_BANCO) return;
    try {
        $feito = aplicar_migracoes();
        foreach ($feito as $f) error_log('[orcamento.net][migração] '.$f);
    } catch (Throwable $e) {
        error_log('[orcamento.net][migração] '.$e->getMessage());
        http_response_code(500);
        exit('Não foi possível atualizar o banco de dados automaticamente: '.e($e->getMessage()).'. Rode database/migracao_v1_para_v2.sql no phpMyAdmin.');
    }
}
