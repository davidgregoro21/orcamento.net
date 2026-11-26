<?php
/**
 * Dados reais do primeiro cliente (Conect Síndico), usados na instalação e na atualização do banco.
 * Nada aqui é fictício: são os dados informados pelo dono da plataforma e o que foi desenvolvido no site.
 * Valores do orçamento ficam em branco (a definir) e o orçamento nasce oculto para o cliente.
 */
function dados_conect(): array {
    return [
        'cliente' => [
            'nome' => 'Conect Síndico', 'localizacao' => 'Caxias do Sul/RS', 'tipo_projeto' => 'Site institucional',
            'categoria' => 'Serviços de sindicância e gestão condominial', 'site_atual' => 'https://conectsindico.com.br',
            'logo' => 'assets/img/clientes/conect-sindico.png', 'cor' => '#34B1C0',
            'telefone' => '(54) 3538-2146',
        ],
        'projeto' => [
            'slug' => 'conect-sindico', 'nome' => 'Site institucional Conect Síndico', 'tipo' => 'Site institucional', 'situacao' => 'aprovacao',
            'chamada' => 'O novo site da Conect Síndico está pronto para você ver.',
            'descricao' => "Reformulação completa do site conectsindico.com.br: layout novo nas cores da marca, conteúdo revisado, formulário de proposta e blog com os artigos atuais.\nEsta é a versão de apresentação. Navegue à vontade: é assim que o site vai ficar.",
            'funcionalidades' => "Páginas Início, Sobre, Serviços, Blog, Contato e Política de privacidade\nLayout adaptado para celular, tablet e computador\nFormulário de proposta com aviso por e-mail e cópia de segurança\nProteção contra spam e autorização de contato (LGPD)\nBotão de WhatsApp em todas as páginas\nBlog com 16 artigos e filtro por categoria\nLinks antigos do blog redirecionados (preserva o Google)\nSEO: título e descrição por página, mapa do site e dados de empresa local\nImagem de compartilhamento para WhatsApp e redes sociais",
            'futuras' => "Portal do condômino com login\nPainel do síndico para vários condomínios\nMural de comunicados e documentos (atas, balancetes)\nReserva de áreas comuns\nChamados e ocorrências\nRelatório mensal para o conselho",
            'imagem' => 'previa-conect-sindico.jpg',
        ],
        'orcamento_itens' => [
            ['descricao' => 'Site institucional', 'detalhes' => "7 páginas: Início, Sobre, Serviços, Blog, Artigo, Contato e Privacidade\nLayout novo nas cores da marca, adaptado ao celular\nFormulário de proposta com LGPD e proteção contra spam\nWhatsApp em todas as páginas\nBlog com os 16 artigos migrados e endereços preservados\nSEO, mapa do site e dados de empresa local\nHTTPS, publicação e envio ao Google Search Console\nDuas rodadas de ajustes"],
            ['descricao' => 'Manutenção mensal (opcional)', 'detalhes' => "Atualizações de segurança e backup\nAté 2 alterações de texto ou imagem por mês\nPublicação de 1 artigo enviado pelo cliente"],
        ],
    ];
}

