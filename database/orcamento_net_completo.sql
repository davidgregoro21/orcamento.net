-- ============================================================================
-- orcamento.net — banco de dados completo (estrutura + dados iniciais)
-- Gerado em 15/11/2026 02:22
--
-- Como usar (phpMyAdmin): crie o banco vazio, selecione-o, aba Importar, escolha este arquivo.
-- Depois crie storage/.instalado (arquivo vazio) para o instalador não abrir.
--
-- Acessos incluídos:
--   Administrador: davidgregorio21@gmail.com  /  senha provisória Trocar@2026 (o sistema pede para trocar no 1º acesso)
--   Cliente Conect: daniel@conectsindico.com.br  /  senha Conect@2026
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================================
-- orcamento.net — estrutura do banco v2 (MySQL / MariaDB: XAMPP e Hostinger)
-- Instalação nova: public/instalar.php executa este arquivo.
-- Banco v1 já instalado: a atualização roda sozinha no primeiro acesso (app/Core/Migracoes.php)
-- ou manualmente com database/migracao_v1_para_v2.sql.
-- ============================================================================================

-- Empresas atendidas. Cada cliente pode ter vários usuários, projetos, orçamentos e arquivos.
CREATE TABLE IF NOT EXISTS clientes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(160) NOT NULL,
  documento VARCHAR(30) NULL,                              -- CNPJ/CPF
  localizacao VARCHAR(120) NULL,                           -- ex.: Caxias do Sul/RS
  tipo_projeto VARCHAR(80) NULL,                           -- ex.: Site institucional
  categoria VARCHAR(160) NULL,                             -- ramo de atividade
  contato_nome VARCHAR(120) NULL,
  email VARCHAR(160) NULL,
  telefone VARCHAR(30) NULL,
  site_atual VARCHAR(255) NULL,
  logo VARCHAR(160) NULL,                                  -- arquivo em public/uploads/clientes
  cor VARCHAR(7) NULL,                                     -- cor da marca do cliente (#RRGGBB)
  observacoes TEXT NULL,                                   -- interno: o cliente nunca vê
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pessoas que entram no portal. perfil = admin (master) | cliente (vê só o próprio cliente_id).
CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT UNSIGNED NULL,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  empresa VARCHAR(120) NULL,
  senha_hash VARCHAR(255) NOT NULL,
  perfil VARCHAR(20) NOT NULL DEFAULT 'cliente',
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  deve_trocar_senha TINYINT(1) NOT NULL DEFAULT 1,
  ultimo_acesso_em DATETIME NULL,
  criado_em DATETIME NOT NULL,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Projetos (sites e sistemas) de cada cliente. ativo = liberado para o cliente ver.
CREATE TABLE IF NOT EXISTS projetos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT UNSIGNED NULL,
  slug VARCHAR(80) NOT NULL UNIQUE,
  nome VARCHAR(120) NOT NULL,
  cliente VARCHAR(120) NULL,                               -- legado v1 (texto); use cliente_id
  tipo VARCHAR(60) NULL,                                   -- Site institucional, Sistema administrativo...
  situacao VARCHAR(30) NOT NULL DEFAULT 'desenvolvimento', -- planejamento | desenvolvimento | aprovacao | publicado | pausado
  chamada VARCHAR(200) NULL,                               -- frase de destaque na área do cliente
  descricao TEXT NULL,
  funcionalidades TEXT NULL,                               -- já implementadas, uma por linha
  futuras TEXT NULL,                                       -- possibilidades de expansão, uma por linha
  url_previa VARCHAR(255) NOT NULL,                        -- demonstração (aberta com link assinado)
  url_publicacao VARCHAR(255) NULL,                        -- endereço definitivo, quando publicado
  url_proposta VARCHAR(255) NULL,
  imagem VARCHAR(160) NULL,                                -- prévia visual (public/uploads/projetos ou assets/img)
  permitir_embed TINYINT(1) NOT NULL DEFAULT 1,            -- mostrar dentro do portal (iframe)
  portfolio TINYINT(1) NOT NULL DEFAULT 0,                 -- aparece na página pública Portfólio
  portfolio_resumo VARCHAR(255) NULL,                      -- texto curto exibido no portfólio
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  ordem INT NOT NULL DEFAULT 0,
  criado_em DATETIME NOT NULL,
  atualizado_em DATETIME NULL,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Versões e histórico de atualizações de cada projeto.
CREATE TABLE IF NOT EXISTS projeto_atualizacoes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  projeto_id INT UNSIGNED NOT NULL,
  versao VARCHAR(30) NULL,
  titulo VARCHAR(160) NOT NULL,
  descricao TEXT NULL,
  url VARCHAR(255) NULL,                                   -- link de demonstração desta versão (opcional)
  visivel_cliente TINYINT(1) NOT NULL DEFAULT 1,
  criado_em DATETIME NOT NULL,
  FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Legado v1 (liberação por usuário). Mantida para não perder dados; o v2 usa projetos.cliente_id.
CREATE TABLE IF NOT EXISTS usuario_projeto (
  usuario_id INT UNSIGNED NOT NULL,
  projeto_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (usuario_id, projeto_id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orçamentos / propostas comerciais.
CREATE TABLE IF NOT EXISTS orcamentos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT UNSIGNED NOT NULL,
  projeto_id INT UNSIGNED NULL,
  numero VARCHAR(20) NOT NULL,                             -- ex.: 2026-0001
  titulo VARCHAR(160) NOT NULL,
  descricao TEXT NULL,
  condicoes TEXT NULL,                                     -- condições comerciais
  prazo VARCHAR(120) NULL,
  validade DATE NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'elaboracao',        -- elaboracao | enviado | aguardando | aprovado | recusado | concluido
  visivel_cliente TINYINT(1) NOT NULL DEFAULT 0,
  observacoes TEXT NULL,                                   -- interno
  emitido_em DATE NULL,
  respondido_em DATETIME NULL,
  resposta_cliente TEXT NULL,
  criado_em DATETIME NOT NULL,
  atualizado_em DATETIME NULL,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
  FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orcamento_itens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  orcamento_id INT UNSIGNED NOT NULL,
  descricao VARCHAR(200) NOT NULL,
  detalhes TEXT NULL,                                      -- funcionalidades incluídas, uma por linha
  quantidade DECIMAL(10,2) NOT NULL DEFAULT 1,
  valor_unitario DECIMAL(12,2) NULL,                       -- vazio = a definir
  ordem INT NOT NULL DEFAULT 0,
  FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Solicitações de alteração, melhorias e comentários do cliente.
CREATE TABLE IF NOT EXISTS solicitacoes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT UNSIGNED NOT NULL,
  projeto_id INT UNSIGNED NULL,
  usuario_id INT UNSIGNED NULL,
  tipo VARCHAR(20) NOT NULL DEFAULT 'alteracao',           -- alteracao | melhoria | erro | duvida
  titulo VARCHAR(160) NOT NULL,
  descricao TEXT NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'aberta',            -- aberta | andamento | concluida | recusada
  criado_em DATETIME NOT NULL,
  atualizado_em DATETIME NULL,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
  FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE SET NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS solicitacao_mensagens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  solicitacao_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NULL,
  mensagem TEXT NOT NULL,
  criado_em DATETIME NOT NULL,
  FOREIGN KEY (solicitacao_id) REFERENCES solicitacoes(id) ON DELETE CASCADE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Arquivos do cliente/projeto (guardados em storage/arquivos, fora da web; download só com permissão).
CREATE TABLE IF NOT EXISTS arquivos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT UNSIGNED NOT NULL,
  projeto_id INT UNSIGNED NULL,
  nome VARCHAR(200) NOT NULL,                              -- nome original
  arquivo VARCHAR(80) NOT NULL,                            -- nome gravado em disco (aleatório)
  tamanho INT UNSIGNED NOT NULL DEFAULT 0,
  mime VARCHAR(100) NULL,
  descricao VARCHAR(255) NULL,
  visivel_cliente TINYINT(1) NOT NULL DEFAULT 1,
  usuario_id INT UNSIGNED NULL,
  criado_em DATETIME NOT NULL,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
  FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recuperação de senha (token guardado só como hash, uso único, expira em 1 hora).
CREATE TABLE IF NOT EXISTS senha_reset (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expira_em DATETIME NOT NULL,
  usado_em DATETIME NULL,
  criado_em DATETIME NOT NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pedidos de orçamento do site público (interessados).
CREATE TABLE IF NOT EXISTS pedidos_orcamento (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL,
  telefone VARCHAR(20) NOT NULL,
  empresa VARCHAR(120) NULL,
  servico VARCHAR(80) NOT NULL,
  prazo VARCHAR(40) NULL,
  mensagem TEXT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'novo',              -- novo | em_contato | proposta | fechado | perdido
  anotacoes TEXT NULL,                                     -- interno: histórico da negociação
  atualizado_em DATETIME NULL,
  ip VARCHAR(45) NULL,
  criado_em DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catálogo público de soluções.
CREATE TABLE IF NOT EXISTS sistemas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(80) NOT NULL UNIQUE,
  nome VARCHAR(120) NOT NULL,
  categoria VARCHAR(60) NOT NULL,
  icone VARCHAR(30) NOT NULL DEFAULT 'sistema',
  resumo VARCHAR(255) NOT NULL,
  descricao TEXT NULL,
  para_quem VARCHAR(255) NULL,
  recursos TEXT NULL,
  preco VARCHAR(60) NULL,
  mensalidade VARCHAR(60) NULL,
  prazo VARCHAR(60) NULL,
  imagem VARCHAR(160) NULL,
  destaque TINYINT(1) NOT NULL DEFAULT 0,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  ordem INT NOT NULL DEFAULT 0,
  criado_em DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registro de acessos e atividades (login, visualizações, alterações).
CREATE TABLE IF NOT EXISTS auditoria (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NULL,
  acao VARCHAR(60) NOT NULL,
  detalhe VARCHAR(255) NULL,
  ip VARCHAR(45) NULL,
  criado_em DATETIME NOT NULL,
  INDEX (usuario_id), INDEX (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Controle de versões do banco (atualizações aplicadas).
CREATE TABLE IF NOT EXISTS migracoes (
  versao VARCHAR(40) NOT NULL PRIMARY KEY,
  aplicada_em DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ---------------------------------------------------------------------------
-- Dados iniciais
-- ---------------------------------------------------------------------------

-- clientes
INSERT INTO `clientes` (`id`, `nome`, `documento`, `localizacao`, `tipo_projeto`, `categoria`, `contato_nome`, `email`, `telefone`, `site_atual`, `logo`, `cor`, `observacoes`, `ativo`, `criado_em`) VALUES (1, 'Conect Síndico', NULL, 'Caxias do Sul/RS', 'Site institucional', 'Serviços de sindicância e gestão condominial', NULL, NULL, '(54) 3538-2146', 'https://conectsindico.com.br', 'assets/img/clientes/conect-sindico.png', '#34B1C0', NULL, 1, '2026-11-14 23:22:11');

-- usuarios
INSERT INTO `usuarios` (`id`, `cliente_id`, `nome`, `email`, `empresa`, `senha_hash`, `perfil`, `ativo`, `deve_trocar_senha`, `ultimo_acesso_em`, `criado_em`) VALUES (1, NULL, 'David Gregorio', 'davidgregorio21@gmail.com', NULL, '$2y$10$PkOiegrSvIBxXh/uKBBqsOlO9ygMqFXN8iWys7ElIkcODVc2VBJLe', 'admin', 1, 1, NULL, '2026-11-14 23:22:11');
INSERT INTO `usuarios` (`id`, `cliente_id`, `nome`, `email`, `empresa`, `senha_hash`, `perfil`, `ativo`, `deve_trocar_senha`, `ultimo_acesso_em`, `criado_em`) VALUES (2, 1, 'Daniel Mesquita de Almeida', 'daniel@conectsindico.com.br', 'Conect Síndico', '$2y$10$Uas5jmQtlQYQTXbOQVwrVu1ZCIXKGpB1JoK/unE2RntcowXOwxYhW', 'cliente', 1, 0, NULL, '2026-11-14 23:22:11');

-- projetos
INSERT INTO `projetos` (`id`, `cliente_id`, `slug`, `nome`, `cliente`, `tipo`, `situacao`, `chamada`, `descricao`, `funcionalidades`, `futuras`, `url_previa`, `url_publicacao`, `url_proposta`, `imagem`, `permitir_embed`, `portfolio`, `portfolio_resumo`, `ativo`, `ordem`, `criado_em`, `atualizado_em`) VALUES (1, 1, 'conect-sindico', 'Site institucional Conect Síndico', NULL, 'Site institucional', 'aprovacao', 'O novo site da Conect Síndico está pronto para você ver.', 'Reformulação completa do site conectsindico.com.br: layout novo nas cores da marca, conteúdo revisado, formulário de proposta e blog com os artigos atuais.\nEsta é a versão de apresentação. Navegue à vontade: é assim que o site vai ficar.', 'Páginas Início, Sobre, Serviços, Blog, Contato e Política de privacidade\nLayout adaptado para celular, tablet e computador\nFormulário de proposta com aviso por e-mail e cópia de segurança\nProteção contra spam e autorização de contato (LGPD)\nBotão de WhatsApp em todas as páginas\nBlog com 16 artigos e filtro por categoria\nLinks antigos do blog redirecionados (preserva o Google)\nSEO: título e descrição por página, mapa do site e dados de empresa local\nImagem de compartilhamento para WhatsApp e redes sociais', 'Portal do condômino com login\nPainel do síndico para vários condomínios\nMural de comunicados e documentos (atas, balancetes)\nReserva de áreas comuns\nChamados e ocorrências\nRelatório mensal para o conselho', 'sites/conect-sindico/', NULL, NULL, 'previa-conect-sindico.jpg', 1, 0, 'Site institucional com blog, formulário de proposta e SEO local para empresa de gestão condominial.', 1, 0, '2026-11-14 23:22:11', '2026-11-14 23:22:11');

-- projeto_atualizacoes
INSERT INTO `projeto_atualizacoes` (`id`, `projeto_id`, `versao`, `titulo`, `descricao`, `url`, `visivel_cliente`, `criado_em`) VALUES (1, 1, '1.0', 'Versão de apresentação liberada', 'Primeira versão completa do novo site, com todas as páginas, formulário de proposta e blog.', NULL, 1, '2026-11-14 23:22:11');

-- orcamentos
INSERT INTO `orcamentos` (`id`, `cliente_id`, `projeto_id`, `numero`, `titulo`, `descricao`, `condicoes`, `prazo`, `validade`, `status`, `visivel_cliente`, `observacoes`, `emitido_em`, `respondido_em`, `resposta_cliente`, `criado_em`, `atualizado_em`) VALUES (1, 1, 1, '2026-0001', 'Novo site institucional – Conect Síndico', 'Reformulação completa do site conectsindico.com.br.', NULL, '15 dias úteis após o envio de textos e fotos', NULL, 'elaboracao', 0, 'Preencher valores antes de enviar ao cliente.', NULL, NULL, NULL, '2026-11-14 23:22:11', '2026-11-14 23:22:11');

-- orcamento_itens
INSERT INTO `orcamento_itens` (`id`, `orcamento_id`, `descricao`, `detalhes`, `quantidade`, `valor_unitario`, `ordem`) VALUES (1, 1, 'Site institucional', '7 páginas: Início, Sobre, Serviços, Blog, Artigo, Contato e Privacidade\nLayout novo nas cores da marca, adaptado ao celular\nFormulário de proposta com LGPD e proteção contra spam\nWhatsApp em todas as páginas\nBlog com os 16 artigos migrados e endereços preservados\nSEO, mapa do site e dados de empresa local\nHTTPS, publicação e envio ao Google Search Console\nDuas rodadas de ajustes', 1, NULL, 0);
INSERT INTO `orcamento_itens` (`id`, `orcamento_id`, `descricao`, `detalhes`, `quantidade`, `valor_unitario`, `ordem`) VALUES (2, 1, 'Manutenção mensal (opcional)', 'Atualizações de segurança e backup\nAté 2 alterações de texto ou imagem por mês\nPublicação de 1 artigo enviado pelo cliente', 1, NULL, 1);

-- sistemas
INSERT INTO `sistemas` (`id`, `slug`, `nome`, `categoria`, `icone`, `resumo`, `descricao`, `para_quem`, `recursos`, `preco`, `mensalidade`, `prazo`, `imagem`, `destaque`, `ativo`, `ordem`, `criado_em`) VALUES (1, 'portal-do-condominio', 'Portal do Condomínio', 'Condomínios', 'predio', 'Comunicados, documentos, reservas e chamados para síndicos, administradoras e moradores.', 'Um portal com a marca da sua empresa para atender todos os condomínios da carteira em um só lugar. O síndico publica comunicados e documentos, os moradores reservam áreas comuns e abrem chamados pelo celular.', 'Síndicos profissionais, administradoras e condomínios', 'Painel para vários condomínios, blocos e unidades\nMural de comunicados com aviso por e-mail\nAtas, balancetes, regimento e convenção\nReserva de áreas comuns com calendário\nChamados e ocorrências com histórico\nRelatório mensal em PDF para o conselho\nFunciona no celular como aplicativo', NULL, NULL, NULL, NULL, 1, 1, 0, '2026-11-14 23:22:11');
INSERT INTO `sistemas` (`id`, `slug`, `nome`, `categoria`, `icone`, `resumo`, `descricao`, `para_quem`, `recursos`, `preco`, `mensalidade`, `prazo`, `imagem`, `destaque`, `ativo`, `ordem`, `criado_em`) VALUES (2, 'agenda-e-atendimentos', 'Agenda e Atendimentos', 'Saúde e serviços', 'calendario', 'Agenda por profissional, fila de atendimento, ficha do cliente e relatórios.', 'Organize a agenda de vários profissionais, confirme horários, acompanhe a fila do dia e mantenha o histórico de cada atendimento em um só sistema, com acesso controlado por perfil.', 'Clínicas, consultórios, escritórios e prestadores de serviço', 'Agenda por profissional e por sala\nEncaixes e horários livres\nFila de atendimento em tempo real\nFicha e histórico do cliente\nPerfis de acesso e auditoria\nIndicadores e relatórios em PDF', NULL, NULL, NULL, NULL, 1, 1, 1, '2026-11-14 23:22:11');
INSERT INTO `sistemas` (`id`, `slug`, `nome`, `categoria`, `icone`, `resumo`, `descricao`, `para_quem`, `recursos`, `preco`, `mensalidade`, `prazo`, `imagem`, `destaque`, `ativo`, `ordem`, `criado_em`) VALUES (3, 'orcamentos-e-pedidos', 'Orçamentos e Pedidos', 'Comércio e serviços', 'documento', 'Monte orçamentos em minutos, envie por WhatsApp ou e-mail e acompanhe a aprovação.', 'Cadastre produtos e serviços, gere orçamentos com a sua marca em PDF, envie com um clique e saiba quando o cliente aprovou. Os pedidos aprovados viram ordens de serviço.', 'Lojas, prestadores de serviço, gráficas e indústrias', 'Catálogo de produtos e serviços\nOrçamento em PDF com a sua marca\nEnvio por WhatsApp e e-mail\nAprovação online pelo cliente\nPedidos e ordens de serviço\nRelatório de vendas por período', NULL, NULL, NULL, NULL, 1, 1, 2, '2026-11-14 23:22:11');
INSERT INTO `sistemas` (`id`, `slug`, `nome`, `categoria`, `icone`, `resumo`, `descricao`, `para_quem`, `recursos`, `preco`, `mensalidade`, `prazo`, `imagem`, `destaque`, `ativo`, `ordem`, `criado_em`) VALUES (4, 'portal-do-cliente', 'Portal do Cliente', 'Escritórios', 'portal', 'Área exclusiva com login para seus clientes acessarem documentos, solicitações e avisos.', 'Chega de documentos perdidos no e-mail. Cada cliente entra com login próprio e encontra arquivos, avisos e solicitações em um só lugar, com registro de quem acessou o quê.', 'Contabilidades, advocacias, consultorias e imobiliárias', 'Login individual por cliente\nEnvio e download de documentos\nSolicitações com prazo e status\nAvisos e notificações por e-mail\nRegistro de acessos (LGPD)', NULL, NULL, NULL, NULL, 0, 1, 3, '2026-11-14 23:22:11');
INSERT INTO `sistemas` (`id`, `slug`, `nome`, `categoria`, `icone`, `resumo`, `descricao`, `para_quem`, `recursos`, `preco`, `mensalidade`, `prazo`, `imagem`, `destaque`, `ativo`, `ordem`, `criado_em`) VALUES (5, 'chamados-e-protocolos', 'Chamados e Protocolos', 'Empresas e órgãos', 'caixa', 'Abertura, encaminhamento e acompanhamento de chamados com protocolo e prazo.', 'Cada solicitação ganha um número de protocolo, um responsável e um prazo. Gestores acompanham tudo em painéis e relatórios, com trilha de auditoria completa.', 'Empresas, associações e órgãos públicos', 'Protocolo automático\nEncaminhamento por setor e responsável\nPrazos e alertas de atraso\nPainel com indicadores\nAuditoria de todas as ações', NULL, NULL, NULL, NULL, 0, 1, 4, '2026-11-14 23:22:11');
INSERT INTO `sistemas` (`id`, `slug`, `nome`, `categoria`, `icone`, `resumo`, `descricao`, `para_quem`, `recursos`, `preco`, `mensalidade`, `prazo`, `imagem`, `destaque`, `ativo`, `ordem`, `criado_em`) VALUES (6, 'vistorias-e-checklists', 'Vistorias e Checklists', 'Manutenção', 'ferramenta', 'Checklists pelo celular com fotos, assinatura e relatório em PDF na hora.', 'Equipes em campo preenchem checklists pelo celular, anexam fotos e geram o relatório na hora. Ideal para manutenção predial, inspeções e entregas de obra.', 'Manutenção predial, construtoras e prestadores técnicos', 'Modelos de checklist por norma\nFotos e observações por item\nRelatório em PDF automático\nHistórico por local ou equipamento\nAgenda de manutenções preventivas', NULL, NULL, NULL, NULL, 0, 1, 5, '2026-11-14 23:22:11');

-- migracoes
INSERT INTO `migracoes` (`versao`, `aplicada_em`) VALUES ('002_v2_multiprojeto', '2026-11-14 23:22:11');
INSERT INTO `migracoes` (`versao`, `aplicada_em`) VALUES ('003_conect_dentro_do_portal', '2026-11-14 23:22:11');
INSERT INTO `migracoes` (`versao`, `aplicada_em`) VALUES ('004_usuario_daniel', '2026-11-14 23:22:11');
INSERT INTO `migracoes` (`versao`, `aplicada_em`) VALUES ('005_portfolio', '2026-11-14 23:22:11');
INSERT INTO `migracoes` (`versao`, `aplicada_em`) VALUES ('006_pedidos_anotacoes', '2026-11-14 23:22:11');

SET FOREIGN_KEY_CHECKS = 1;
