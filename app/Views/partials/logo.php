<?php /** Marca orcamento.net (SVG, sem arquivo externo). $logoClaro = true para fundo escuro. */ ?>
<span class="marca<?= !empty($logoClaro) ? ' marca--clara' : '' ?>">
  <svg class="marca__ico" viewBox="0 0 40 40" aria-hidden="true">
    <defs><linearGradient id="mg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#4f7cff"/><stop offset="1" stop-color="#2346c9"/></linearGradient></defs>
    <rect width="40" height="40" rx="11" fill="url(#mg)"/>
    <path d="M13 11h10l5 5v13a2 2 0 0 1-2 2H13a2 2 0 0 1-2-2V13a2 2 0 0 1 2-2z" fill="none" stroke="#fff" stroke-width="2.2" stroke-linejoin="round"/>
    <path d="m15.5 22 3 3 6-6.5" fill="none" stroke="#7ef0b8" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
  <span class="marca__nome">orcamento<b>.net</b></span>
</span>
