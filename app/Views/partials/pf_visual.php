<?php /** Ilustração (só HTML/CSS) de cada área do portfólio. Espera $g com 'visual': site | whatsapp | painel | status. */
$tipo = $g['visual'] ?? 'site'; ?>
<?php if ($tipo === 'site'): ?>
<div class="pv pv--site">
  <div class="navegador">
    <div class="navegador__barra"><i></i><i></i><i></i><span>suaempresa.com.br</span></div>
    <div class="pv-site">
      <div class="pv-site__menu"><b></b><span></span><span></span><span></span><em>Orçamento</em></div>
      <div class="pv-site__hero"><div><i class="l1"></i><i class="l2"></i><i class="l3"></i><u>Solicitar orçamento</u></div><span class="pv-site__foto"></span></div>
      <div class="pv-site__cards"><span></span><span></span><span></span></div>
    </div>
  </div>
  <div class="pv-flutua pv-flutua--google"><small>Google</small><b>Sua Empresa | Serviços na sua região</b><span>suaempresa.com.br</span><em>Orçamento grátis, atendimento rápido...</em></div>
  <div class="pv-flutua pv-flutua--aviso"><span class="pv-ico pv-ico--verde"><?=icone('email')?></span><div><b>Novo pedido de orçamento</b><small>pelo formulário do site</small></div></div>
</div>
<?php elseif ($tipo === 'whatsapp'): ?>
<div class="pv pv--whats">
  <div class="pv-celular">
    <div class="pv-celular__topo"><span class="pv-ico pv-ico--verde"><?=icone('whatsapp')?></span><div><b>Sua Empresa</b><small>responde na hora</small></div></div>
    <div class="pv-chat">
      <p class="cli">Oi! Quero marcar um horário.</p>
      <p class="bot">Olá! Escolha uma opção:<br>1. Agendar horário<br>2. Pedir orçamento<br>3. Falar com a equipe</p>
      <p class="cli">1</p>
      <p class="bot">Tenho quinta às 14h ou sexta às 9h. Qual prefere?</p>
      <p class="cli">Sexta às 9h</p>
      <p class="bot">Agendado! <?=icone('check')?> Você vai receber um lembrete um dia antes.</p>
    </div>
  </div>
  <div class="pv-flutua pv-flutua--a"><span class="pv-ico"><?=icone('cifrao')?></span><div><b>Lembrete de pagamento enviado</b><small>automático</small></div></div>
  <div class="pv-flutua pv-flutua--b"><span class="pv-ico"><?=icone('grafico')?></span><div><b>Relatório do mês pronto</b><small>enviado por e-mail</small></div></div>
</div>
<?php elseif ($tipo === 'painel'): ?>
<div class="pv pv--painel">
  <div class="navegador">
    <div class="navegador__barra"><i></i><i></i><i></i><span>sistema.suaempresa.com.br</span></div>
    <div class="pv-painel">
      <div class="pv-painel__lateral"><b></b><span class="ativo"></span><span></span><span></span><span></span><span></span></div>
      <div class="pv-painel__corpo">
        <div class="pv-painel__kpis"><div><small>Clientes</small><b>128</b></div><div><small>Chamados abertos</small><b>7</b></div><div><small>Concluídos no mês</small><b>94</b></div></div>
        <div class="pv-painel__grafico"><small>Atendimentos por mês</small><div><i style="height:38%"></i><i style="height:52%"></i><i style="height:47%"></i><i style="height:66%"></i><i style="height:61%"></i><i style="height:78%"></i><i style="height:90%"></i></div></div>
        <div class="pv-painel__lista"><span><i></i><u class="ok">Concluído</u></span><span><i></i><u>Em andamento</u></span><span><i></i><u class="ok">Concluído</u></span></div>
      </div>
    </div>
  </div>
  <div class="pv-flutua pv-flutua--perfil"><span class="pv-ico"><?=icone('usuario')?></span><div><b>Acesso por perfil</b><small>administrador · equipe · cliente</small></div></div>
</div>
<?php else: ?>
<div class="pv pv--status">
  <div class="pv-status">
    <div class="pv-status__cab"><b>suaempresa.com.br</b><span><i></i> Tudo funcionando</span></div>
    <ul>
      <li><span class="pv-ico pv-ico--verde"><?=icone('site')?></span>Site no ar<em>OK</em></li>
      <li><span class="pv-ico pv-ico--verde"><?=icone('chave')?></span>Certificado HTTPS<em>Ativo</em></li>
      <li><span class="pv-ico pv-ico--verde"><?=icone('nuvem')?></span>Backup automático<em>Feito hoje</em></li>
      <li><span class="pv-ico pv-ico--verde"><?=icone('email')?></span>E-mail profissional<em>OK</em></li>
      <li><span class="pv-ico pv-ico--verde"><?=icone('escudo')?></span>Atualizações de segurança<em>Em dia</em></li>
    </ul>
  </div>
  <div class="pv-flutua pv-flutua--suporte"><span class="pv-ico"><?=icone('ferramenta')?></span><div><b>Ajuste solicitado</b><small>resolvido pela equipe</small></div></div>
</div>
<?php endif; ?>
