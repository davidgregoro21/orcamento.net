<?php
// A chave dos links de prévia vem do próprio portal orcamento.net (config/config.php + config.local.php), então nunca fica diferente.
$portal = (static function (): array {
    $raiz = dirname(__DIR__, 3).'/config';
    $c = is_file($raiz.'/config.php') ? (array)require $raiz.'/config.php' : [];
    if (is_file($raiz.'/config.local.php')) $c = array_replace_recursive($c, (array)require $raiz.'/config.local.php');
    return $c;
})();
/**
 * Configuração padrão (ambiente de desenvolvimento local / XAMPP).
 * Em produção NÃO altere este arquivo: crie config/config.local.php a partir de
 * config/config.local.example.php. Ele sobrescreve estes valores e fica fora do Git.
 */
return [
    'app_name' => 'Conect Síndico',
    'timezone' => 'America/Sao_Paulo',
    'session_name' => 'conect_sindico_sessao',
    // 'desenvolvimento' | 'homologacao' | 'producao'. Em produção nenhum erro técnico aparece na tela.
    'ambiente' => (string)($portal['ambiente'] ?? 'desenvolvimento'),   // segue o portal
    // Em produção: true redireciona para HTTPS e envia HSTS. (Em localhost nunca força.)
    'forcar_https' => (bool)($portal['forcar_https'] ?? false),          // segue o portal
    // Endereço público do site, sem barra no final (usado em canonical, Open Graph e sitemap). Vazio = detecta pelo acesso.
    'app_url' => '',
    // Endereços amigáveis (/servicos, /blog/slug). Exige mod_rewrite (public/.htaccess). false = index.php?page=...
    'urls_amigaveis' => true,

    // --- Dados da empresa (aparecem no cabeçalho, rodapé, contato e dados estruturados do Google) ---
    'empresa' => [
        'nome' => 'Conect Síndico Gestão de Condomínios',
        'nome_curto' => 'Conect Síndico',
        'gestor' => 'Daniel Mesquita de Almeida',
        'telefone' => '(54) 3538-2146',
        'whatsapp' => '(54) 99143-2200',
        'email' => 'contato@conectsindico.com.br',          // CONFIRMAR com o cliente
        'endereco' => 'Av. Therezinha Pauletti Sanvitto, 457',
        'bairro' => 'Villaggio Iguatemi',
        'cidade' => 'Caxias do Sul',
        'uf' => 'RS',
        'cep' => '',                                         // CONFIRMAR com o cliente
        'regiao' => 'Caxias do Sul e região',
        'instagram' => '',                                   // ex.: https://instagram.com/conectsindico
        'facebook' => '',
        'linkedin' => '',
        // Área do condômino (sistema atual). Troque quando o portal novo estiver no ar.
        'portal_url' => 'https://conectsindico.com.br/portal',
        // Endereço que recebe o login da Área do condômino (sistema atual de condôminos). A página nova envia e-mail e senha para cá.
        'portal_login_acao' => 'http://conectsindico.com.br/portal/dologin',
    ],

    // Mensagem pronta que abre no WhatsApp ao clicar nos botões do site.
    'whatsapp_mensagem' => 'Olá! Vim pelo site e gostaria de uma proposta de síndico profissional para o meu condomínio.',

    // Servidor de e-mail do formulário de contato. Sem 'host', as mensagens ficam gravadas em storage/emails (modo de teste).
    // Enquanto o site roda dentro do orcamento.net, usa o mesmo servidor de e-mail do portal.
    'mail' => [
        'host' => (string)($portal['mail']['host'] ?? ''), 'port' => (int)($portal['mail']['port'] ?? 587),
        'seguranca' => (string)($portal['mail']['seguranca'] ?? 'tls'),
        'usuario' => (string)($portal['mail']['usuario'] ?? ''), 'senha' => (string)($portal['mail']['senha'] ?? ''),
        'remetente' => (string)($portal['mail']['remetente'] ?? 'site@conectsindico.com.br'), 'nome' => 'Site Conect Síndico',
        'destino' => 'contato@conectsindico.com.br',        // quem recebe os contatos do site
    ],
    // Prévia restrita pelo portal orcamento.net. Enquanto 'ativo' => true, só quem você liberou no portal consegue abrir o site.
    // 'chave' deve ser IGUAL a 'previa_chave' do portal; 'projeto' igual ao identificador do projeto cadastrado lá.
    'acesso_restrito' => [
        'ativo' => true,
        'chave' => (string)($portal['previa_chave'] ?? ''),
        'projeto' => 'conect-sindico',
        'portal_login' => '',   // vazio = login do próprio orcamento.net onde o site está
        'horas' => 8,                                             // tempo que o acesso fica aberto depois de entrar
    ],
    // Limite de envios do formulário por IP (anti-spam): quantidade por janela de minutos.
    'limite_contato' => ['quantidade' => 5, 'minutos' => 30],
];
