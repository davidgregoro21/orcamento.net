<?php
/**
 * Instalação do portal (roda uma única vez): cria o banco, as tabelas, o administrador e o primeiro projeto.
 * Depois de concluída, grava storage/.instalado e esta página fica bloqueada.
 */
define('SEM_BANCO', true);
require __DIR__.'/../app/Core/bootstrap.php';

$marca = __DIR__.'/../storage/.instalado';
if (is_file($marca)) { http_response_code(403); exit('O portal já está instalado. Para reinstalar, apague storage/.instalado (e o banco).'); }

$erro = null; $feito = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $nome = trim((string)($_POST['nome'] ?? '')); $email = mb_strtolower(trim((string)($_POST['email'] ?? ''))); $senha = (string)($_POST['senha'] ?? '');
    // O site da Conect vem junto com o portal (public/sites/conect-sindico). Basta marcar para cadastrar o cliente.
    $urlConect = !empty($_POST['cadastrar_conect']) ? SITE_INTERNO : '';
    if (mb_strlen($nome) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erro = 'Informe seu nome e um e-mail válido.';
    elseif ($e2 = senha_fraca($senha)) $erro = $e2;
    else {
        try {
            $db = cfg('db');
            if (App\Core\Database::driver() === 'mysql') {
                // No XAMPP cria o banco; na hospedagem (Hostinger etc.) o banco já foi criado no painel e o usuário
                // não tem permissão de CREATE DATABASE — nesse caso só segue usando o banco existente.
                try {
                    $srv = new PDO("mysql:host={$db['host']};port={$db['port']};charset={$db['charset']}", $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                    $srv->exec('CREATE DATABASE IF NOT EXISTS `'.str_replace('`', '', $db['name']).'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
                } catch (PDOException $e) { /* banco criado pelo painel da hospedagem */ }
            }
            aplicar_migracoes(true);   // cria todas as tabelas (versão atual do banco)

            db()->prepare('INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo, deve_trocar_senha, criado_em) VALUES (?,?,?,?,1,0,?)')
                ->execute([$nome, $email, hash_senha($senha), 'admin', agora()]);
            $adminId = (int)db()->lastInsertId();

            // Catálogo inicial de soluções (sem valores; edite em Catálogo).
            $insS = db()->prepare('INSERT INTO sistemas (slug, nome, categoria, icone, resumo, descricao, para_quem, recursos, preco, mensalidade, prazo, imagem, destaque, ativo, ordem, criado_em) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,1,?,?)');
            foreach (conteudo('sistemas_iniciais') as $i => $si) {
                $insS->execute([$si['slug'], $si['nome'], $si['categoria'], $si['icone'], $si['resumo'], $si['descricao'], $si['para_quem'], $si['recursos'], $si['preco'] ?: null, $si['mensalidade'] ?: null, $si['prazo'], $si['imagem'] ?: null, $si['destaque'], $i, agora()]);
            }

            // Primeiro cliente real: Conect Síndico (dados informados por você) + projeto do site já desenvolvido.
            if ($urlConect !== '') criar_cliente_conect($urlConect, trim((string)($_POST['url_proposta'] ?? '')));
            audit('instalacao', 'portal instalado', $adminId);
            @file_put_contents($marca, date('c'));
            $feito = true;
        } catch (Throwable $e) {
            $erro = 'Não foi possível instalar: '.$e->getMessage();
        }
    }
}
?><!doctype html>
<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">
<title>Instalação | orcamento.net</title><link rel="stylesheet" href="<?=asset('assets/css/app.css')?>"></head>
<body class="guest"><div class="guest__grade">
<section class="guest__arte" aria-hidden="true"><div class="guest__arte-conteudo">
  <?php $logoClaro = true; require __DIR__.'/../app/Views/partials/logo.php'; ?>
  <h2>Configuração inicial do portal</h2>
  <p>Leva menos de um minuto. Depois disso, a página comercial e a área do cliente ficam no ar.</p>
  <ul class="mini-lista mini-lista--clara mini-lista--grande">
    <li><?=icone('check')?> Cria o banco de dados e as tabelas</li>
    <li><?=icone('check')?> Cadastra o seu acesso de administrador</li>
    <li><?=icone('check')?> Publica 6 sistemas no catálogo de vendas</li>
    <li><?=icone('check')?> Libera o site da Conect Síndico para o cliente</li>
  </ul>
</div></section>
<main class="guest__acesso"><div class="guest__cartao instalar__cartao">
<span class="guest__logo-mobile"><?php $logoClaro = false; require __DIR__.'/../app/Views/partials/logo.php'; ?></span>
<?php if ($feito): ?>
  <h1>Portal instalado!</h1>
  <p>Banco criado, administrador cadastrado<?= $urlConect ? ' e o projeto <b>Novo site Conect Síndico</b> já está na vitrine' : '' ?>.</p>
  <?php if ($urlConect): ?><p>Acesso do cliente: <b><?=e(DANIEL_EMAIL)?></b> · senha <b><?=e(DANIEL_SENHA_PADRAO)?></b></p><?php endif; ?>
  <ol class="lista-passos">
    <li>Entre com o seu e-mail e senha.</li>
    <li>Vá em <b>Clientes → Novo usuário</b>, cadastre o cliente e marque o sistema dele.</li>
    <li>Ajuste textos e valores dos sistemas à venda em <b>Catálogo</b>.</li>
    <li>Copie a mensagem com o acesso e envie para ele.</li>
  </ol>
  <a class="btn btn--primario btn--bloco" href="index.php?page=login">Ir para o login</a>
<?php else: ?>
  <h1>Instalação do portal</h1>
  <p class="mudo">Banco: <b><?=e(App\Core\Database::driver())?></b> · <?=e(cfg('db')['driver'] === 'sqlite' ? 'arquivo em storage/' : cfg('db')['host'].' / '.cfg('db')['name'])?> (ajuste em config/config.php)</p>
  <?php if ($erro): ?><div class="aviso aviso--erro"><?=e($erro)?></div><?php endif; ?>
  <form method="post" class="form">
    <?=csrf_field()?>
    <h2 class="cartao__titulo">Seu acesso de administrador</h2>
    <label class="campo"><span>Seu nome</span><input name="nome" required value="<?=e($_POST['nome'] ?? '')?>"></label>
    <label class="campo"><span>E-mail</span><input type="email" name="email" required value="<?=e($_POST['email'] ?? '')?>"></label>
    <label class="campo"><span>Senha</span><input type="password" name="senha" required minlength="8"><small>Mínimo de 8 caracteres, com letras e números.</small></label>
    <h2 class="cartao__titulo">Primeiro sistema de cliente</h2>
    <label class="campo campo--check"><input type="checkbox" name="cadastrar_conect" value="1"<?= ($_SERVER['REQUEST_METHOD'] !== 'POST' || !empty($_POST['cadastrar_conect'])) ? ' checked' : '' ?>> <span>Cadastrar a <b>Conect Síndico</b> como primeiro cliente (o site dela já vem dentro do portal)</span></label>
    <label class="campo"><span>Link da proposta comercial (opcional)</span><input name="url_proposta" value="<?=e($_POST['url_proposta'] ?? '')?>"></label>
    <button class="btn btn--primario btn--bloco" type="submit">Instalar</button>
  </form>
<?php endif; ?>
</div></main></div></body></html>
