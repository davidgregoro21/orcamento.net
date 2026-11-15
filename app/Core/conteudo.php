<?php
/**
 * Conteúdo da página comercial (orcamento.net). Para mudar textos, serviços ou valores, edite aqui.
 */
return [
    // Faixa de números/garantias logo abaixo do topo.
    'garantias_topo' => [
        ['valor' => 'Orçamento grátis', 'texto' => 'sem compromisso'],
        ['valor' => 'Proposta clara', 'texto' => 'escopo e valores por escrito'],
        ['valor' => 'Prévia no portal', 'texto' => 'você aprova antes de ir ao ar'],
        ['valor' => '100% seu', 'texto' => 'código, domínio e dados'],
    ],

    // Para quem (segmentos atendidos).
    'segmentos' => [
        ['icone' => 'predio', 'titulo' => 'Condomínios e administradoras'],
        ['icone' => 'balanca', 'titulo' => 'Escritórios e consultorias'],
        ['icone' => 'pessoas', 'titulo' => 'Clínicas e saúde'],
        ['icone' => 'caixa', 'titulo' => 'Comércio e serviços'],
        ['icone' => 'engrenagem', 'titulo' => 'Indústria e logística'],
        ['icone' => 'escudo', 'titulo' => 'Órgãos e associações'],
    ],

    // Problema → solução.
    'problemas' => [
        ['dor' => 'O site é antigo, lento e aparece "Não seguro" no navegador.', 'solucao' => 'Site novo com HTTPS, rápido no celular e pronto para o Google.'],
        ['dor' => 'Clientes não encontram você nem conseguem pedir orçamento.', 'solucao' => 'Formulário, WhatsApp em todas as páginas e pedidos chegando no seu e-mail.'],
        ['dor' => 'A rotina depende de planilhas, papéis e mensagens soltas.', 'solucao' => 'Sistema sob medida com cadastros, painel e relatórios em um só lugar.'],
        ['dor' => 'Você paga pelo projeto e não sabe o que vai receber.', 'solucao' => 'Proposta com escopo fechado e prévia no portal antes de ir ao ar.'],
    ],

    'servicos' => [
        ['icone' => 'site', 'titulo' => 'Sites institucionais', 'texto' => 'Site profissional que passa confiança e gera contatos.', 'itens' => ['Layout exclusivo, adaptado ao celular', 'Formulário + WhatsApp', 'SEO, HTTPS e LGPD'], 'a_partir' => '', 'prazo' => ''],
        ['icone' => 'sistema', 'titulo' => 'Sistemas web sob medida', 'texto' => 'Organize a rotina da empresa com um sistema feito para ela.', 'itens' => ['Cadastros, painéis e relatórios', 'Perfis de acesso e auditoria', 'Funciona no navegador e no celular'], 'a_partir' => '', 'prazo' => ''],
        ['icone' => 'portal', 'titulo' => 'Portais e área do cliente', 'texto' => 'Seus clientes acessam tudo com login próprio.', 'itens' => ['Documentos e comunicados', 'Chamados e reservas', 'Acesso seguro por perfil'], 'a_partir' => '', 'prazo' => ''],
        ['icone' => 'ferramenta', 'titulo' => 'Manutenção e evolução', 'texto' => 'Seu site ou sistema sempre atualizado e seguro.', 'itens' => ['Atualizações de segurança', 'Backup e monitoramento', 'Ajustes e novas funções'], 'a_partir' => '', 'prazo' => ''],
        ['icone' => 'nuvem', 'titulo' => 'Hospedagem, domínio e e-mail', 'texto' => 'Infraestrutura configurada do jeito certo.', 'itens' => ['Servidor e HTTPS', 'E-mail profissional', 'Migração sem perder dados'], 'a_partir' => '', 'prazo' => ''],
        ['icone' => 'engrenagem', 'titulo' => 'Integrações e automação', 'texto' => 'Menos trabalho manual, menos erro.', 'itens' => ['Planilhas e APIs', 'WhatsApp e e-mail automáticos', 'Relatórios gerados sozinhos'], 'a_partir' => '', 'prazo' => ''],
    ],

    // Portfólio de serviços (página /portfolio e resumo na página inicial). Edite à vontade: cada grupo vira um bloco.
    'portfolio' => [
        ['id' => 'sites', 'icone' => 'site', 'titulo' => 'Sites e presença digital', 'texto' => 'Sua empresa encontrada no Google e pronta para receber contatos.', 'visual' => 'site',
            'chamada' => 'Um site que vende por você, até enquanto você dorme.',
            'pitch' => 'Antes de comprar, o seu cliente pesquisa no Google. Criamos um site rápido, bonito no celular e pensado para transformar visita em pedido de orçamento, ligação ou mensagem.',
            'ganhos' => ['Mais pedidos de orçamento chegando', 'Sua empresa aparecendo no Google da região', 'Confiança logo na primeira visita'],
            'ideal' => ['Prestadores de serviço', 'Comércio local', 'Profissionais liberais', 'Condomínios'], 'itens' => [
            ['icone' => 'site', 'titulo' => 'Site institucional', 'texto' => 'Páginas da empresa, serviços, equipe e contato, no celular e no computador.'],
            ['icone' => 'foguete', 'titulo' => 'Landing page de campanha', 'texto' => 'Página única para anúncio, lançamento ou captação de clientes.'],
            ['icone' => 'caixa', 'titulo' => 'Loja virtual e catálogo', 'texto' => 'Produtos com fotos, carrinho ou pedido direto pelo WhatsApp.'],
            ['icone' => 'documento', 'titulo' => 'Blog e conteúdo', 'texto' => 'Artigos que trazem visitas do Google, com painel para publicar.'],
            ['icone' => 'local', 'titulo' => 'SEO e Google Meu Negócio', 'texto' => 'Título, descrição, mapa do site e dados de empresa local.'],
            ['icone' => 'ferramenta', 'titulo' => 'Reforma de site antigo', 'texto' => 'Layout novo sem perder os endereços que já estão no Google.'],
        ]],
        ['id' => 'automacoes', 'icone' => 'engrenagem', 'titulo' => 'Automações', 'texto' => 'Tarefas repetitivas feitas sozinhas, sem erro e a qualquer hora.', 'visual' => 'whatsapp',
            'chamada' => 'Sua empresa trabalhando sozinha, 24 horas por dia.',
            'pitch' => 'Responder sempre as mesmas perguntas, lembrar cliente de pagamento, confirmar horário, montar relatório no fim do mês. Colocamos a tecnologia para fazer isso por você, sem esquecer nada.',
            'ganhos' => ['Horas livres toda semana', 'Nenhum cliente fica sem resposta', 'Menos esquecimento e menos inadimplência'],
            'ideal' => ['Quem atende pelo WhatsApp', 'Agendas cheias', 'Cobrança mensal', 'Quem vive em planilhas'], 'itens' => [
            ['icone' => 'whatsapp', 'titulo' => 'Atendimento automático no WhatsApp', 'texto' => 'Respostas, menu de opções e encaminhamento para a pessoa certa.'],
            ['icone' => 'email', 'titulo' => 'E-mails e avisos automáticos', 'texto' => 'Confirmações, lembretes, cobranças e boas-vindas disparados no momento certo.'],
            ['icone' => 'calendario', 'titulo' => 'Agendamento online', 'texto' => 'O cliente escolhe o horário e recebe a confirmação e o lembrete.'],
            ['icone' => 'cifrao', 'titulo' => 'Cobranças e financeiro', 'texto' => 'Boletos, Pix e lembretes de vencimento, com baixa automática.'],
            ['icone' => 'grafico', 'titulo' => 'Relatórios que se montam sozinhos', 'texto' => 'Números do mês em PDF ou planilha, enviados por e-mail.'],
            ['icone' => 'api', 'titulo' => 'Integração entre sistemas', 'texto' => 'Planilhas, ERPs, CRMs e APIs conversando sem copiar e colar.'],
            ['icone' => 'documento', 'titulo' => 'Formulários e documentos', 'texto' => 'Formulários que viram contrato, proposta ou ficha em PDF.'],
            ['icone' => 'ia', 'titulo' => 'Ferramentas com inteligência artificial', 'texto' => 'Resumo de documentos, triagem de pedidos e respostas sugeridas.'],
        ]],
        ['id' => 'sistemas', 'icone' => 'sistema', 'titulo' => 'Sistemas e portais', 'texto' => 'A rotina da empresa organizada em um só lugar, com login e permissões.', 'visual' => 'painel',
            'chamada' => 'Chega de planilha, papel e mensagem perdida.',
            'pitch' => 'Um sistema feito do jeito que a sua empresa trabalha: cada pessoa com o seu login, tudo registrado e os números na tela na hora em que você precisa, no computador ou no celular.',
            'ganhos' => ['Controle de toda a operação em uma tela', 'Informação disponível em qualquer lugar', 'Cada pessoa vê só o que precisa'],
            'ideal' => ['Equipes', 'Síndicos e administradoras', 'Escritórios', 'Clínicas'], 'itens' => [
            ['icone' => 'painel', 'titulo' => 'Sistema administrativo sob medida', 'texto' => 'Cadastros, painéis e relatórios com os campos que o seu negócio usa.'],
            ['icone' => 'portal', 'titulo' => 'Área do cliente com login', 'texto' => 'Documentos, avisos, solicitações e histórico para cada cliente.'],
            ['icone' => 'predio', 'titulo' => 'Portal para condomínios', 'texto' => 'Comunicados, atas, reservas e chamados para síndicos e moradores.'],
            ['icone' => 'pessoas', 'titulo' => 'Controle de clientes (CRM)', 'texto' => 'Contatos, negociações, tarefas e próximos passos de cada cliente.'],
            ['icone' => 'mensagem', 'titulo' => 'Chamados e protocolos', 'texto' => 'Cada pedido com número, responsável, prazo e histórico.'],
            ['icone' => 'grafico', 'titulo' => 'Dashboards gerenciais', 'texto' => 'Indicadores da empresa atualizados em tempo real.'],
        ]],
        ['id' => 'suporte', 'icone' => 'nuvem', 'titulo' => 'Infraestrutura e suporte', 'texto' => 'Tudo funcionando, seguro e com backup, sem você se preocupar.', 'visual' => 'status',
            'chamada' => 'Tecnologia funcionando, sem dor de cabeça.',
            'pitch' => 'Cuidamos do domínio, da hospedagem, do e-mail e da segurança. Você não precisa entender de servidor: se algo precisar de atenção, a gente resolve.',
            'ganhos' => ['Site seguro, atualizado e com backup', 'E-mail com o nome da sua empresa', 'Um contato só para resolver tudo'],
            'ideal' => ['Empresas sem TI própria', 'Sites que já existem', 'Quem quer e-mail profissional'], 'itens' => [
            ['icone' => 'nuvem', 'titulo' => 'Hospedagem e domínio', 'texto' => 'Servidor, domínio e HTTPS configurados do jeito certo.'],
            ['icone' => 'email', 'titulo' => 'E-mail profissional', 'texto' => 'voce@suaempresa.com.br no celular e no computador.'],
            ['icone' => 'escudo', 'titulo' => 'Segurança e LGPD', 'texto' => 'Proteção contra spam e invasões, política de privacidade e consentimento.'],
            ['icone' => 'ferramenta', 'titulo' => 'Manutenção mensal', 'texto' => 'Atualizações, backup, monitoramento e pequenos ajustes.'],
        ]],
    ],

    // Opções de escolha (valores em branco = "Sob consulta"; preencha 'preco' quando definir).
    'pacotes' => [
        ['nome' => 'Essencial', 'preco' => '', 'detalhe' => '', 'para' => 'Sites simples, institucionais e páginas de apresentação', 'destaque' => false,
         'itens' => ['Site institucional ou página de apresentação', 'Formulário de contato e WhatsApp', 'Adaptado a celular e computador', 'SEO, HTTPS e LGPD', 'Publicação e configuração do domínio']],
        ['nome' => 'Profissional', 'preco' => '', 'detalhe' => '', 'para' => 'Sites com painel administrativo, cadastros e funções sob medida', 'destaque' => true,
         'itens' => ['Tudo do Essencial', 'Painel administrativo para editar conteúdo', 'Formulários e cadastros personalizados', 'Área do cliente com login', 'Cadastro e administração de usuários', 'Funcionalidades personalizadas']],
        ['nome' => 'Empresarial', 'preco' => '', 'detalhe' => '', 'para' => 'Sistemas completos com autonomia administrativa', 'destaque' => false,
         'itens' => ['Tudo do Profissional', 'Gerenciamento de usuários e permissões por perfil', 'Módulos sob medida (financeiro, clientes, documentos...)', 'Relatórios e painéis gerenciais', 'Automações e integrações com APIs', 'Auditoria, backup e suporte']],
    ],
    // Soluções que a orcamento.net desenvolve (página inicial).
    'solucoes' => [
        ['icone' => 'site', 'titulo' => 'Sites institucionais'], ['icone' => 'caixa', 'titulo' => 'Sites comerciais'], ['icone' => 'estrela', 'titulo' => 'Sites profissionais para empresas'],
        ['icone' => 'predio', 'titulo' => 'Sites para condomínios e administradoras'], ['icone' => 'sistema', 'titulo' => 'Sistemas administrativos personalizados'], ['icone' => 'engrenagem', 'titulo' => 'Sistemas de gerenciamento'],
        ['icone' => 'portal', 'titulo' => 'Plataformas com login e controle de usuários'], ['icone' => 'foguete', 'titulo' => 'Automação de processos'], ['icone' => 'grafico', 'titulo' => 'Painéis gerenciais e dashboards'],
        ['icone' => 'cifrao', 'titulo' => 'Sistemas financeiros'], ['icone' => 'pessoas', 'titulo' => 'Controle de clientes'], ['icone' => 'documento', 'titulo' => 'Gestão de documentos'],
        ['icone' => 'calendario', 'titulo' => 'Agendamentos e reservas'], ['icone' => 'api', 'titulo' => 'Integrações com APIs'], ['icone' => 'ia', 'titulo' => 'Ferramentas com inteligência artificial'],
    ],

    // Módulos que podem entrar em qualquer sistema (página inicial e catálogo).
    'modulos' => [
        ['icone' => 'pessoas', 'titulo' => 'Administração de usuários', 'texto' => 'Crie, ative, bloqueie e redefina a senha de qualquer usuário pelo painel.'],
        ['icone' => 'usuario', 'titulo' => 'Cadastro de usuários', 'texto' => 'Cadastro pelo administrador ou por convite, com senha provisória e troca no primeiro acesso.'],
        ['icone' => 'portal', 'titulo' => 'Perfis e permissões', 'texto' => 'Cada perfil vê só o que precisa: administrador, equipe, cliente, morador...'],
        ['icone' => 'sistema', 'titulo' => 'Painel administrativo', 'texto' => 'Indicadores, atalhos e pendências do dia na primeira tela.'],
        ['icone' => 'documento', 'titulo' => 'Cadastros sob medida', 'texto' => 'Clientes, unidades, produtos, serviços: os campos que o seu negócio usa.'],
        ['icone' => 'grafico', 'titulo' => 'Relatórios', 'texto' => 'Filtros por período e exportação em PDF ou planilha.'],
        ['icone' => 'email', 'titulo' => 'Notificações', 'texto' => 'Avisos automáticos por e-mail e WhatsApp nos momentos certos.'],
        ['icone' => 'escudo', 'titulo' => 'Segurança e auditoria', 'texto' => 'HTTPS, senhas protegidas, LGPD e registro de todas as ações.'],
    ],

    'etapas' => [
        ['titulo' => 'Pedido', 'texto' => 'Você conta o que precisa pelo formulário ou WhatsApp.', 'icone' => 'email'],
        ['titulo' => 'Conversa', 'texto' => 'Entendemos o negócio, o público e o que precisa funcionar.', 'icone' => 'pessoas'],
        ['titulo' => 'Proposta', 'texto' => 'Escopo e investimento por escrito, sem letra miúda.', 'icone' => 'documento'],
        ['titulo' => 'Prévia no portal', 'texto' => 'Você acompanha e aprova o projeto antes de ir ao ar.', 'icone' => 'olho'],
        ['titulo' => 'No ar', 'texto' => 'Publicação, treinamento e garantia de correções.', 'icone' => 'estrela'],
    ],

    'diferenciais' => [
        ['icone' => 'olho', 'titulo' => 'Prévia antes de pagar o restante', 'texto' => 'Você navega no projeto pelo portal, com login próprio, e só aprova quando estiver do seu jeito.'],
        ['icone' => 'documento', 'titulo' => 'Proposta sem surpresa', 'texto' => 'Escopo e valores fechados por escrito. Mudança de escopo só com seu aceite.'],
        ['icone' => 'portal', 'titulo' => 'Segurança de verdade', 'texto' => 'HTTPS, senhas protegidas, proteção contra spam, LGPD e backups em todos os projetos.'],
        ['icone' => 'chave', 'titulo' => 'Tudo fica com você', 'texto' => 'Código, domínio e dados são do cliente. Sem fidelidade na manutenção.'],
        ['icone' => 'pessoas', 'titulo' => 'Fala direto com quem faz', 'texto' => 'Sem intermediários: quem atende é quem desenvolve o seu projeto.'],
        ['icone' => 'foguete', 'titulo' => 'Cresce com a sua empresa', 'texto' => 'Comece pelo essencial e acrescente módulos e automações quando precisar.'],
    ],

    'faq' => [
        ['p' => 'O orçamento é gratuito?', 'r' => 'Sim. Você descreve o projeto e recebe uma proposta por escrito, sem compromisso.'],
        ['p' => 'Quanto tempo leva para ficar pronto?', 'r' => 'Depende do tamanho do projeto. O cronograma é combinado com você na proposta, e sistemas maiores são entregues em partes.'],
        ['p' => 'Como funciona a prévia no portal?', 'r' => 'Você recebe um login na orcamento.net e acompanha o projeto em uma versão de apresentação, antes de ele ir ao ar. Pode pedir ajustes até aprovar.'],
        ['p' => 'Quais opções eu posso escolher?', 'r' => 'Os planos Essencial, Profissional e Empresarial são pontos de partida. Também desenvolvemos projetos totalmente personalizados, com os módulos que o seu negócio precisa.'],
        ['p' => 'Como funciona o pagamento?', 'r' => 'As condições ficam na proposta. Projetos maiores podem ser pagos por etapa entregue.'],
        ['p' => 'Domínio e hospedagem estão inclusos?', 'r' => 'Configuramos domínio, hospedagem e e-mail para você. O que entra no valor fica descrito na proposta.'],
        ['p' => 'E depois que o site estiver no ar?', 'r' => 'Você tem garantia para correções. Se quiser, contrata a manutenção mensal, sem fidelidade.'],
    ],

    // Sistemas cadastrados no catálogo na instalação (depois você edita em Catálogo, no painel).
    'sistemas_iniciais' => [
        ['slug' => 'portal-do-condominio', 'nome' => 'Portal do Condomínio', 'categoria' => 'Condomínios', 'icone' => 'predio', 'destaque' => 1,
         'resumo' => 'Comunicados, documentos, reservas e chamados para síndicos, administradoras e moradores.',
         'descricao' => "Um portal com a marca da sua empresa para atender todos os condomínios da carteira em um só lugar. O síndico publica comunicados e documentos, os moradores reservam áreas comuns e abrem chamados pelo celular.",
         'para_quem' => 'Síndicos profissionais, administradoras e condomínios',
         'recursos' => "Painel para vários condomínios, blocos e unidades\nMural de comunicados com aviso por e-mail\nAtas, balancetes, regimento e convenção\nReserva de áreas comuns com calendário\nChamados e ocorrências com histórico\nRelatório mensal em PDF para o conselho\nFunciona no celular como aplicativo",
         'preco' => '', 'mensalidade' => '', 'prazo' => '', 'imagem' => ''],
        ['slug' => 'agenda-e-atendimentos', 'nome' => 'Agenda e Atendimentos', 'categoria' => 'Saúde e serviços', 'icone' => 'calendario', 'destaque' => 1,
         'resumo' => 'Agenda por profissional, fila de atendimento, ficha do cliente e relatórios.',
         'descricao' => "Organize a agenda de vários profissionais, confirme horários, acompanhe a fila do dia e mantenha o histórico de cada atendimento em um só sistema, com acesso controlado por perfil.",
         'para_quem' => 'Clínicas, consultórios, escritórios e prestadores de serviço',
         'recursos' => "Agenda por profissional e por sala\nEncaixes e horários livres\nFila de atendimento em tempo real\nFicha e histórico do cliente\nPerfis de acesso e auditoria\nIndicadores e relatórios em PDF",
         'preco' => '', 'mensalidade' => '', 'prazo' => '', 'imagem' => ''],
        ['slug' => 'orcamentos-e-pedidos', 'nome' => 'Orçamentos e Pedidos', 'categoria' => 'Comércio e serviços', 'icone' => 'documento', 'destaque' => 1,
         'resumo' => 'Monte orçamentos em minutos, envie por WhatsApp ou e-mail e acompanhe a aprovação.',
         'descricao' => "Cadastre produtos e serviços, gere orçamentos com a sua marca em PDF, envie com um clique e saiba quando o cliente aprovou. Os pedidos aprovados viram ordens de serviço.",
         'para_quem' => 'Lojas, prestadores de serviço, gráficas e indústrias',
         'recursos' => "Catálogo de produtos e serviços\nOrçamento em PDF com a sua marca\nEnvio por WhatsApp e e-mail\nAprovação online pelo cliente\nPedidos e ordens de serviço\nRelatório de vendas por período",
         'preco' => '', 'mensalidade' => '', 'prazo' => '', 'imagem' => ''],
        ['slug' => 'portal-do-cliente', 'nome' => 'Portal do Cliente', 'categoria' => 'Escritórios', 'icone' => 'portal', 'destaque' => 0,
         'resumo' => 'Área exclusiva com login para seus clientes acessarem documentos, solicitações e avisos.',
         'descricao' => "Chega de documentos perdidos no e-mail. Cada cliente entra com login próprio e encontra arquivos, avisos e solicitações em um só lugar, com registro de quem acessou o quê.",
         'para_quem' => 'Contabilidades, advocacias, consultorias e imobiliárias',
         'recursos' => "Login individual por cliente\nEnvio e download de documentos\nSolicitações com prazo e status\nAvisos e notificações por e-mail\nRegistro de acessos (LGPD)",
         'preco' => '', 'mensalidade' => '', 'prazo' => '', 'imagem' => ''],
        ['slug' => 'chamados-e-protocolos', 'nome' => 'Chamados e Protocolos', 'categoria' => 'Empresas e órgãos', 'icone' => 'caixa', 'destaque' => 0,
         'resumo' => 'Abertura, encaminhamento e acompanhamento de chamados com protocolo e prazo.',
         'descricao' => "Cada solicitação ganha um número de protocolo, um responsável e um prazo. Gestores acompanham tudo em painéis e relatórios, com trilha de auditoria completa.",
         'para_quem' => 'Empresas, associações e órgãos públicos',
         'recursos' => "Protocolo automático\nEncaminhamento por setor e responsável\nPrazos e alertas de atraso\nPainel com indicadores\nAuditoria de todas as ações",
         'preco' => '', 'mensalidade' => '', 'prazo' => '', 'imagem' => ''],
        ['slug' => 'vistorias-e-checklists', 'nome' => 'Vistorias e Checklists', 'categoria' => 'Manutenção', 'icone' => 'ferramenta', 'destaque' => 0,
         'resumo' => 'Checklists pelo celular com fotos, assinatura e relatório em PDF na hora.',
         'descricao' => "Equipes em campo preenchem checklists pelo celular, anexam fotos e geram o relatório na hora. Ideal para manutenção predial, inspeções e entregas de obra.",
         'para_quem' => 'Manutenção predial, construtoras e prestadores técnicos',
         'recursos' => "Modelos de checklist por norma\nFotos e observações por item\nRelatório em PDF automático\nHistórico por local ou equipamento\nAgenda de manutenções preventivas",
         'preco' => '', 'mensalidade' => '', 'prazo' => '', 'imagem' => ''],
    ],
    'categorias_sistema' => ['Condomínios', 'Saúde e serviços', 'Comércio e serviços', 'Escritórios', 'Empresas e órgãos', 'Manutenção', 'Outros'],

    'opcoes_servico' => ['Essencial', 'Profissional', 'Empresarial', 'Projeto personalizado', 'Um sistema do catálogo', 'Sistema web sob medida', 'Manutenção e evolução', 'Hospedagem, domínio e e-mail', 'Integrações e automação', 'Outro'],
    'opcoes_prazo' => ['O quanto antes', 'Em até 30 dias', 'Em até 3 meses', 'Só pesquisando'],
];
