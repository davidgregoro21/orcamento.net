<?php
/**
 * Conteúdo do site em um só lugar: serviços, diferenciais, formação e artigos do blog.
 * Para mudar um texto, edite aqui — as páginas leem destas listas.
 */
$conteudo = [

    // Números que aparecem no topo da página inicial.
    'numeros' => [
        ['valor' => '+7 anos', 'texto' => 'atuando como síndico'],
        ['valor' => '+20 anos', 'texto' => 'em gestão e consultoria'],
        ['valor' => '24h', 'texto' => 'atendimento por telefone'],
        ['valor' => 'Semanal', 'texto' => 'presença no condomínio'],
    ],

    // As seis esferas de responsabilidade do síndico (página inicial e Sobre).
    'responsabilidades' => [
        ['icone' => 'balanca', 'titulo' => 'Civil', 'texto' => 'Danos a moradores, visitantes e terceiros por falta de manutenção ou de seguro.'],
        ['icone' => 'escudo', 'titulo' => 'Criminal', 'texto' => 'Acidentes em áreas comuns, omissão em situações de risco e apropriação indevida.'],
        ['icone' => 'pessoas', 'titulo' => 'Trabalhista', 'texto' => 'Contratação, jornada, eSocial e passivos com funcionários e terceirizados.'],
        ['icone' => 'folha', 'titulo' => 'Ambiental', 'texto' => 'Descarte de resíduos, esgoto, reservatórios e normas ambientais.'],
        ['icone' => 'grafico', 'titulo' => 'Tributária', 'texto' => 'Retenções, declarações e obrigações fiscais do condomínio em dia.'],
        ['icone' => 'documento', 'titulo' => 'Previdenciária', 'texto' => 'Recolhimentos de INSS e FGTS de empregados e prestadores de serviço.'],
    ],

    // Serviços agrupados (página Serviços; os 6 grupos também aparecem na inicial).
    'servicos' => [
        ['icone' => 'predio', 'titulo' => 'Gestão presencial', 'resumo' => 'O síndico profissional no seu condomínio toda semana, acompanhando de perto a rotina.', 'itens' => [
            'Atendimento presencial semanal, conforme o plano contratado',
            'Cumprir e fazer cumprir a convenção, o regimento e as decisões de assembleia',
            'Comunicados aos moradores sobre manutenções, novos funcionários e segurança',
        ]],
        ['icone' => 'grafico', 'titulo' => 'Finanças e inadimplência', 'resumo' => 'Contas equilibradas, previsão orçamentária e cobrança organizada.', 'itens' => [
            'Análise permanente de custos e receitas',
            'Parecer sobre balancetes e prestações de contas',
            'Acordos formais, extrajudiciais e judiciais para reduzir a inadimplência',
            'Revisão de contratos para pagar menos e receber mais qualidade',
        ]],
        ['icone' => 'ferramenta', 'titulo' => 'Manutenção e conservação', 'resumo' => 'Checklists baseados nas normas técnicas e vistorias periódicas.', 'itens' => [
            'Controle de serviços por checklist com base em ABNT, NBR e NR',
            'Vistorias e relatório predial das áreas comuns e equipamentos',
            'Cotação e compra de materiais e equipamentos',
            'Projetos e cronogramas de obras com acompanhamento',
        ]],
        ['icone' => 'balanca', 'titulo' => 'Consultoria jurídica e normas', 'resumo' => 'Decisões dentro da lei e documentos do condomínio atualizados.', 'itens' => [
            'Consultoria administrativa, financeira e jurídica',
            'Elaboração e alteração de regimento interno, convenção e normativas',
            'Estudo, aplicação e controle da legislação',
        ]],
        ['icone' => 'pessoas', 'titulo' => 'Pessoas e convivência', 'resumo' => 'Assembleias produtivas e conflitos resolvidos com equilíbrio.', 'itens' => [
            'Condução de reuniões e assembleias',
            'Mediação de conflitos entre moradores',
            'Seleção criteriosa de funcionários, evitando passivos trabalhistas',
            'Treinamentos e reuniões com conselho e prestadores, sem custo adicional',
        ]],
        ['icone' => 'chave', 'titulo' => 'Procedimentos e implantação', 'resumo' => 'Regras claras para a rotina e apoio a condomínios novos.', 'itens' => [
            'Procedimentos de garagem, portaria, limpeza e manutenção preventiva e corretiva',
            'Uso racional de água, energia e serviços terceirizados',
            'Assessoria a construtoras na implantação do condomínio',
        ]],
    ],

    // "Com a Conect Síndico, seu condomínio terá:" (mesma lista do site atual).
    'beneficios' => [
        'Gestão profissional',
        'Isenção e transparência nas decisões',
        'Experiência em gestão de conflitos',
        'Atribuições previstas em contrato',
        'Tranquilidade na gestão (alçada de decisões e conduta delimitada pelo conselho)',
        'Agilidade nos processos administrativos, operacionais e de rotina',
        'Gestão de planejamento',
        'Trabalho de acordo com a legislação, normas e práticas modernas de gestão eficiente',
        'Maior comodidade para os condôminos',
        'Profissional qualificado e capacitado para o desenvolvimento das atividades',
    ],

    // "Conte com a Conect Síndico para ter uma gestão diferenciada" (mesma lista do site atual).
    'diferenciais' => [
        'Transparência e honestidade nas relações',
        'Tecnologia para facilitar a comunicação',
        'Atendimento 24 horas via telefone',
        'Grupo seleto de fornecedores e prestadores de serviço',
        'Explanações, treinamentos e reuniões periódicas com conselho, condomínio e prestadores de serviço, sem cobrança adicional',
        'Foco em gestão de inadimplência, redução de custos e previsão orçamentária',
        'Implantação de checklist para todos os funcionários, com fiscalização e cobrança de resultados semanais',
        'Cumprimento da legislação e do Código Civil',
    ],

    // Como funciona a contratação (página inicial).
    'etapas' => [
        ['titulo' => 'Conversa inicial', 'texto' => 'Entendemos o perfil do condomínio, as dificuldades atuais e o que o conselho espera.'],
        ['titulo' => 'Diagnóstico e proposta', 'texto' => 'Visitamos o prédio, analisamos contas e contratos e apresentamos uma proposta sob medida.'],
        ['titulo' => 'Assembleia', 'texto' => 'Apresentamos o plano de gestão aos condôminos para aprovação em assembleia.'],
        ['titulo' => 'Gestão profissional', 'texto' => 'Assumimos a rotina com presença semanal, relatórios e prestação de contas clara.'],
    ],

    // Formação do gestor (página Sobre).
    'formacao' => [
        'Bacharel em Direito (UCS)',
        'Pós-graduação em Direito Ambiental (UCS)',
        'MBA em Marketing (FGV)',
        'Pós-graduação em Gestão de Pessoas (UCS)',
        'Gestão Contemporânea (FGC-Nortus)',
        'MBA em Gestão Profissional de Condomínios (UCAM)',
    ],

    // Artigos do blog. Os slugs são os mesmos do site antigo (preserva o Google e os links já compartilhados).
    // 'corpo' recebe o HTML do texto completo; enquanto estiver vazio, a página mostra o resumo.
    'artigos' => [
        ['slug' => 'diferenca-entre-porteiros-vigias-e-vigilantes', 'categoria' => 'Segurança', 'titulo' => 'Diferença entre porteiros, vigias e vigilantes',
         'resumo' => 'Cada função tem atribuições e exigências legais diferentes. Saiba qual o condomínio precisa e quais são os limites de cada uma.', 'corpo' => ''],
        ['slug' => 'tudo-o-que-voce-precisa-saber-sobre-limpeza-e-manutencao-de-janelas-varandas-e-fachadas', 'categoria' => 'Manutenção', 'titulo' => 'Limpeza e manutenção de janelas, varandas e fachadas',
         'resumo' => 'Trabalho em altura exige técnica, equipamento e responsabilidade. Veja os cuidados para manter a fachada conservada e segura.', 'corpo' => ''],
        ['slug' => 'pintura-de-condominio-o-que-precisamos-saber', 'categoria' => 'Manutenção', 'titulo' => 'Pintura de condomínio: o que precisamos saber?',
         'resumo' => 'Da escolha da tinta à aprovação em assembleia: o passo a passo para planejar a pintura com custo justo e garantia.', 'corpo' => ''],
        ['slug' => 'animais-no-condominio-como-evitar-atritos', 'categoria' => 'Convivência', 'titulo' => 'Animais no condomínio: como evitar atritos?',
         'resumo' => 'A Justiça garante o direito de ter pets, desde que não prejudiquem o sossego e a segurança. Regras claras evitam conflitos.', 'corpo' => ''],
        ['slug' => 'centrais-de-gas-condominiais-manutencoes-e-prevencoes-para-nao-gerar-riscos', 'categoria' => 'Segurança', 'titulo' => 'Centrais de gás: manutenção e prevenção de riscos',
         'resumo' => 'Vazamentos de gás podem causar explosões. Conheça as inspeções e cuidados obrigatórios para a central do condomínio.', 'corpo' => ''],
        ['slug' => 'vazamentos-e-infiltracoes-em-condominios-o-que-fazer-quem-e-responsavel', 'categoria' => 'Manutenção', 'titulo' => 'Vazamentos e infiltrações: o que fazer e quem é responsável?',
         'resumo' => 'Antes de discutir quem paga, é preciso achar a origem. Veja como agir rápido e evitar prejuízos e processos.', 'corpo' => ''],
        ['slug' => 'furtos-em-condominios-quem-e-responsavel-o-que-fazer-e-quem-paga-a-conta', 'categoria' => 'Segurança', 'titulo' => 'Furtos em condomínios: quem é responsável e quem paga a conta?',
         'resumo' => 'Na maioria dos casos o condomínio não precisa indenizar. Entenda o que diz a convenção e o entendimento do STJ.', 'corpo' => ''],
        ['slug' => 'acessibilidade-em-condominios', 'categoria' => 'Legislação', 'titulo' => 'Acessibilidade em condomínios',
         'resumo' => 'Garantir acessibilidade é obrigação do condomínio, prevista em decreto e na norma ABNT NBR 9050. Saiba por onde começar.', 'corpo' => ''],
        ['slug' => 'seguro-predial-obrigatoriedade-de-contratar-quais-riscos-implicam', 'categoria' => 'Legislação', 'titulo' => 'Seguro predial: obrigatoriedade e riscos',
         'resumo' => 'O Código Civil obriga o síndico a contratar o seguro da edificação. Não contratar pode levar à destituição e à responsabilização.', 'corpo' => ''],
        ['slug' => 'como-manter-a-seguranca-das-criancas-em-um-condominio', 'categoria' => 'Segurança', 'titulo' => 'Como manter a segurança das crianças no condomínio',
         'resumo' => 'Estrutura ajuda, mas não basta. Regras para playground, piscina, garagem e portaria que protegem os pequenos.', 'corpo' => ''],
        ['slug' => 'barulho-e-problemas-com-vizinhos-como-evitar', 'categoria' => 'Convivência', 'titulo' => 'Barulho e problemas com vizinhos: como evitar?',
         'resumo' => 'Ruído é a principal causa de reclamações. Veja como o regimento e a mediação ajudam a manter a boa convivência.', 'corpo' => ''],
        ['slug' => 'sustentabilidade-em-condominio-como-ser-sustentavel-e-diminuir-gastos', 'categoria' => 'Finanças', 'titulo' => 'Sustentabilidade: como economizar e ser sustentável',
         'resumo' => 'Coleta seletiva, iluminação eficiente e reaproveitamento de água reduzem a taxa de condomínio e o impacto ambiental.', 'corpo' => ''],
        ['slug' => 'medicao-individualizada-de-agua', 'categoria' => 'Finanças', 'titulo' => 'Medição individualizada de água',
         'resumo' => 'Cada unidade paga o que consome. Conheça as vantagens, os custos de implantação e o que diz a lei.', 'corpo' => ''],
        ['slug' => 'inadimplencia-em-condominio-como-evitar', 'categoria' => 'Finanças', 'titulo' => 'Inadimplência em condomínio: como evitar?',
         'resumo' => 'Cobrança organizada, acordos e comunicação clara mantêm o caixa saudável sem desgastar a relação com os moradores.', 'corpo' => ''],
        ['slug' => 'principais-problemas-em-condominios-como-evitar', 'categoria' => 'Convivência', 'titulo' => 'Principais problemas em condomínios e como evitar',
         'resumo' => 'Garagem, lixo, obras, áreas comuns: os conflitos mais frequentes e as medidas que previnem cada um.', 'corpo' => ''],
        ['slug' => 'e-social-em-condominios-decreto-83732014', 'categoria' => 'Legislação', 'titulo' => 'eSocial em condomínios (Decreto 8.373/2014)',
         'resumo' => 'Condomínios com funcionários precisam enviar as informações trabalhistas pelo eSocial. Entenda as obrigações e prazos.', 'corpo' => ''],
    ],
];

// Blog: título original, texto completo e imagem de capa vindos do site atual (artigos_corpo.php e assets/img/blog/).
$corpos = require __DIR__.'/artigos_corpo.php';
foreach ($conteudo['artigos'] as &$artigo) {
    $c = $corpos[$artigo['slug']] ?? null;
    if ($c) { $artigo['titulo'] = $c['titulo']; $artigo['corpo'] = $c['html']; }
    $artigo['imagem'] = 'assets/img/blog/'.$artigo['slug'].'.jpg';
}
unset($artigo);
return $conteudo;
