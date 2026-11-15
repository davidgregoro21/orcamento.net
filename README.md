# orcamento.net — plataforma de apresentação, clientes, orçamentos e demonstração de projetos (v2)

PHP 8.1+ e MySQL/MariaDB, sem frameworks nem dependências externas, no mesmo padrão dos sistemas
(`public/index.php` como controlador, `app/Core`, `app/Views`, `config.php` + `config.local.php`).

## Áreas
| Área | Quem acessa | Onde está o código |
| --- | --- | --- |
| Site público (Início, Soluções, Planos, Sobre, Contato) | qualquer visitante | `app/Views/pages` |
| Área exclusiva do cliente | usuários de um cliente | `app/Views/cliente` |
| Painel administrativo master | administradores | `app/Views/admin` |

### Painel master (`/admin`)
Painel com indicadores e acessos recentes · **Clientes** (ficha com abas: usuários, projetos, orçamentos,
solicitações, arquivos, acessos; criar login, redefinir senha, bloquear, desativar, excluir) · **Projetos**
(endereço de demonstração, publicação, situação, funcionalidades, possibilidades de expansão, imagem, versões e
histórico, liberar/ocultar) · **Orçamentos** (itens com funcionalidades, valores individuais, total, condições,
prazo, validade, status, envio ao cliente) · **Solicitações** (conversa com o cliente) · **Arquivos** (upload por
cliente/projeto, visível ou interno) · **Usuários** · **Atividades** (histórico de acessos) · **Interessados**
(pedidos do site, com "converter em cliente") · **Catálogo** de soluções.

### Área do cliente (`/painel`)
Boas-vindas com a identidade do cliente · **Meu projeto** (descrição, imagem, situação, datas, funcionalidades,
próximos passos possíveis com botão "Tenho interesse", histórico de atualizações) · **Visualizar meu site** (iframe
com tamanhos computador/tablet/celular e "Abrir em nova aba") · **Meus orçamentos** (proposta completa, aprovar ou
recusar, imprimir/PDF) · **Solicitar alterações** · **Meus arquivos** · Minha senha.

## Banco de dados
- `database/orcamento_net.sql` — estrutura completa (v2): clientes, usuarios, projetos, projeto_atualizacoes,
  orcamentos, orcamento_itens, solicitacoes, solicitacao_mensagens, arquivos, senha_reset, pedidos_orcamento,
  sistemas, auditoria, migracoes.
- **Atualização automática**: quem já tinha a v1 instalada não precisa fazer nada. No primeiro acesso,
  `app/Core/Migracoes.php` cria o que falta e converte os dados (usuários-cliente viram clientes, projetos são
  ligados a eles). Nada é apagado. Manual, se precisar: `database/migracao_v1_para_v2.sql`.
- Primeiro cliente real: **Conect Síndico** (Caxias do Sul/RS, Site institucional, Serviços de sindicância e gestão
  condominial), com o projeto do site, histórico e um orçamento **em elaboração, sem valores e oculto do cliente**
  (`app/Core/Sementes.php`).

## Segurança
Senhas com `password_hash` · sessão com cookie HttpOnly/SameSite, expiração por inatividade e encerramento ·
CSRF em todos os formulários · consultas preparadas (PDO) · saída escapada (XSS) · **toda permissão é verificada no
servidor** (`registro_do_cliente()` filtra por `cliente_id`) · cliente desativado perde o acesso na hora ·
recuperação de senha por link de uso único (1 h, guardado como hash) · limite de tentativas de login ·
arquivos fora da pasta pública, download só com permissão · uploads de imagem validados e sem execução de scripts ·
registro de acessos e ações (`auditoria`).

## Sites dos clientes dentro do portal
O site da Conect Síndico roda **dentro** do orcamento.net, em `/sites/conect-sindico/`:
- código (fora da pasta pública): `sites_clientes/conect-sindico/` (app, config, storage);
- página pública e arquivos estáticos: `public/sites/conect-sindico/`.

Ele só abre para quem entrou pelo portal: o botão "Visualizar meu site" gera um link assinado (HMAC, 10 min) e o site
mantém o acesso por 8 h. A chave, o ambiente e o e-mail são lidos da configuração do próprio portal — nada a configurar
no site do cliente. No cadastro do projeto, o endereço de demonstração é `sites/conect-sindico/`.
Endereços externos (https://...) continuam aceitos para outros projetos.

## Instalação local (XAMPP)
`http://orcamento.localhost/` → instalação (banco, administrador, catálogo e Conect Síndico).
Publicação na Hostinger: veja `PUBLICAR_HOSTINGER.md`.
