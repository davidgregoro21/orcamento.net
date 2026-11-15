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
