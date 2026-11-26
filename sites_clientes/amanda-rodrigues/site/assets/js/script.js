/* =========================================================
   AMANDA RODRIGUES ADVOCACIA — Interações (JavaScript puro)
   1. Cabeçalho que muda na rolagem + link ativo
   2. Scroll reveal (IntersectionObserver)
   3. Capítulos das áreas de atuação (expandir/recolher)
   4. Desenho da Deusa da Justiça e frase palavra a palavra
   5. Links de WhatsApp com mensagem automática
   6. Formulário de contato (validação + envio)
   7. Pequenos detalhes (parallax, ano, botão flutuante)
   ========================================================= */
(function () {
  "use strict";

  var WHATSAPP = "5551998613596";
  var WA_MSG_PADRAO = "Olá, Dra. Amanda! Vim pelo site e gostaria de agendar um atendimento.";
  var reduzMovimento = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* ---------- 1. Cabeçalho ---------- */
  var header = $(".site-header");
  var waFloat = $(".wa-float");
  var heroArch = $(".hero-arch");
  var ticking = false;

  function onScroll() {
    var y = window.scrollY || window.pageYOffset;
    header.classList.toggle("is-solid", y > 40);
    if (waFloat) waFloat.classList.toggle("is-visible", y > window.innerHeight * 0.5);
    if (heroArch && !reduzMovimento && y < window.innerHeight) heroArch.style.setProperty("--parallax", (y * 0.25).toFixed(1));
    ticking = false;
  }
  window.addEventListener("scroll", function () {
    if (!ticking) { window.requestAnimationFrame(onScroll); ticking = true; }
  }, { passive: true });
  onScroll();

  // Destaca no menu a seção visível
  var navLinks = $$(".nav-links a");
  if ("IntersectionObserver" in window && navLinks.length) {
    var spy = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        navLinks.forEach(function (a) { a.classList.toggle("active", a.getAttribute("href") === "#" + e.target.id); });
      });
    }, { rootMargin: "-45% 0px -50% 0px" });
    $$("main section[id]").forEach(function (s) { spy.observe(s); });
  }

  /* ---------- 2. Scroll reveal ---------- */
  var reveals = $$(".reveal");
  if ("IntersectionObserver" in window && !reduzMovimento) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add("is-in"); io.unobserve(e.target); }
      });
    }, { threshold: 0.12, rootMargin: "0px 0px -6% 0px" });
    reveals.forEach(function (el, i) {
      // pequeno escalonamento entre elementos irmãos
      var irmaos = Array.prototype.indexOf.call(el.parentNode.children, el);
      el.style.transitionDelay = Math.min(irmaos, 5) * 0.08 + "s";
      io.observe(el);
    });
  } else {
    reveals.forEach(function (el) { el.classList.add("is-in"); });
  }

  /* ---------- 3. Capítulos (áreas de atuação) ---------- */
  $$(".chapter-toggle").forEach(function (btn) {
    var painel = document.getElementById(btn.getAttribute("aria-controls"));
    var capitulo = btn.closest(".chapter");
    var rotulo = btn.querySelector("span");
    painel.setAttribute("inert", "");

    btn.addEventListener("click", function () {
      var abrir = btn.getAttribute("aria-expanded") !== "true";
      btn.setAttribute("aria-expanded", String(abrir));
      painel.classList.toggle("is-visible", abrir);
      capitulo.classList.toggle("is-open", abrir);
      rotulo.textContent = abrir ? "Recolher serviços" : "Conhecer os serviços";
      if (abrir) painel.removeAttribute("inert"); else painel.setAttribute("inert", "");
    });
  });

  // Links do rodapé para uma área: rola até o capítulo e o abre
  $$('.footer-links a[href^="#direito-"]').forEach(function (a) {
    a.addEventListener("click", function () {
      var alvo = document.querySelector(a.getAttribute("href") + " .chapter-toggle");
      if (alvo && alvo.getAttribute("aria-expanded") !== "true") setTimeout(function () { alvo.click(); }, 600);
    });
  });

  /* ---------- 4. Deusa da Justiça + frase institucional ---------- */
  var arte = $(".justice-art");
  var frase = $("[data-words]");

  // Divide a frase em palavras, preservando o <em>
  if (frase) {
    var idx = 0;
    (function dividir(no) {
      Array.prototype.slice.call(no.childNodes).forEach(function (filho) {
        if (filho.nodeType === 3) {
          var frag = document.createDocumentFragment();
          filho.textContent.split(/(\s+)/).forEach(function (parte) {
            if (!parte) return;
            if (/^\s+$/.test(parte)) { frag.appendChild(document.createTextNode(parte)); return; }
            var s = document.createElement("span");
            s.className = "w"; s.textContent = parte;
            s.style.transitionDelay = (idx++ * 0.09) + "s";
            frag.appendChild(s);
          });
          no.replaceChild(frag, filho);
        } else if (filho.nodeType === 1) { dividir(filho); }
      });
    })(frase);
  }

  if ("IntersectionObserver" in window) {
    var io2 = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        e.target.classList.add(e.target === arte ? "is-drawn" : "is-in");
        io2.unobserve(e.target);
      });
    }, { threshold: 0.35 });
    if (arte) io2.observe(arte);
    if (frase) io2.observe(frase);
  } else {
    if (arte) arte.classList.add("is-drawn");
    if (frase) frase.classList.add("is-in");
  }

  /* ---------- 5. WhatsApp com mensagem automática ---------- */
  function linkWhats(msg) { return "https://wa.me/" + WHATSAPP + "?text=" + encodeURIComponent(msg || WA_MSG_PADRAO); }
  $$("[data-wa]").forEach(function (a) {
    a.href = linkWhats(a.getAttribute("data-wa"));
    a.target = "_blank"; a.rel = "noopener";
  });

  /* ---------- 6. Formulário de contato ---------- */
  var form = $("#formContato");
  if (form) {
    var tel = $("#telefone", form);
    var status = $(".form-status", form);

    // Máscara de telefone: (51) 99999-9999
    tel.addEventListener("input", function () {
      var d = tel.value.replace(/\D/g, "").slice(0, 11);
      var r = d;
      if (d.length > 2) r = "(" + d.slice(0, 2) + ") " + d.slice(2);
      if (d.length > 7) r = "(" + d.slice(0, 2) + ") " + d.slice(2, d.length - 4) + "-" + d.slice(-4);
      tel.value = r;
    });

    function validar() {
      var ok = true;
      var regras = {
        nome: function (v) { return v.trim().length >= 2; },
        telefone: function (v) { return v.replace(/\D/g, "").length >= 10; },
        email: function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v.trim()); },
        mensagem: function (v) { return v.trim().length >= 5; }
      };
      Object.keys(regras).forEach(function (nome) {
        var campo = form.elements[nome];
        var valido = regras[nome](campo.value);
        campo.closest(".field").classList.toggle("has-error", !valido);
        campo.setAttribute("aria-invalid", String(!valido));
        if (!valido && ok) { campo.focus(); }
        ok = ok && valido;
      });
      var lgpd = form.elements.lgpd;
      lgpd.closest(".consent").classList.toggle("has-error", !lgpd.checked);
      if (!lgpd.checked && ok) lgpd.focus();
      return ok && lgpd.checked;
    }

    $$("input, textarea", form).forEach(function (c) {
      c.addEventListener("input", function () { var f = c.closest(".field"); if (f) f.classList.remove("has-error"); });
    });

    function mensagemWhats() {
      return "Olá, Dra. Amanda! Vim pelo site.\n\nNome: " + form.nome.value.trim() +
        "\nTelefone: " + form.telefone.value.trim() + "\nE-mail: " + form.email.value.trim() +
        "\n\n" + form.mensagem.value.trim();
    }

    form.addEventListener("submit", function (ev) {
      ev.preventDefault();
      status.className = "form-status"; status.textContent = "";
      if (!validar()) { status.classList.add("err"); status.textContent = "Revise os campos destacados."; return; }

      var botao = form.querySelector('button[type="submit"]');
      botao.disabled = true; botao.querySelector("span").textContent = "Enviando...";

      // Envia para enviar.php (Hostinger, PHP). Se não estiver disponível, oferece o WhatsApp.
      fetch(form.action, { method: "POST", body: new FormData(form), headers: { "Accept": "application/json" } })
        .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
        .then(function (res) {
          if (!res.ok) throw new Error(res.erro || "falha");
          status.classList.add("ok");
          status.textContent = "Mensagem enviada. Retornaremos o contato em breve.";
          form.reset();
        })
        .catch(function () {
          status.classList.add("err");
          status.innerHTML = 'Não foi possível enviar agora. <a href="' + linkWhats(mensagemWhats()) +
            '" target="_blank" rel="noopener">Envie sua mensagem pelo WhatsApp</a>.';
        })
        .then(function () { botao.disabled = false; botao.querySelector("span").textContent = "Enviar mensagem"; });
    });
  }

  /* ---------- 7. Detalhes ---------- */
  $$("[data-year]").forEach(function (el) { el.textContent = new Date().getFullYear(); });

  // Fecha o menu mobile antes de rolar até a âncora (evita salto)
  $$(".menu-mobile a[href^='#']").forEach(function (a) {
    a.addEventListener("click", function (e) {
      var alvo = document.querySelector(a.getAttribute("href"));
      if (!alvo) return;
      e.preventDefault();
      setTimeout(function () { alvo.scrollIntoView({ behavior: reduzMovimento ? "auto" : "smooth" }); }, 350);
    });
  });
})();