/** Cria o cliente Conect Síndico, o projeto do site e um orçamento em elaboração (sem valores). Retorna o id do cliente. */
function criar_cliente_conect(string $urlPrevia, string $urlProposta = ''): int {
    $d = dados_conect(); $c = $d['cliente']; $p = $d['projeto'];
    $st = db()->prepare('SELECT id FROM clientes WHERE nome = ?'); $st->execute([$c['nome']]);
    $cid = (int)$st->fetchColumn();
    if (!$cid) {
        db()->prepare('INSERT INTO clientes (nome, localizacao, tipo_projeto, categoria, telefone, site_atual, logo, cor, ativo, criado_em) VALUES (?,?,?,?,?,?,?,?,1,?)')
            ->execute([$c['nome'], $c['localizacao'], $c['tipo_projeto'], $c['categoria'], $c['telefone'], $c['site_atual'], $c['logo'], $c['cor'], agora()]);
        $cid = (int)db()->lastInsertId();
    }
    $st = db()->prepare('SELECT id FROM projetos WHERE slug = ?'); $st->execute([$p['slug']]);
    $pid = (int)$st->fetchColumn();
    if (!$pid) {
        db()->prepare('INSERT INTO projetos (cliente_id, slug, nome, tipo, situacao, chamada, descricao, funcionalidades, futuras, url_previa, url_proposta, imagem, permitir_embed, ativo, ordem, criado_em, atualizado_em) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,1,0,?,?)')
            ->execute([$cid, $p['slug'], $p['nome'], $p['tipo'], $p['situacao'], $p['chamada'], $p['descricao'], $p['funcionalidades'], $p['futuras'], $urlPrevia, $urlProposta ?: null, $p['imagem'], agora(), agora()]);
        $pid = (int)db()->lastInsertId();
        db()->prepare('INSERT INTO projeto_atualizacoes (projeto_id, versao, titulo, descricao, visivel_cliente, criado_em) VALUES (?,?,?,?,1,?)')
            ->execute([$pid, '1.0', 'Versão de apresentação liberada', 'Primeira versão completa do novo site, com todas as páginas, formulário de proposta e blog.', agora()]);
    }
    criar_orcamento_conect($cid, $pid);
    criar_usuario_daniel($cid);
    return $cid;
}
/** Acesso do Daniel (cliente Conect) com a senha padrão. Só cria se o cliente ainda não tiver nenhum usuário. */
const DANIEL_EMAIL = 'daniel@conectsindico.com.br';
const DANIEL_SENHA_PADRAO = 'Conect@2026';
function criar_usuario_daniel(?int $cid = null): bool {
    if (!$cid) { $st = db()->prepare("SELECT cliente_id FROM projetos WHERE slug = 'conect-sindico'"); $st->execute(); $cid = (int)$st->fetchColumn(); }
    if (!$cid) return false;
    $st = db()->prepare('SELECT COUNT(*) FROM usuarios WHERE cliente_id = ? OR email = ?'); $st->execute([$cid, DANIEL_EMAIL]);
    if ((int)$st->fetchColumn() > 0) return false;
    db()->prepare("INSERT INTO usuarios (cliente_id, nome, email, empresa, senha_hash, perfil, ativo, deve_trocar_senha, criado_em) VALUES (?,?,?,?,?,'cliente',1,0,?)")
        ->execute([$cid, 'Daniel Mesquita de Almeida', DANIEL_EMAIL, 'Conect Síndico', hash_senha(DANIEL_SENHA_PADRAO), agora()]);
    return true;
}
function criar_orcamento_conect(int $cid, int $pid): void {
    $st = db()->prepare('SELECT COUNT(*) FROM orcamentos WHERE cliente_id = ?'); $st->execute([$cid]);
    if ((int)$st->fetchColumn() > 0) return;
    db()->prepare('INSERT INTO orcamentos (cliente_id, projeto_id, numero, titulo, descricao, prazo, status, visivel_cliente, observacoes, criado_em, atualizado_em) VALUES (?,?,?,?,?,?,?,0,?,?,?)')
        ->execute([$cid, $pid, proximo_numero_orcamento(), 'Novo site institucional – Conect Síndico', 'Reformulação completa do site conectsindico.com.br.', '15 dias úteis após o envio de textos e fotos', 'elaboracao', 'Preencher valores antes de enviar ao cliente.', agora(), agora()]);
    $oid = (int)db()->lastInsertId();
    $ins = db()->prepare('INSERT INTO orcamento_itens (orcamento_id, descricao, detalhes, quantidade, valor_unitario, ordem) VALUES (?,?,?,1,NULL,?)');
    foreach (dados_conect()['orcamento_itens'] as $i => $it) $ins->execute([$oid, $it['descricao'], $it['detalhes'], $i]);
}
/** Próximo número de orçamento: ANO-0001. */
function proximo_numero_orcamento(): string {
    $ano = date('Y'); $st = db()->prepare('SELECT numero FROM orcamentos WHERE numero LIKE ? ORDER BY numero DESC LIMIT 1'); $st->execute([$ano.'-%']);
    $ult = (string)$st->fetchColumn();
    return $ano.'-'.str_pad((string)((int)substr($ult, 5) + 1), 4, '0', STR_PAD_LEFT);
}
/** Atualização do v1: completa os dados do cliente/projeto da Conect se já existiam com poucos dados. */
function completar_conect_existente(): void {
    $d = dados_conect();
    $st = db()->prepare('SELECT * FROM projetos WHERE slug = ?'); $st->execute([$d['projeto']['slug']]); $p = $st->fetch();
    if (!$p) return;
    $cid = (int)$p['cliente_id'];
    if (!$cid) { $cid = criar_cliente_conect($p['url_previa']); return; }
    $c = $d['cliente'];
    db()->prepare("UPDATE clientes SET localizacao = COALESCE(localizacao, ?), tipo_projeto = COALESCE(tipo_projeto, ?), categoria = COALESCE(categoria, ?), site_atual = COALESCE(site_atual, ?), logo = COALESCE(logo, ?), cor = COALESCE(cor, ?), telefone = COALESCE(telefone, ?) WHERE id = ?")
        ->execute([$c['localizacao'], $c['tipo_projeto'], $c['categoria'], $c['site_atual'], $c['logo'], $c['cor'], $c['telefone'], $cid]);
    $pp = $d['projeto'];
    db()->prepare("UPDATE projetos SET tipo = COALESCE(tipo, ?), funcionalidades = COALESCE(funcionalidades, ?), futuras = COALESCE(futuras, ?), situacao = ? WHERE id = ?")
        ->execute([$pp['tipo'], $pp['funcionalidades'], $pp['futuras'], $p['situacao'] === 'desenvolvimento' ? $pp['situacao'] : $p['situacao'], $p['id']]);
    $st = db()->prepare('SELECT COUNT(*) FROM projeto_atualizacoes WHERE projeto_id = ?'); $st->execute([$p['id']]);
    if (!(int)$st->fetchColumn()) db()->prepare('INSERT INTO projeto_atualizacoes (projeto_id, versao, titulo, descricao, visivel_cliente, criado_em) VALUES (?,?,?,?,1,?)')->execute([$p['id'], '1.0', 'Versão de apresentação liberada', 'Primeira versão completa do novo site, com todas as páginas, formulário de proposta e blog.', $p['criado_em']]);
    criar_orcamento_conect($cid, (int)$p['id']);
}

