<?php
/**
 * Rotas: página => [arquivo em app/Views, acesso, layout, título].
 * acesso: 'publico' | 'login' (qualquer logado) | 'cliente' (usuário de cliente) | 'admin' (administrador master).
 * layout: 'site' (público) | 'guest' (login) | 'app' (área logada, com menu lateral).
 * Separação: app/Views/pages (site público), app/Views/cliente (área do cliente), app/Views/admin (painel master).
 */
return [
 // --- Site público ---
 'inicio'=>['pages/inicio.php', 'publico', 'site', null],
 'portfolio'=>['pages/portfolio.php', 'publico', 'site', 'Portfólio de serviços'],
 'sistemas'=>['pages/sistemas.php', 'publico', 'site', 'Soluções'],
 'sistema'=>['pages/sistema.php', 'publico', 'site', 'Solução'],
 'servicos'=>['pages/servicos.php', 'publico', 'site', 'Serviços e planos'],
 'sobre'=>['pages/sobre.php', 'publico', 'site', 'Sobre'],
 'contato'=>['pages/contato.php', 'publico', 'site', 'Contato e orçamento'],
 'nao_encontrada'=>['pages/nao_encontrada.php', 'publico', 'site', 'Página não encontrada'],
 // --- Acesso ---
 'login'=>['pages/login.php', 'publico', 'guest', 'Entrar'],
 'esqueci_senha'=>['pages/esqueci_senha.php', 'publico', 'guest', 'Esqueci minha senha'],
 'redefinir_senha'=>['pages/redefinir_senha.php', 'publico', 'guest', 'Nova senha'],
 'minha_senha'=>['pages/minha_senha.php', 'login', 'app', 'Minha senha'],
 // --- Área exclusiva do cliente ---
 'painel'=>['cliente/painel.php', 'cliente', 'app', 'Início'],
 'projetos'=>['cliente/projetos.php', 'cliente', 'app', 'Meus projetos'],
 'projeto'=>['cliente/projeto.php', 'cliente', 'app', 'Meu projeto'],
 'visualizar'=>['cliente/visualizar.php', 'login', 'app', 'Visualizar meu site'],
 'orcamentos'=>['cliente/orcamentos.php', 'cliente', 'app', 'Meus orçamentos'],
 'orcamento'=>['cliente/orcamento.php', 'login', 'app', 'Orçamento'],
 'solicitacoes'=>['cliente/solicitacoes.php', 'cliente', 'app', 'Solicitações'],
 'solicitacao'=>['cliente/solicitacao.php', 'cliente', 'app', 'Solicitação'],
 'arquivos'=>['cliente/arquivos.php', 'cliente', 'app', 'Meus arquivos'],
 // --- Painel administrativo master ---
 'admin'=>['admin/painel.php', 'admin', 'app', 'Painel'],
 'admin_clientes'=>['admin/clientes.php', 'admin', 'app', 'Clientes'],
 'admin_cliente'=>['admin/cliente.php', 'admin', 'app', 'Cliente'],
 'admin_projetos'=>['admin/projetos.php', 'admin', 'app', 'Projetos'],
 'admin_projeto'=>['admin/projeto.php', 'admin', 'app', 'Projeto'],
 'admin_orcamentos'=>['admin/orcamentos.php', 'admin', 'app', 'Orçamentos'],
 'admin_orcamento'=>['admin/orcamento.php', 'admin', 'app', 'Orçamento'],
 'admin_solicitacoes'=>['admin/solicitacoes.php', 'admin', 'app', 'Solicitações'],
 'admin_solicitacao'=>['admin/solicitacao.php', 'admin', 'app', 'Solicitação'],
 'admin_arquivos'=>['admin/arquivos.php', 'admin', 'app', 'Arquivos'],
 'admin_usuarios'=>['admin/usuarios.php', 'admin', 'app', 'Usuários'],
 'admin_atividades'=>['admin/atividades.php', 'admin', 'app', 'Atividades'],
 'admin_pedidos'=>['admin/pedidos.php', 'admin', 'app', 'Interessados'],
 'admin_catalogo'=>['admin/catalogo.php', 'admin', 'app', 'Catálogo de soluções'],
];
