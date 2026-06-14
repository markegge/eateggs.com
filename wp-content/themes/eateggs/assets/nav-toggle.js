/**
 * Mobile nav hamburger toggle.
 *
 * Opens/closes the primary nav dropdown on small screens by flipping a
 * `nav-open` class on the <header class="s-head"> (the CSS keys off that class)
 * and keeping the toggle button's aria-expanded in sync. Vanilla JS, no deps,
 * and defensive: it bails out quietly if the expected markup is missing.
 */
(function () {
  'use strict';

  // Must match the @media (max-width:760px) breakpoint in styles.css.
  var BREAKPOINT = 760;

  // Run once the DOM is parsed (the script is enqueued in the footer, but guard anyway).
  function ready(fn) {
    if (document.readyState !== 'loading') {
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  }

  ready(function () {
    var toggle = document.querySelector('.nav-toggle');
    var header = document.querySelector('.s-head');
    var nav = document.getElementById('primary-nav');

    // Nothing to wire up if any piece of the header markup is absent.
    if (!toggle || !header || !nav) {
      return;
    }

    function isOpen() {
      return header.classList.contains('nav-open');
    }

    function openMenu() {
      header.classList.add('nav-open');
      toggle.setAttribute('aria-expanded', 'true');
    }

    function closeMenu(returnFocus) {
      header.classList.remove('nav-open');
      toggle.setAttribute('aria-expanded', 'false');
      if (returnFocus) {
        toggle.focus();
      }
    }

    // Tap the hamburger to toggle the panel.
    toggle.addEventListener('click', function (event) {
      event.preventDefault();
      if (isOpen()) {
        closeMenu(false);
      } else {
        openMenu();
      }
    });

    // Tapping a link inside the panel closes it (navigation or same-page anchor).
    nav.addEventListener('click', function (event) {
      if (isOpen() && event.target.closest && event.target.closest('a')) {
        closeMenu(false);
      }
    });

    // Escape closes the panel and returns focus to the toggle.
    document.addEventListener('keydown', function (event) {
      if (isOpen() && (event.key === 'Escape' || event.key === 'Esc')) {
        closeMenu(true);
      }
    });

    // A click/tap anywhere outside the header closes the panel.
    document.addEventListener('click', function (event) {
      if (isOpen() && !header.contains(event.target)) {
        closeMenu(false);
      }
    });

    // Resizing up to desktop resets state so the panel never sticks open.
    var resizeTimer;
    window.addEventListener('resize', function () {
      window.clearTimeout(resizeTimer);
      resizeTimer = window.setTimeout(function () {
        if (isOpen() && window.innerWidth > BREAKPOINT) {
          closeMenu(false);
        }
      }, 150);
    });
  });
})();
