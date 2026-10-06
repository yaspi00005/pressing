/* ==========================================================================
   Pressing — comportements de l'interface (aucun script n'est écrit dans les pages).
   Les éléments sont repérés par des attributs data-… ou des classes js-… ;
   chaque fonction est isolée : une erreur dans l'une n'empêche pas les autres.
   ========================================================================== */
(function () {
  'use strict';

  function isole(nom, fn) {
    try { fn(); } catch (e) { if (window.console) console.error('[pressing] ' + nom, e); }
  }
  function pret(fn) {
    if (document.readyState !== 'loading') { fn(); } else { document.addEventListener('DOMContentLoaded', fn); }
  }
  function monnaie(n, devise) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' ' + (devise || 'FCFA');
  }

  pret(function () {

    /* ---------- Confirmation avant une action sensible (formulaire ou lien) ---------- */
    isole('confirmation', function () {
      var modalEl = document.getElementById('modal-confirmation');
      if (!modalEl || !window.bootstrap) return;
      var modal = new bootstrap.Modal(modalEl);
      var suite = null;
      var ok = document.getElementById('modal-confirmation-ok');

      function demander(el, action) {
        document.getElementById('modal-confirmation-titre').textContent = el.getAttribute('data-confirm-titre') || 'Confirmer';
        document.getElementById('modal-confirmation-message').textContent = el.getAttribute('data-confirm');
        ok.textContent = el.getAttribute('data-confirm-bouton') || 'Confirmer';
        ok.className = 'btn btn-' + (el.getAttribute('data-confirm-type') || 'primary');
        suite = action;
        modal.show();
      }
      ok.addEventListener('click', function () { var f = suite; suite = null; modal.hide(); if (f) f(); });

      document.addEventListener('submit', function (e) {
        var f = e.target;
        if (f.matches && f.matches('form[data-confirm]') && !f.dataset.confirme) {
          e.preventDefault();
          demander(f, function () { f.dataset.confirme = '1'; if (f.requestSubmit) f.requestSubmit(); else f.submit(); });
        }
      });
      document.addEventListener('click', function (e) {
        var a = e.target.closest ? e.target.closest('a[data-confirm]') : null;
        if (a) { e.preventDefault(); demander(a, function () { window.location.href = a.href; }); }
      });
    });

    /* ---------- Fenêtres à ouvrir d'office (ex. erreurs d'un formulaire) ---------- */
    isole('ouvrir-modal', function () {
      if (!window.bootstrap) return;
      document.querySelectorAll('[data-ouvrir-modal]').forEach(function (el) { new bootstrap.Modal(el).show(); });
    });

    /* ---------- Listes déroulantes avec recherche (clients, articles) ---------- */
    isole('select2', function () {
      if (!window.jQuery || !jQuery.fn.select2) return;
      jQuery('.js-select2').each(function () {
        jQuery(this).select2({ width: '100%', placeholder: this.getAttribute('data-placeholder') || '', language: { noResults: function () { return 'Aucun résultat'; } } });
      });
    });

    /* ---------- Filtres : envoi automatique au changement ---------- */
    isole('auto-submit', function () {
      document.querySelectorAll('.js-auto-submit').forEach(function (el) {
        el.addEventListener('change', function () { if (el.form) el.form.submit(); });
      });
    });

    /* ---------- Impression ---------- */
    isole('impression', function () {
      document.addEventListener('click', function (e) {
        if (e.target.closest && e.target.closest('[data-print]')) { e.preventDefault(); window.print(); }
      });
      if (document.body.hasAttribute('data-auto-print') && location.hash === '#print') { window.print(); }
    });

    /* ---------- Graphiques (Chart.js) : <canvas data-chart='{"type":"bar",...}'> ---------- */
    isole('graphiques', function () {
      if (!window.Chart) return;
      document.querySelectorAll('canvas[data-chart]').forEach(function (c) {
        var cfg = JSON.parse(c.getAttribute('data-chart'));
        var couleur = getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#00A15D';
        (cfg.data.datasets || []).forEach(function (d) {
          if (!d.backgroundColor) d.backgroundColor = couleur;
          if (cfg.type === 'bar' && d.maxBarThickness === undefined) d.maxBarThickness = 28;
        });
        cfg.options = Object.assign({ responsive: true, maintainAspectRatio: false, legend: { display: cfg.type !== 'bar', position: 'bottom', labels: { boxWidth: 10, fontSize: 11 } } }, cfg.options || {});
        if (cfg.type === 'bar') {
          cfg.options.scales = { yAxes: [{ ticks: { beginAtZero: true, precision: 0, fontSize: 10 }, gridLines: { color: '#eef0f3' } }], xAxes: [{ ticks: { fontSize: 10 }, gridLines: { display: false } }] };
        }
        new Chart(c.getContext('2d'), cfg);
      });
    });

    /* ---------- Formulaire de commande : lignes d'articles, prix automatiques, totaux en direct ---------- */
    isole('commande', function () {
      var form = document.getElementById('commande-form');
      if (!form) return;
      var conteneur = document.getElementById('lignes');
      var TARIFS = JSON.parse(form.getAttribute('data-tarifs') || '{}');
      var BASE = JSON.parse(form.getAttribute('data-base') || '{}');
      var MAJORATION = parseInt(form.getAttribute('data-majoration'), 10) || 0;
      var DEVISE = form.getAttribute('data-devise') || 'FCFA';
      var $ = window.jQuery;

      function champ(suffixe) { return form.querySelector('[name$="[' + suffixe + ']"]'); }
      function valeur(suffixe) { var c = champ(suffixe); return c ? (parseInt(c.value, 10) || 0) : 0; }
      function ligneEl(el) { return el.closest('.ligne-commande'); }

      function majPrix(ligne) {
        var a = ligne.querySelector('.ligne-article').value;
        var s = ligne.querySelector('.ligne-service').value;
        if (!a) return;
        var p = TARIFS[a + '-' + s];
        if (p === undefined) p = BASE[a];
        if (p !== undefined) ligne.querySelector('.ligne-prix').value = p;
      }

      function recalculer() {
        var sous = 0;
        conteneur.querySelectorAll('.ligne-commande').forEach(function (l) {
          var q = parseInt(l.querySelector('.ligne-qte').value, 10) || 0;
          var p = parseInt(l.querySelector('.ligne-prix').value, 10) || 0;
          sous += q * p;
          var m = l.querySelector('.ligne-montant');
          if (m) m.textContent = monnaie(q * p, DEVISE);
        });
        var urgent = champ('urgent').checked;
        var domicile = champ('modeLivraison').value === 'domicile';
        var maj = urgent ? sous * MAJORATION / 100 : 0;
        var liv = domicile ? valeur('fraisLivraison') : 0;
        var remise = valeur('remise');
        var total = Math.max(0, sous + maj + liv - remise);
        function set(id, v) { var e = document.getElementById(id); if (e) e.textContent = monnaie(v, DEVISE); }
        set('r-sous-total', sous); set('r-majoration', maj); set('r-livraison', liv); set('r-remise', -remise); set('r-total', total);
        document.getElementById('r-ligne-majoration').classList.toggle('d-none', !urgent);
        document.getElementById('r-ligne-livraison').classList.toggle('d-none', !domicile);
        var reste = document.getElementById('r-reste');
        if (reste) reste.textContent = monnaie(Math.max(0, total - valeur('acompte')), DEVISE);
        form.querySelectorAll('.bloc-domicile').forEach(function (b) { b.classList.toggle('d-none', !domicile); });
      }

      function ajouterLigne() {
        var i = parseInt(conteneur.getAttribute('data-index'), 10);
        conteneur.setAttribute('data-index', i + 1);
        conteneur.insertAdjacentHTML('beforeend', conteneur.getAttribute('data-prototype').replace(/__name__/g, i));
        var l = conteneur.lastElementChild;
        l.querySelector('.ligne-qte').value = 1;
        if ($ && $.fn.select2) { $(l).find('.ligne-article').select2({ width: '100%', placeholder: 'Article…' }); }
        recalculer();
      }

      document.getElementById('ajouter-ligne').addEventListener('click', ajouterLigne);
      conteneur.addEventListener('click', function (e) {
        var b = e.target.closest('.ligne-supprimer');
        if (b) { ligneEl(b).remove(); recalculer(); }
      });
      // select2 déclenche des événements jQuery : on les relaie aussi.
      function surChangement(e) {
        var l = ligneEl(e.target);
        if (l && (e.target.matches('.ligne-article') || e.target.matches('.ligne-service'))) majPrix(l);
        recalculer();
      }
      conteneur.addEventListener('change', surChangement);
      if ($) { $(conteneur).on('select2:select', '.ligne-article', function (e) { surChangement({ target: e.currentTarget }); }); }
      form.addEventListener('input', recalculer);
      form.addEventListener('change', recalculer);
      if ($ && $.fn.select2) { $('.ligne-article').select2({ width: '100%', placeholder: 'Article…' }); }
      if (!conteneur.children.length) ajouterLigne();
      recalculer();
    });

    /* ---------- Application installable (PWA) ---------- */
    isole('pwa', function () {
      if ('serviceWorker' in navigator && location.protocol === 'https:' || location.hostname === 'localhost') {
        var base = document.querySelector('link[rel="manifest"]');
        if (base && 'serviceWorker' in navigator) {
          navigator.serviceWorker.register(base.href.replace('manifest.webmanifest', 'sw.js')).catch(function () {});
        }
      }
    });
  });
})();
