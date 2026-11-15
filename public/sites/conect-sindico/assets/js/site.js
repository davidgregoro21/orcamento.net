/* Conect Síndico — interações do site (sem dependências) */
(function () {
  'use strict';
  var corpo = document.body;
  var topo = document.getElementById('topo');
  var botaoMenu = document.querySelector('.topo__menu');

  // Menu do celular
  if (botaoMenu) {
    botaoMenu.addEventListener('click', function () {
      var aberto = corpo.classList.toggle('menu-aberto');
      botaoMenu.setAttribute('aria-expanded', aberto ? 'true' : 'false');
      botaoMenu.setAttribute('aria-label', aberto ? 'Fechar menu' : 'Abrir menu');
    });
    document.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape' && corpo.classList.contains('menu-aberto')) { botaoMenu.click(); botaoMenu.focus(); }
    });
  }

  // Sombra no cabeçalho ao rolar
  var aoRolar = function () { if (topo) topo.classList.toggle('rolou', window.scrollY > 8); };
  window.addEventListener('scroll', aoRolar, { passive: true }); aoRolar();

  // Máscara de telefone: (54) 99999-9999
  document.querySelectorAll('[data-mascara="telefone"]').forEach(function (campo) {
    campo.addEventListener('input', function () {
      var d = campo.value.replace(/\D/g, '').slice(0, 11), r = d;
      if (d.length > 2) r = '(' + d.slice(0, 2) + ') ' + d.slice(2);
      if (d.length > 6) r = '(' + d.slice(0, 2) + ') ' + d.slice(2, d.length === 11 ? 7 : 6) + '-' + d.slice(d.length === 11 ? 7 : 6);
      campo.value = r;
    });
  });

  // Validação simples antes de enviar (o servidor valida de novo)
  document.querySelectorAll('form.form').forEach(function (form) {
    form.addEventListener('submit', function (ev) {
      var primeiro = null;
      form.querySelectorAll('[required]').forEach(function (c) {
        var ok = c.type === 'checkbox' ? c.checked : c.value.trim() !== '' && (!c.checkValidity || c.checkValidity());
        c.classList.toggle('invalido', !ok);
        if (!ok && !primeiro) primeiro = c;
      });
      if (primeiro) { ev.preventDefault(); primeiro.focus(); return; }
      var btn = form.querySelector('button[type="submit"]');
      if (btn) { btn.disabled = true; btn.textContent = 'Enviando...'; }
    });
  });

  // Animação suave dos blocos ao aparecer na tela
  if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    var alvos = document.querySelectorAll('.cartao, .etapa, .servico, .pilar, .painel, .numeros');
    var obs = new IntersectionObserver(function (entradas) {
      entradas.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('visivel'); obs.unobserve(e.target); } });
    }, { rootMargin: '0px 0px -40px 0px' });
    alvos.forEach(function (el) { el.classList.add('revelar'); obs.observe(el); });
  }
})();

// Área do condômino: mostrar/ocultar a senha.
document.querySelectorAll('[data-ver-senha]').forEach(function (b) {
  b.addEventListener('click', function () {
    var campo = b.parentNode.querySelector('input');
    var mostrar = campo.type === 'password';
    campo.type = mostrar ? 'text' : 'password';
    b.setAttribute('aria-pressed', mostrar ? 'true' : 'false');
    b.setAttribute('aria-label', mostrar ? 'Ocultar senha' : 'Mostrar senha');
  });
});
