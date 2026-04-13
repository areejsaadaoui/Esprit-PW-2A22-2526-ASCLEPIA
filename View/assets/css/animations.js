/**
 * ASCLEPIA — animations.js
 * Active toutes les animations définies dans animations.css
 * Usage : <script src="assets/js/animations.js" defer></script>
 */

(function () {
  'use strict';

  /* ============================================================
     1. PAGE TRANSITION (entrée douce à chaque chargement)
     ============================================================ */
  function initPageTransition() {
    // Crée l'overlay s'il n'existe pas
    let overlay = document.getElementById('page-transition');
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.id = 'page-transition';
      document.body.appendChild(overlay);
    }

    // Entrée : révèle la page
    overlay.classList.add('is-entering');
    setTimeout(function () {
      overlay.classList.remove('is-entering');
    }, 600);

    // Sur chaque lien interne : effet de sortie
    document.addEventListener('click', function (e) {
      const link = e.target.closest('a[href]');
      if (!link) return;
      const href = link.getAttribute('href');
      if (!href || href.startsWith('#') || href.startsWith('javascript') ||
          href.startsWith('mailto') || href.startsWith('tel') ||
          link.target === '_blank' || e.ctrlKey || e.metaKey) return;

      e.preventDefault();
      overlay.classList.add('is-leaving');
      setTimeout(function () {
        window.location.href = href;
      }, 480);
    });
  }

  /* ============================================================
     2. BARRE DE PROGRESSION DE DÉFILEMENT
     ============================================================ */
  function initScrollProgress() {
    let bar = document.getElementById('scroll-progress');
    if (!bar) {
      bar = document.createElement('div');
      bar.id = 'scroll-progress';
      document.body.appendChild(bar);
    }

    function update() {
      const scrollTop  = document.documentElement.scrollTop || document.body.scrollTop;
      const docHeight  = document.documentElement.scrollHeight - document.documentElement.clientHeight;
      const pct        = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
      bar.style.width  = pct + '%';
    }

    window.addEventListener('scroll', update, { passive: true });
    update();
  }

  /* ============================================================
     3. ORBES DE FOND
     ============================================================ */
  function initBgOrbs() {
    if (document.querySelector('.bg-orbs')) return; // déjà présent en HTML
    const wrap = document.createElement('div');
    wrap.className = 'bg-orbs';
    wrap.innerHTML =
      '<div class="bg-orb bg-orb-1"></div>' +
      '<div class="bg-orb bg-orb-2"></div>' +
      '<div class="bg-orb bg-orb-3"></div>';
    document.body.insertBefore(wrap, document.body.firstChild);
  }

  /* ============================================================
     4. PARTICULES FLOTTANTES
     ============================================================ */
  function initParticles() {
    if (document.querySelector('.particles-wrap')) return;
    const wrap = document.createElement('div');
    wrap.className = 'particles-wrap';
    const count = 10;
    for (let i = 0; i < count; i++) {
      const p = document.createElement('div');
      p.className = 'particle';
      // Position et taille aléatoires complémentaires
      const size   = Math.random() * 5 + 2;
      const left   = Math.random() * 100;
      const dur    = Math.random() * 8 + 6;
      const delay  = Math.random() * 5;
      const colors = ['rgba(14,165,233,', 'rgba(16,185,129,', 'rgba(6,182,212,'];
      const color  = colors[Math.floor(Math.random() * colors.length)];
      p.style.cssText =
        'width:' + size + 'px;height:' + size + 'px;' +
        'left:' + left + '%;bottom:-10px;' +
        'background:' + color + (Math.random() * 0.5 + 0.3) + ');' +
        'animation-duration:' + dur + 's;' +
        'animation-delay:' + delay + 's;';
      wrap.appendChild(p);
    }
    document.body.insertBefore(wrap, document.body.firstChild);
  }

  /* ============================================================
     5. SCROLL REVEAL (Intersection Observer)
     ============================================================ */
  function initScrollReveal() {
    if (!('IntersectionObserver' in window)) return;

    const observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    // Éléments avec attribut data-reveal
    document.querySelectorAll('[data-reveal]').forEach(function (el) {
      observer.observe(el);
    });

    // Auto-reveal sur cartes et sections si non déjà marquées
    const autoReveal = document.querySelectorAll(
      '.card:not([data-reveal]), .stat-card:not([data-reveal]), ' +
      '.asclepia-card:not([data-reveal]), .post-card:not([data-reveal]), ' +
      '.book-card:not([data-reveal]), .section:not([data-reveal])'
    );
    autoReveal.forEach(function (el, i) {
      if (!el.hasAttribute('data-reveal')) {
        el.setAttribute('data-reveal', 'up');
        el.style.transitionDelay = (i % 6) * 0.08 + 's';
        observer.observe(el);
      }
    });
  }

  /* ============================================================
     6. EFFET REFLET LUMINEUX SUR CARTES (.card-glow)
     ============================================================ */
  function initCardGlow() {
    document.querySelectorAll('.card-glow, .asclepia-card, .stat-card').forEach(function (card) {
      card.classList.add('card-glow');
      card.addEventListener('mousemove', function (e) {
        const rect = card.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width)  * 100;
        const y = ((e.clientY - rect.top)  / rect.height) * 100;
        card.style.setProperty('--mouse-x', x + '%');
        card.style.setProperty('--mouse-y', y + '%');
      });
    });
  }

  /* ============================================================
     7. EFFET RIPPLE SUR BOUTONS
     ============================================================ */
  function initRipple() {
    document.addEventListener('click', function (e) {
      const btn = e.target.closest(
        'button, .btn, .btn-asclepia-primary, .btn-asclepia-secondary, ' +
        '.btn-asclepia-warning, .btn-asclepia-danger, .btn-front-submit, ' +
        '.btn-hero, .main-btn'
      );
      if (!btn) return;
      btn.classList.add('btn-ripple');

      const circle = document.createElement('span');
      circle.className = 'ripple-circle';
      const rect   = btn.getBoundingClientRect();
      const size   = Math.max(rect.width, rect.height) * 2;
      circle.style.cssText =
        'width:' + size + 'px;height:' + size + 'px;' +
        'top:' + (e.clientY - rect.top  - size / 2) + 'px;' +
        'left:' + (e.clientX - rect.left - size / 2) + 'px;';
      btn.appendChild(circle);
      setTimeout(function () { circle.remove(); }, 650);
    });
  }

  /* ============================================================
     8. FLASH MESSAGES ANIMÉS
     ============================================================ */
  function initFlashMessages() {
    // Récupère les messages PHP legacy (divs .alert-*, .flash-*)
    const selectors = [
      '.alert-asclepia-success', '.alert-asclepia-danger',
      '.flash-success-front', '.flash-error-front',
      '.alert.alert-success', '.alert.alert-danger',
      '.alert.alert-warning', '.alert.alert-info'
    ];

    selectors.forEach(function (sel) {
      document.querySelectorAll(sel).forEach(function (el) {
        // Crée un toast stylisé
        const isSuccess = sel.includes('success');
        const isWarning = sel.includes('warning');
        const text      = el.textContent.trim();
        if (!text) return;

        const toast = createToast(
          isSuccess ? '✅' : isWarning ? '⚠️' : '❌',
          isSuccess ? 'Succès'   : isWarning ? 'Attention' : 'Erreur',
          text,
          isSuccess ? 'success'  : isWarning ? 'warning'   : 'error'
        );
        el.style.display = 'none'; // cache l'original
        showToast(toast);
      });
    });
  }

  function createToast(icon, title, text, type) {
    const toast = document.createElement('div');
    toast.className = 'flash-msg flash-' + type;
    toast.innerHTML =
      '<span class="flash-icon">' + icon + '</span>' +
      '<div class="flash-body">' +
        '<div class="flash-title">' + title + '</div>' +
        '<div class="flash-text">' + escHtml(text) + '</div>' +
      '</div>' +
      '<button class="flash-close" aria-label="Fermer">×</button>';

    toast.querySelector('.flash-close').addEventListener('click', function () {
      dismissToast(toast);
    });

    return toast;
  }

  function showToast(toast) {
    let stack = document.querySelector('.flash-stack');
    if (!stack) {
      stack = document.createElement('div');
      stack.className = 'flash-stack';
      document.body.appendChild(stack);
    }
    stack.appendChild(toast);
    // Auto-dismiss après 4.5s (correspond à la barre progressBar 4s)
    setTimeout(function () { dismissToast(toast); }, 4500);
  }

  function dismissToast(toast) {
    if (!toast.parentNode) return;
    toast.classList.add('is-hiding');
    setTimeout(function () { toast.remove(); }, 380);
  }

  function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  /* ============================================================
     9. COMPTEURS ANIMÉS (.counter[data-target])
     ============================================================ */
  function initCounters() {
    if (!('IntersectionObserver' in window)) return;

    const observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        const el     = entry.target;
        const target = parseFloat(el.dataset.target) || 0;
        const dur    = 1400;
        const step   = 16;
        const steps  = dur / step;
        let current  = 0;
        const inc    = target / steps;

        const timer = setInterval(function () {
          current += inc;
          if (current >= target) {
            current = target;
            clearInterval(timer);
          }
          el.textContent = Number.isInteger(target)
            ? Math.round(current).toLocaleString()
            : current.toFixed(1);
        }, step);

        observer.unobserve(el);
      });
    }, { threshold: 0.5 });

    document.querySelectorAll('.counter[data-target], .stat-number[data-target]').forEach(function (el) {
      observer.observe(el);
    });
  }

  /* ============================================================
     10. SHIMMER SUR BOUTONS AU HOVER
     ============================================================ */
  function initBtnShimmer() {
    document.querySelectorAll(
      '.btn-asclepia-primary, .btn-front-submit, .btn-hero, .main-btn.primary-btn'
    ).forEach(function (btn) {
      btn.classList.add('btn-shimmer');
    });
  }

  /* ============================================================
     11. TABLE ROWS — animation d'entrée
     ============================================================ */
  function initTableAnim() {
    document.querySelectorAll('.asclepia-table, table.table').forEach(function (t) {
      t.classList.add('table-anim');
    });
  }

  /* ============================================================
     12. HOVER LIFT SUR CARTES
     ============================================================ */
  function initHoverLift() {
    document.querySelectorAll(
      '.icon-card, .book-card, .product-item, .service-card, .pharmacie-card'
    ).forEach(function (el) {
      el.classList.add('hover-lift');
    });
  }

  /* ============================================================
     13. NAVBAR SCROLL (ajoute classe .scrolled)
     ============================================================ */
  function initNavbarScroll() {
    const navbar = document.querySelector('.navbar, .front-nav, header.header');
    if (!navbar) return;
    function update() {
      navbar.classList.toggle('scrolled', window.scrollY > 20);
    }
    window.addEventListener('scroll', update, { passive: true });
    update();
  }

  /* ============================================================
     14. INPUT ANIM — enveloppe automatique les champs
     ============================================================ */
  function initInputAnim() {
    document.querySelectorAll(
      '.form-group-front, .form-group, .mb-3'
    ).forEach(function (group) {
      const inputs = group.querySelectorAll('input:not([type=file]):not([type=radio]):not([type=checkbox]), textarea');
      inputs.forEach(function (inp) {
        if (!inp.closest('.input-anim')) {
          const wrapper = document.createElement('div');
          wrapper.className = 'input-anim';
          inp.parentNode.insertBefore(wrapper, inp);
          wrapper.appendChild(inp);
        }
      });
    });
  }

  /* ============================================================
     15. LANCEMENT GLOBAL
     ============================================================ */
  function init() {
    initPageTransition();
    initScrollProgress();
    initBgOrbs();
    initParticles();
    initScrollReveal();
    initCardGlow();
    initRipple();
    initFlashMessages();
    initCounters();
    initBtnShimmer();
    initTableAnim();
    initHoverLift();
    initNavbarScroll();
    initInputAnim();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
