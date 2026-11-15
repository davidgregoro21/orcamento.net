<?php
/**
 * Área do condômino — página nova de acesso.
 * O formulário envia e-mail e senha para o sistema de condôminos atual (config: empresa.portal_login_acao),
 * com os mesmos campos do login antigo (login / password). Assim o acesso continua funcionando com o visual novo.
 */
$acao = empresa('portal_login_acao') ?: rtrim(empresa('portal_url'), '/').'/dologin';
?>
<section class="acesso">
  <div class="container acesso__grade">
    <div class="acesso__info">
      <span class="sobretitulo sobretitulo--claro">Exclusivo para condôminos</span>
      <h1>Área do condômino</h1>
      <p class="acesso__sub">Acompanhe as informações do seu condomínio em um só lugar, com o mesmo cuidado e transparência da gestão da <?=e(empresa('nome_curto'))?>.</p>
      <ul class="acesso__lista">
        <li><span class="acesso__ico"><?=icone('documento')?></span><div><strong>Documentos do condomínio</strong><span>Convenção, regimento interno, atas e prestações de contas.</span></div></li>
        <li><span class="acesso__ico"><?=icone('calendario')?></span><div><strong>Comunicados e avisos</strong><span>Manutenções programadas, assembleias e informativos.</span></div></li>
        <li><span class="acesso__ico"><?=icone('escudo')?></span><div><strong>Acesso seguro</strong><span>Login individual, liberado pela administração do seu condomínio.</span></div></li>
      </ul>
    </div>

    <div class="acesso__cartao">
      <div class="acesso__cabeca">
        <img src="<?=asset('assets/img/logo.png')?>" alt="<?=e(empresa('nome_curto'))?>" width="59" height="64">
        <div><h2>Entrar</h2><p>Use o e-mail e a senha cadastrados pela administração.</p></div>
      </div>
      <form class="acesso__form" method="post" action="<?=e($acao)?>" autocomplete="on">
        <label class="campo"><span>E-mail</span>
          <span class="campo__com-ico"><?=icone('email')?><input type="email" name="login" required autocomplete="username" placeholder="seuemail@exemplo.com"></span>
        </label>
        <label class="campo"><span>Senha</span>
          <span class="campo__com-ico"><?=icone('cadeado')?><input type="password" name="password" required autocomplete="current-password" placeholder="Sua senha">
            <button type="button" class="acesso__ver" aria-label="Mostrar senha" data-ver-senha><?=icone('olho')?></button></span>
        </label>
        <button class="btn btn--laranja btn--grande acesso__botao" type="submit">Entrar na área do condômino <?=icone('seta')?></button>
      </form>
      <div class="acesso__ajuda">
        <p><strong>Ainda não tem acesso ou esqueceu a senha?</strong> Fale com a nossa equipe que liberamos para você.</p>
        <div class="acesso__contatos">
          <a class="btn btn--whats btn--pequeno" href="<?=e(link_whatsapp('Olá! Sou condômino e preciso de ajuda para acessar a Área do condômino.'))?>" target="_blank" rel="noopener"><?=icone('whatsapp')?> WhatsApp</a>
          <a class="acesso__tel" href="tel:+55<?=e(so_digitos(empresa('telefone')))?>"><?=icone('telefone')?> <?=e(empresa('telefone'))?></a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="secao secao--clara">
  <div class="container">
    <div class="cartoes cartoes--3">
      <div class="cartao"><span class="cartao__ico"><?=icone('pessoas')?></span><h3>É síndico ou conselheiro?</h3><p>Conheça como a gestão profissional pode trazer mais tranquilidade para o seu condomínio.</p><a class="link-seta" href="<?=url_pagina('servicos')?>">Ver serviços <?=icone('seta')?></a></div>
      <div class="cartao"><span class="cartao__ico"><?=icone('documento')?></span><h3>Dicas para condôminos</h3><p>Artigos sobre convivência, segurança, manutenção e legislação em condomínios.</p><a class="link-seta" href="<?=url_pagina('blog')?>">Ir para o blog <?=icone('seta')?></a></div>
      <div class="cartao"><span class="cartao__ico"><?=icone('telefone')?></span><h3>Atendimento 24 horas</h3><p>Precisa falar com a administração? Estamos disponíveis por telefone e WhatsApp.</p><a class="link-seta" href="<?=url_pagina('contato')?>">Falar com a Conect <?=icone('seta')?></a></div>
    </div>
  </div>
</section>
