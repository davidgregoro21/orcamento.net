# Publicar na Hostinger — orcamento.net (com o site da Conect Síndico dentro)

Resultado final:
- **https://orcamento.net** → site da orcamento.net (soluções, planos, contato) e área do cliente (`/login`).
- **https://orcamento.net/sites/conect-sindico/** → site do Daniel, que roda **dentro** do portal.
  Só abre para quem entrou no portal com permissão (o cliente pelo botão "Visualizar meu site", ou você como administrador).
  Quem tentar abrir direto, sem login, é mandado para a tela de login.

Arquivo para enviar: **`orcamento.net-hostinger.zip`** (um só). Não precisa de subdomínio nem de um segundo site.

---

## 1. Domínio e PHP
1. hPanel → **Sites** → adicione o site **orcamento.net** (ou aponte o domínio para a Hostinger).
2. hPanel → **Avançado → Configuração do PHP** → escolha **PHP 8.2** ou mais novo.
3. hPanel → **Segurança → SSL** → instale o SSL grátis em `orcamento.net`.

## 2. Banco de dados
1. hPanel → **Bancos de dados → Gerenciamento** → crie um banco MySQL.
   Ex.: banco `u123456789_orcamento`, usuário `u123456789_orcamento`, uma senha forte.
2. Anote os três dados (nome do banco, usuário, senha).

## 3. E-mail
1. hPanel → **E-mails** → crie `contato@orcamento.net` (onde você recebe) e `nao-responda@orcamento.net` (que envia).
2. Anote a senha de `nao-responda@orcamento.net`.

## 4. Enviar o portal
1. hPanel → **Arquivos → Gerenciador de arquivos** → abra `public_html` e apague o `default.php`, se existir.
2. Envie `orcamento.net-hostinger.zip` para dentro de `public_html` e clique em **Extrair**.
   Dentro de `public_html` devem ficar as pastas `app`, `config`, `database`, `public`, `sites_clientes`, `storage` e o arquivo `.htaccess`.
3. Edite `public_html/config/config.local.php`:
   - `db` → nome do banco, usuário e senha do passo 2;
   - `contato` → seu nome e WhatsApp;
   - `mail` → `senha` da caixa `nao-responda@orcamento.net`.
   (O site da Conect usa automaticamente essas mesmas configurações — não há nada para editar nele.)
4. Abra **https://orcamento.net/instalar.php**:
   - preencha seu nome, e-mail e senha de administrador;
   - deixe marcado **"Cadastrar a Conect Síndico como primeiro cliente"**;
   - clique em **Instalar**.

## 4b. Alternativa: importar o banco pronto (sem usar o instalador)
1. hPanel → **Bancos de dados → phpMyAdmin** → abra o banco criado no passo 2 → aba **Importar** → escolha `database/orcamento_net_completo.sql`.
2. No Gerenciador de arquivos, crie o arquivo vazio `public_html/storage/.instalado` (assim o instalador não abre).
3. Entre com **davidgregorio21@gmail.com** e a senha provisória **Trocar@2026** — o sistema pede uma senha nova no primeiro acesso.
   O acesso do Daniel já vem pronto: **daniel@conectsindico.com.br** / **Conect@2026**.

## 5. Liberar para o Daniel
1. Entre em **https://orcamento.net/login** com o seu e-mail e senha.
2. **Clientes → Conect Síndico → aba Usuários** → nome e e-mail do Daniel → **Criar acesso**.
3. Copie a senha provisória (aparece uma vez só) e envie para ele com o endereço **https://orcamento.net/login**.
4. Ele entra, troca a senha e clica em **Visualizar meu site**.

## Conferência rápida
- `https://orcamento.net/sistemas` abre as soluções.
- `https://orcamento.net/sites/conect-sindico/` **sem login** → vai para o login do portal (correto).
- Logado como Daniel → **Visualizar meu site** mostra o site da Conect dentro do portal.
- `https://orcamento.net/config/config.local.php` e `https://orcamento.net/sites_clientes/` → **404** (pastas internas protegidas).
- Envie um pedido de teste em `https://orcamento.net/contato` e veja se chega em `contato@orcamento.net`.

## Atualizando uma instalação que já existe
- Envie o zip por cima (sem apagar `config/config.local.php` nem `storage/`). O banco se atualiza sozinho no primeiro acesso.
- Projeto da Conect que apontava para outro endereço (ex.: `conect_sindico.localhost` ou subdomínio) passa a abrir `sites/conect-sindico/` automaticamente.
- O orçamento da Conect já vem criado, **sem valores e oculto**: preencha em Orçamentos e clique em "Salvar e enviar ao cliente".

## Problemas comuns
| Sintoma | Causa provável | O que fazer |
| --- | --- | --- |
| "Falha na conexão com o banco" | dados errados em `config.local.php` | confira nome, usuário e senha do banco (host = `localhost`) |
| Erro 500 logo ao abrir | PHP antigo | selecione PHP 8.2+ no hPanel |
| Site da Conect volta sempre para o login | projeto não liberado para o cliente | Projetos → Conect → marque "Liberado para o cliente" |
| Pedido não chega por e-mail | senha da caixa de e-mail | confira `mail.senha`; os pedidos ficam salvos em **Interessados** mesmo assim |
| Precisa reinstalar | — | apague `public_html/storage/.instalado` e as tabelas do banco |

## Outros clientes no futuro
Cada site de cliente segue o mesmo modelo: código em `sites_clientes/<nome>/` e página pública em `public/sites/<nome>/`.
No cadastro do projeto, o "Endereço de demonstração" fica `sites/<nome>/`.

Quando o site da Conect for ao ar no domínio dele (conectsindico.com.br), a pasta `ConectSindico` original continua
servindo para isso: envie para a hospedagem do cliente com `'acesso_restrito' => ['ativo' => false]` e o `app_url` dele.