/**
 * Preparação automática do banco no primeiro acesso ao site (sem tela de instalação).
 * Cria o banco (no XAMPP), as tabelas, o catálogo de soluções e a Conect Síndico. O administrador vem de
 * 'admin_inicial' em config/config.local.php (fora do Git). Roda uma vez só: depois grava storage/.instalado.
 */
function preparar_site_automaticamente(): void {
    $marca = __DIR__.'/../../storage/.instalado';
    if (is_file($marca)) return;
    // Trava: se dois visitantes chegarem juntos no primeiro acesso, só um prepara o banco.
    $trava = fopen(__DIR__.'/../../storage/.instalando.lock', 'c');
    if ($trava) flock($trava, LOCK_EX);
    try {
        if (is_file($marca)) return;
        $db = cfg('db');
        if (App\Core\Database::driver() === 'mysql') {
            // No XAMPP cria o banco; na hospedagem o banco já vem criado pelo painel (e o usuário não tem CREATE DATABASE).
            try {
                $srv = new PDO("mysql:host={$db['host']};port={$db['port']};charset={$db['charset']}", $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $srv->exec('CREATE DATABASE IF NOT EXISTS `'.str_replace('`', '', $db['name']).'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            } catch (PDOException $e) { /* banco criado pelo painel da hospedagem */ }
        }
        aplicar_migracoes(true);

        // Catálogo inicial de soluções (sem valores; edite em Catálogo). Só se ainda estiver vazio.
        if ((int)db()->query('SELECT COUNT(*) FROM sistemas')->fetchColumn() === 0) {
            $insS = db()->prepare('INSERT INTO sistemas (slug, nome, categoria, icone, resumo, descricao, para_quem, recursos, preco, mensalidade, prazo, imagem, destaque, ativo, ordem, criado_em) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,1,?,?)');
            foreach (conteudo('sistemas_iniciais') as $i => $si) {
                $insS->execute([$si['slug'], $si['nome'], $si['categoria'], $si['icone'], $si['resumo'], $si['descricao'], $si['para_quem'], $si['recursos'], $si['preco'] ?: null, $si['mensalidade'] ?: null, $si['prazo'], $si['imagem'] ?: null, $si['destaque'], $i, agora()]);
            }
        }

        // Primeiro cliente: Conect Síndico (o site dela já vem dentro do portal).
        criar_cliente_conect(SITE_INTERNO);

        criar_admin_inicial();
        audit('instalacao', 'banco preparado automaticamente no primeiro acesso');
        @file_put_contents($marca, date('c'));
    } finally {
        if ($trava) { flock($trava, LOCK_UN); fclose($trava); }
    }
}

/** Cria o administrador definido em config.local.php ('admin_inicial' => nome, email, senha), se ainda não houver nenhum. */
function criar_admin_inicial(): void {
    $a = cfg('admin_inicial');
    if (!is_array($a) || empty($a['email']) || empty($a['senha'])) return;
    if ((int)db()->query("SELECT COUNT(*) FROM usuarios WHERE perfil = 'admin'")->fetchColumn() > 0) return;
    db()->prepare('INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo, deve_trocar_senha, criado_em) VALUES (?,?,?,?,1,0,?)')
        ->execute([(string)($a['nome'] ?? 'Administrador'), mb_strtolower(trim((string)$a['email'])), hash_senha((string)$a['senha']), 'admin', agora()]);
}


/**
 * Segundo cliente: Amanda Rodrigues Advocacia (Porto Alegre/RS). O site fica em sites_clientes/amanda-rodrigues/site
 * e abre dentro do portal em sites/amanda-rodrigues/ (só para quem tem o projeto liberado). Sem login criado aqui:
 * crie o acesso da cliente no painel (Clientes → Amanda Rodrigues Advocacia → Logins).
 */
const SITE_AMANDA = 'sites/amanda-rodrigues/';
function criar_cliente_amanda(): int {
    $st = db()->prepare('SELECT id FROM clientes WHERE nome = ?'); $st->execute(['Amanda Rodrigues Advocacia']);
    $cid = (int)$st->fetchColumn();
    if (!$cid) {
        db()->prepare('INSERT INTO clientes (nome, localizacao, tipo_projeto, categoria, contato_nome, email, telefone, logo, cor, ativo, criado_em) VALUES (?,?,?,?,?,?,?,?,?,1,?)')
            ->execute(['Amanda Rodrigues Advocacia', 'Porto Alegre/RS', 'Site institucional', 'Advocacia', 'Dra. Amanda Rodrigues', 'faj.atendimento@gmail.com', '(51) 3515-3533', 'assets/img/clientes/amanda-rodrigues.svg', '#4A1425', agora()]);
        $cid = (int)db()->lastInsertId();
    }
    $st = db()->prepare('SELECT id FROM projetos WHERE slug = ?'); $st->execute(['amanda-rodrigues']);
    if (!(int)$st->fetchColumn()) {
        db()->prepare('INSERT INTO projetos (cliente_id, slug, nome, tipo, situacao, chamada, descricao, funcionalidades, futuras, url_previa, imagem, permitir_embed, ativo, ordem, criado_em, atualizado_em) VALUES (?,?,?,?,?,?,?,?,?,?,?,1,1,1,?,?)')
            ->execute([$cid, 'amanda-rodrigues', 'Site institucional Amanda Rodrigues Advocacia', 'Site institucional', 'aprovacao',
                'O novo site da Amanda Rodrigues Advocacia está pronto para você ver.',
                "Site institucional do escritório, nas cores e no monograma da marca: apresentação da advogada, áreas de atuação, diferenciais e contato.\nEsta é a versão de apresentação. Navegue à vontade: é assim que o site vai ficar.",
                "Página única com Início, O Escritório, Áreas de Atuação, Diferenciais e Contato\nCinco áreas: Direito Civil, Sucessório, Notarial e Registral, Empresarial e Público\nLayout adaptado para celular, tablet e computador\nFormulário de contato com envio por e-mail e alternativa pelo WhatsApp\nWhatsApp com mensagem pronta para cada área de atuação\nProteção contra spam e autorização de contato (LGPD)\nPolítica de privacidade e página de erro personalizada\nSEO: título, descrição, dados de escritório de advocacia e imagem de compartilhamento",
                "Blog com artigos jurídicos\nAgendamento de consulta online\nÁrea do cliente com documentos e andamento dos casos\nFormulários por área (inventário, contratos, empresas)",
                SITE_AMANDA, 'previa-amanda-rodrigues.jpg', agora(), agora()]);
        $pid = (int)db()->lastInsertId();
        db()->prepare('INSERT INTO projeto_atualizacoes (projeto_id, versao, titulo, descricao, visivel_cliente, criado_em) VALUES (?,?,?,?,1,?)')
            ->execute([$pid, '1.0', 'Versão de apresentação liberada', 'Primeira versão completa do site, com todas as seções, formulário de contato e WhatsApp.', agora()]);
    }
    return $cid;
}
