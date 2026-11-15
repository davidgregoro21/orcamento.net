/* orcamento.net — interações (sem dependências) */
(function () {
  'use strict';
  // Máscara de telefone
  document.querySelectorAll('[data-mascara="telefone"]').forEach(function (c) {
    c.addEventListener('input', function () {
      var d = c.value.replace(/\D/g, '').slice(0, 11), r = d, corte = d.length === 11 ? 7 : 6;
      if (d.length > 2) r = '(' + d.slice(0, 2) + ') ' + d.slice(2);
      if (d.length > 6) r = '(' + d.slice(0, 2) + ') ' + d.slice(2, corte) + '-' + d.slice(corte);
      c.value = r;
    });
  });
  // Mostrar/ocultar senha
  document.querySelectorAll('.ver-senha').forEach(function (b) {
    b.addEventListener('click', function () {
      var i = b.parentElement.querySelector('input');
      i.type = i.type === 'password' ? 'text' : 'password';
      b.setAttribute('aria-label', i.type === 'password' ? 'Mostrar senha' : 'Ocultar senha');
    });
  });
  // Copiar texto (mensagem com o acesso do cliente)
  document.querySelectorAll('[data-copiar]').forEach(function (b) {
    b.addEventListener('click', function () {
      var alvo = document.querySelector(b.getAttribute('data-copiar')); if (!alvo) return;
      var ok = function () { var t = b.innerHTML; b.textContent = 'Copiado!'; setTimeout(function () { b.innerHTML = t; }, 1800); };
      if (navigator.clipboard) navigator.clipboard.writeText(alvo.value).then(ok, function () { alvo.select(); document.execCommand('copy'); ok(); });
      else { alvo.select(); document.execCommand('copy'); ok(); }
    });
  });
  // Confirmação antes de ações sensíveis
  document.querySelectorAll('form[data-confirmar]').forEach(function (f) {
    f.addEventListener('submit', function (ev) { if (!window.confirm(f.getAttribute('data-confirmar'))) ev.preventDefault(); });
  });
  // Envia o formulário ao trocar a situação do pedido
  document.querySelectorAll('[data-enviar-ao-mudar]').forEach(function (s) { s.addEventListener('change', function () { s.form.submit(); }); });
  // Validação simples antes de enviar
  document.querySelectorAll('form.form').forEach(function (form) {
    form.addEventListener('submit', function (ev) {
      var primeiro = null;
      form.querySelectorAll('[required]').forEach(function (c) {
        var ok = c.type === 'checkbox' ? c.checked : c.value.trim() !== '' && c.checkValidity();
        c.classList.toggle('invalido', !ok); if (!ok && !primeiro) primeiro = c;
      });
      if (primeiro) { ev.preventDefault(); primeiro.focus(); }
    });
  });
  // Fecha o menu do usuário ao clicar fora
  document.addEventListener('click', function (ev) {
    document.querySelectorAll('details.app-usuario[open]').forEach(function (d) { if (!d.contains(ev.target)) d.removeAttribute('open'); });
  });
  // Menu do celular (página comercial)
  var bm = document.querySelector('.pub-topo__menu');
  if (bm) {
    bm.addEventListener('click', function () { var a = document.body.classList.toggle('menu-aberto'); bm.setAttribute('aria-expanded', a ? 'true' : 'false'); });
    document.querySelectorAll('.pub-menu a').forEach(function (l) { l.addEventListener('click', function () { document.body.classList.remove('menu-aberto'); bm.setAttribute('aria-expanded', 'false'); }); });
  }
  var topo = document.getElementById('topo');
  if (topo) { var r = function () { topo.classList.toggle('rolou', window.scrollY > 8); }; window.addEventListener('scroll', r, { passive: true }); r(); }
  // Botão de pacote já escolhe o serviço no formulário
  var sel = document.getElementById('campo-servico');
  document.querySelectorAll('[data-servico]').forEach(function (b) {
    b.addEventListener('click', function () { if (!sel) return; var v = b.getAttribute('data-servico'); for (var i = 0; i < sel.options.length; i++) if (sel.options[i].text === v) sel.selectedIndex = i; });
  });
  // Animação suave dos blocos ao aparecer
  if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    var obs = new IntersectionObserver(function (es) { es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('visivel'); obs.unobserve(e.target); } }); }, { rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.publico .servico-card, .publico .pacote, .publico .lp-problema, .publico .diferencial, .publico .linha-tempo li, .publico .lp-case, .publico .lp-portal, .publico .sistema-card, .publico .servico-mini').forEach(function (el) { el.classList.add('revelar'); obs.observe(el); });
  }
  // Menu lateral (área logada, celular)
  document.querySelectorAll('[data-menu-lateral]').forEach(function (b) { b.addEventListener('click', function () { document.body.classList.toggle('menu-lateral-aberto'); }); });
  // Confirmação em botões específicos (ex.: enviar orçamento, aprovar)
  document.querySelectorAll('[data-confirmar-botao]').forEach(function (b) {
    b.addEventListener('click', function (ev) { if (!window.confirm(b.getAttribute('data-confirmar-botao'))) ev.preventDefault(); });
  });
  // Orçamento: adicionar linha de item
  var botaoItem = document.querySelector('[data-adicionar-item]');
  if (botaoItem) botaoItem.addEventListener('click', function () {
    var lista = document.getElementById('itens'), modelo = lista.querySelector('.item');
    var novo = modelo.cloneNode(true);
    novo.querySelectorAll('input, textarea').forEach(function (c) { c.value = c.classList.contains('item__qtd') ? '1' : ''; });
    lista.appendChild(novo); novo.querySelector('input').focus();
  });
  // Projeto: lista só os projetos do cliente escolhido
  document.querySelectorAll('[data-filtra-projetos]').forEach(function (selCli) {
    var selProj = selCli.form && selCli.form.querySelector('select[name="projeto_id"]'); if (!selProj) return;
    var filtrar = function () {
      var c = selCli.value;
      Array.prototype.forEach.call(selProj.options, function (o) { if (!o.value) return; var mostra = !c || o.getAttribute('data-cliente') === c; o.hidden = !mostra; if (!mostra && o.selected) selProj.value = ''; });
    };
    selCli.addEventListener('change', filtrar); filtrar();
  });
  // Visualizador: tamanhos de tela
  var quadro = document.getElementById('quadro-site');
  document.querySelectorAll('.dispositivos button').forEach(function (b) {
    b.addEventListener('click', function () {
      document.querySelectorAll('.dispositivos button').forEach(function (x) { x.classList.remove('ativo'); });
      b.classList.add('ativo'); if (quadro) quadro.style.width = b.getAttribute('data-largura');
    });
  });
  // Imprimir / salvar PDF
  document.querySelectorAll('[data-imprimir]').forEach(function (b) { b.addEventListener('click', function () { window.print(); }); });
})();
