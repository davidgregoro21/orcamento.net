<?php
/**
 * Configuração padrão (ambiente de desenvolvimento local / XAMPP).
 * Em produção NÃO altere este arquivo: crie config/config.local.php a partir de
 * config/config.local.example.php. Ele sobrescreve estes valores e fica fora do Git.
 */
return [
    'app_name' => 'orcamento.net',
    'timezone' => 'America/Sao_Paulo',
    'session_name' => 'orcamento_net_sessao',
    'session_idle_minutes' => 120,
    // 'desenvolvimento' | 'homologacao' | 'producao'. Em produção nenhum erro técnico aparece na tela.
    'ambiente' => 'desenvolvimento',
    'forcar_https' => false,
    'app_url' => '',
    'urls_amigaveis' => true,

    // Banco: 'mysql' (padrão, XAMPP/hospedagem) ou 'sqlite' (testes rápidos, sem servidor de banco).
    'db' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'name' => 'orcamento_net',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
        'sqlite_path' => __DIR__.'/../storage/orcamento_net.sqlite',
    ],

    // Chave que assina os links de prévia dos sites dos clientes. A MESMA chave vai no config do site do cliente
    // ('acesso_restrito' => ['chave' => ...]). Gere com: php -r "echo bin2hex(random_bytes(32));"
    'previa_chave' => 'dev-troque-esta-chave-em-producao-0000000000000000',
    // Validade do link de prévia gerado ao clicar em "Ver o site" (minutos). Depois de aberto, o site do cliente mantém o acesso por 8 h.
    'previa_validade_min' => 10,
    // Área do cliente: 'demo' = o cliente vê só "Projetos" e, ao entrar, já abre o site dele (Rascunho – Demo do projeto).
    // 'completa' = mostra também início, orçamentos, solicitações e arquivos.
    'area_cliente' => 'demo',

    // Contato exibido no site e destino dos pedidos de orçamento.
    'contato' => [
        'responsavel' => '',                    // seu nome
        'email' => 'contato@orcamento.net',
        'whatsapp' => '',                       // ex.: (54) 99999-9999
        'cidade' => 'Caxias do Sul/RS',
    ],
    'mail' => [
        'host' => '', 'port' => 587, 'seguranca' => 'tls', 'usuario' => '', 'senha' => '',
        'remetente' => 'nao-responda@orcamento.net', 'nome' => 'orcamento.net',
        'destino' => 'contato@orcamento.net',
    ],
    'limite_login_ip' => 30,                    // tentativas de login por IP a cada 5 minutos
];
