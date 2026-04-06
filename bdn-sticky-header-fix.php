<?php
/**
 * BabyDashNow™ — Stable sticky custom header on mobile/tablet
 * Scope: frontend only
 *
 * Goal:
 * - Hide native Flatsome mobile/tablet white header bar
 * - Keep custom BDN luxe header fixed and visible at all times
 * - Prevent scroll-state flicker / disappearance
 * - Avoid spacer / placeholder bugs
 */

add_action('wp_head', function () {
  if (is_admin()) return;
  ?>
  <style id="bdn-sticky-header-fix">

    @media (max-width: 1080px) {

      /* =========================================================
         1) Hide native Flatsome main white bar
         ========================================================= */
      .header-main,
      .header-main.tooltipstered,
      #masthead .header-main,
      #masthead .header-main.tooltipstered {
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
        height: 0 !important;
        min-height: 0 !important;
        max-height: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        overflow: hidden !important;
      }

      /* =========================================================
         2) Keep custom BabyDashNow header permanently visible
         CSS !important overrides any inline styles Flatsome sets
         ========================================================= */
      .bdn-luxe,
      .bdn-luxe__shell,
      .bdn-luxe__mobile,
      .bdn-luxe__mobilebar {
        opacity: 1 !important;
        visibility: visible !important;
        pointer-events: auto !important;
        transform: translate3d(0,0,0) !important;
        transition: none !important;
        animation: none !important;
      }

      .bdn-luxe {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        z-index: 999999 !important;
        will-change: auto !important;
        backface-visibility: hidden !important;
        -webkit-backface-visibility: hidden !important;
      }

      /* =========================================================
         3) Neutralize Flatsome sticky wrapper classes
         ========================================================= */
      #header,
      #header .header-wrapper,
      .header-wrapper.stuck,
      .header.has-sticky,
      .sticky-shrink,
      .stuck {
        transform: none !important;
        box-shadow: none !important;
        animation: none !important;
        transition: none !important;
      }

      #header,
      #header .header-wrapper {
        background: transparent !important;
      }

      /* =========================================================
         4) Kill placeholders / spacer bars that create jumps
         ========================================================= */
      .bdn-luxe__spacer,
      .sticky-wrapper,
      .stuck-placeholder,
      .header-placeholder {
        display: none !important;
        height: 0 !important;
        min-height: 0 !important;
        max-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
      }

      /* =========================================================
         5) Page offset under fixed custom header
         Adjust 88px if your real mobile header height changes
         ========================================================= */
      body {
        padding-top: 88px !important;
      }

      html {
        scroll-padding-top: 88px !important;
      }

      /* =========================================================
         6) Keep layout clean under the header
         ========================================================= */
      #wrapper,
      #main,
      .page-wrapper,
      .page-inner {
        margin-top: 0 !important;
      }

      /* =========================================================
         7) Block Flatsome from re-showing header-main via JS
         Even if Flatsome toggles display:block inline, this wins
         ========================================================= */
      .header-main[style] {
        display: none !important;
      }
    }
  </style>
  <?php
}, 9999);


add_action('wp_footer', function () {
  if (is_admin()) return;
  ?>
  <script id="bdn-sticky-header-fix-js">
    (function () {
      var MOBILE_MAX = 1080;

      if (window.innerWidth > MOBILE_MAX) return;

      /* ──────────────────────────────────────────────────────────
         1) Strip Flatsome body classes that trigger sticky logic
         ────────────────────────────────────────────────────────── */
      var killClasses = [
        'sticky-mobile-header',
        'sticky-header',
        'header-has-sticky',
        'sticky-add-to-cart--active',
        'stuck'
      ];

      function stripBodyClasses() {
        killClasses.forEach(function (cls) {
          document.body.classList.remove(cls);
        });
      }

      /* ──────────────────────────────────────────────────────────
         2) One-time: neutralize Flatsome sticky JS on header
         ────────────────────────────────────────────────────────── */
      function neutralizeFlatsome() {
        /* Remove any inline style Flatsome may have set on header-main */
        document.querySelectorAll('.header-main').forEach(function (el) {
          el.removeAttribute('style');
        });

        /* Ensure .bdn-luxe has no leftover inline transforms */
        document.querySelectorAll('.bdn-luxe').forEach(function (el) {
          el.style.removeProperty('transform');
          el.style.removeProperty('top');
          el.style.removeProperty('opacity');
          el.style.removeProperty('visibility');
        });

        stripBodyClasses();
      }

      /* ──────────────────────────────────────────────────────────
         3) MutationObserver — only watch body class changes
            (NOT the entire subtree, which causes infinite loops)
         ────────────────────────────────────────────────────────── */
      function watchBodyClasses() {
        var observer = new MutationObserver(function (mutations) {
          for (var i = 0; i < mutations.length; i++) {
            if (mutations[i].attributeName === 'class') {
              stripBodyClasses();
              break;
            }
          }
        });

        observer.observe(document.body, {
          attributes: true,
          attributeFilter: ['class']
        });
      }

      /* ──────────────────────────────────────────────────────────
         4) Watch .header-main for Flatsome re-showing it
            Scoped to just that element, not the whole DOM
         ────────────────────────────────────────────────────────── */
      function watchHeaderMain() {
        document.querySelectorAll('.header-main').forEach(function (el) {
          var obs = new MutationObserver(function () {
            el.removeAttribute('style');
          });
          obs.observe(el, {
            attributes: true,
            attributeFilter: ['style', 'class']
          });
        });
      }

      /* ──────────────────────────────────────────────────────────
         5) Init — run once, no scroll listener needed
            CSS !important handles everything during scroll
         ────────────────────────────────────────────────────────── */
      function init() {
        neutralizeFlatsome();
        watchBodyClasses();
        watchHeaderMain();

        /* Re-run once after Flatsome's own init fires (usually ~300ms) */
        setTimeout(neutralizeFlatsome, 400);
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
      } else {
        init();
      }

      window.addEventListener('load', neutralizeFlatsome);

      /* Only re-check on resize (orientation change), not scroll */
      window.addEventListener('resize', function () {
        if (window.innerWidth <= MOBILE_MAX) {
          neutralizeFlatsome();
        }
      });
    })();
  </script>
  <?php
}, 9999);
