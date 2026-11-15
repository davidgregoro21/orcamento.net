<?php
/**
 * Rotas: página => [arquivo em app/Views/pages, título da aba, descrição para o Google].
 * Título null = usa o nome do site. A página 'artigo' monta título e descrição a partir do post.
 */
return [
 'home'=>['home.php', null, 'Síndico profissional em Caxias do Sul e região. Gestão de condomínios com transparência, presença semanal, controle de custos e segurança jurídica para síndico e condôminos.'],
 'sobre'=>['sobre.php', 'Sobre a Conect Síndico', 'Conheça a Conect Síndico e o gestor Daniel Mesquita de Almeida: mais de 7 anos como síndico e 20 anos em gestão, com formação em Direito e MBA em Gestão de Condomínios.'],
 'servicos'=>['servicos.php', 'Serviços de síndico profissional', 'Atendimento presencial semanal, consultoria administrativa, financeira e jurídica, controle de manutenções, redução de inadimplência e condução de assembleias.'],
 'blog'=>['blog.php', 'Blog – dicas de gestão de condomínios', 'Artigos sobre seguro predial, inadimplência, vazamentos, segurança, convivência e legislação para síndicos e condôminos.'],
 'artigo'=>['artigo.php', null, null],
 'contato'=>['contato.php', 'Contato e proposta', 'Fale com a Conect Síndico e receba uma proposta de gestão profissional para o seu condomínio em Caxias do Sul e região.'],
 'area_condomino'=>['area_condomino.php', 'Área do condômino', 'Acesso exclusivo para condôminos atendidos pela Conect Síndico: documentos, comunicados e informações do seu condomínio.'],
 'privacidade'=>['privacidade.php', 'Política de privacidade', 'Como a Conect Síndico trata os dados enviados pelo site, conforme a LGPD.'],
 'nao_encontrada'=>['nao_encontrada.php', 'Página não encontrada', null],
];
