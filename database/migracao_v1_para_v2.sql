-- ============================================================================================
-- orcamento.net — migração manual v1 → v2 (MySQL/MariaDB, pelo phpMyAdmin)
-- Normalmente NÃO é preciso: a atualização roda sozinha no primeiro acesso (app/Core/Migracoes.php).
-- Use este script só se a atualização automática falhar. Nenhum dado é apagado.
-- 1) Rode primeiro o conteúdo de database/orcamento_net.sql (cria as tabelas novas; as existentes ficam como estão).
-- 2) Depois rode este arquivo.
-- ============================================================================================

ALTER TABLE usuarios ADD COLUMN cliente_id INT UNSIGNED NULL;
ALTER TABLE projetos ADD COLUMN cliente_id INT UNSIGNED NULL;
ALTER TABLE projetos ADD COLUMN tipo VARCHAR(60) NULL;
ALTER TABLE projetos ADD COLUMN situacao VARCHAR(30) NOT NULL DEFAULT 'desenvolvimento';
ALTER TABLE projetos ADD COLUMN funcionalidades TEXT NULL;
ALTER TABLE projetos ADD COLUMN futuras TEXT NULL;
ALTER TABLE projetos ADD COLUMN url_publicacao VARCHAR(255) NULL;
ALTER TABLE projetos ADD COLUMN permitir_embed TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE projetos ADD COLUMN atualizado_em DATETIME NULL;

-- Cada usuário cliente vira um cliente (empresa) e os projetos liberados para ele passam a ser desse cliente.
INSERT INTO clientes (nome, email, ativo, criado_em)
  SELECT DISTINCT COALESCE(NULLIF(u.empresa, ''), u.nome), u.email, 1, NOW() FROM usuarios u
  WHERE u.perfil = 'cliente' AND u.cliente_id IS NULL;
UPDATE usuarios u JOIN clientes c ON c.nome = COALESCE(NULLIF(u.empresa, ''), u.nome)
  SET u.cliente_id = c.id WHERE u.perfil = 'cliente' AND u.cliente_id IS NULL;
UPDATE projetos p JOIN usuario_projeto up ON up.projeto_id = p.id JOIN usuarios u ON u.id = up.usuario_id
  SET p.cliente_id = u.cliente_id WHERE p.cliente_id IS NULL AND u.cliente_id IS NOT NULL;

INSERT INTO migracoes (versao, aplicada_em) VALUES ('002_v2_multiprojeto', NOW());
-- Depois crie o arquivo storage/.versao_banco com o conteúdo: 2
