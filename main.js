// FILE: assets/js/main.js
document.addEventListener('DOMContentLoaded', function () {
  const body = document.body;

  const mainBtn = document.querySelector('.single_add_to_cart_button');
  const stickyBtn = document.getElementById('sticky-add-btn');
  const sticky = document.querySelector('.sticky-add');
  const variationForm = document.querySelector('.variations_form');
  const mainImage = document.getElementById('product-main-image');
  const thumbs = document.querySelectorAll('.product-thumb');

  const cartDrawer = document.getElementById('cart-drawer');
  const cartOverlay = cartDrawer ? cartDrawer.querySelector('.cart-drawer__overlay') : null;
  const cartCloseBtn = cartDrawer ? cartDrawer.querySelector('.cart-drawer__close') : null;
  const cartTrigger = document.getElementById('header-cart-trigger');

  const megaMenuDesktop = document.getElementById('kangoo-mega-menu-desktop');
  const megaDrawer = document.getElementById('kangoo-mega-menu-drawer');
  const megaOpenBtn = document.getElementById('header-menu-toggle');
  const megaCloseButtons = document.querySelectorAll('[data-mega-menu-close]');
  const megaPanelTriggers = document.querySelectorAll('[data-mega-panel-trigger]');
  const megaPanels = document.querySelectorAll('[data-mega-panel]');

  function isDesktop() {
    return window.innerWidth >= 1024;
  }

  function openCartDrawer() {
    if (!cartDrawer) {
      return;
    }

    closeMegaDrawer();
    closeDesktopMegaMenu();

    cartDrawer.classList.add('is-open');
    body.classList.add('no-scroll');
  }

  function closeCartDrawer() {
    if (!cartDrawer) {
      return;
    }

    cartDrawer.classList.remove('is-open');

    if (!megaDrawer || !megaDrawer.classList.contains('is-open')) {
      body.classList.remove('no-scroll');
    }
  }

  function openMegaDrawer() {
    if (!megaDrawer) {
      return;
    }

    closeCartDrawer();
    closeDesktopMegaMenu();

    megaDrawer.classList.add('is-open');
    megaDrawer.setAttribute('aria-hidden', 'false');

    if (megaOpenBtn) {
      megaOpenBtn.setAttribute('aria-expanded', 'true');
    }

    body.classList.add('no-scroll');
  }

  function closeMegaDrawer() {
    if (!megaDrawer) {
      return;
    }

    megaDrawer.classList.remove('is-open');
    megaDrawer.setAttribute('aria-hidden', 'true');

    if (megaOpenBtn) {
      megaOpenBtn.setAttribute('aria-expanded', 'false');
    }

    if (!cartDrawer || !cartDrawer.classList.contains('is-open')) {
      body.classList.remove('no-scroll');
    }
  }

  function openDesktopMegaMenu() {
    if (!megaMenuDesktop) {
      return;
    }

    closeCartDrawer();
    closeMegaDrawer();

    megaMenuDesktop.classList.add('is-open');

    if (megaOpenBtn) {
      megaOpenBtn.setAttribute('aria-expanded', 'true');
    }
  }

  function closeDesktopMegaMenu() {
    if (!megaMenuDesktop) {
      return;
    }

    megaMenuDesktop.classList.remove('is-open');

    if (megaOpenBtn) {
      megaOpenBtn.setAttribute('aria-expanded', 'false');
    }
  }

  function normalizeMegaPanelKey(panelKey) {
    const normalized = (panelKey || '').toString().trim().toLowerCase();

    if (normalized === 'brand' || normalized === 'brands') {
      return 'brands';
    }

    if (normalized === 'strength' || normalized === 'strengths') {
      return 'strengths';
    }

    if (
      normalized === 'flavour' ||
      normalized === 'flavours' ||
      normalized === 'flavor' ||
      normalized === 'flavors'
    ) {
      return 'flavours';
    }

    return normalized;
  }

  function setActiveMegaPanel(panelKey) {
    const requestedKey = normalizeMegaPanelKey(panelKey);
    const hasPanel = Array.from(megaPanels).some(function (panel) {
      return panel.getAttribute('data-mega-panel') === requestedKey;
    });
    const fallbackKey = megaPanels.length > 0
      ? megaPanels[0].getAttribute('data-mega-panel')
      : '';
    const activeKey = hasPanel ? requestedKey : fallbackKey;

    if (!activeKey) {
      return;
    }

    megaPanelTriggers.forEach(function (trigger) {
      const triggerKey = normalizeMegaPanelKey(trigger.getAttribute('data-mega-panel-trigger'));
      const isActive = triggerKey === activeKey;
      trigger.classList.toggle('is-active', isActive);
      trigger.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });

    megaPanels.forEach(function (panel) {
      const isActive = panel.getAttribute('data-mega-panel') === activeKey;
      panel.classList.toggle('is-active', isActive);
    });
  }

  if (cartTrigger) {
    cartTrigger.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();
      openCartDrawer();
    });
  }

  if (cartCloseBtn) {
    cartCloseBtn.addEventListener('click', closeCartDrawer);
  }

  if (cartOverlay) {
    cartOverlay.addEventListener('click', closeCartDrawer);
  }

  if (megaOpenBtn) {
    megaOpenBtn.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();

      if (isDesktop()) {
        if (megaMenuDesktop && megaMenuDesktop.classList.contains('is-open')) {
          closeDesktopMegaMenu();
        } else {
          openDesktopMegaMenu();
        }
      } else {
        if (megaDrawer && megaDrawer.classList.contains('is-open')) {
          closeMegaDrawer();
        } else {
          openMegaDrawer();
        }
      }
    });
  }

  megaCloseButtons.forEach(function (button) {
    button.addEventListener('click', function (event) {
      event.preventDefault();
      closeMegaDrawer();
    });
  });

  megaPanelTriggers.forEach(function (trigger) {
    const panelKey = trigger.getAttribute('data-mega-panel-trigger');

    trigger.addEventListener('mouseenter', function () {
      if (!isDesktop()) {
        return;
      }

      setActiveMegaPanel(panelKey);
    });

    trigger.addEventListener('focus', function () {
      setActiveMegaPanel(panelKey);
    });

    trigger.addEventListener('click', function (event) {
      event.preventDefault();
      setActiveMegaPanel(panelKey);
    });
  });

  document.addEventListener('click', function (event) {
    const clickedMegaButton = megaOpenBtn && megaOpenBtn.contains(event.target);
    const clickedDesktopMega = megaMenuDesktop && megaMenuDesktop.contains(event.target);
    const clickedMobileMega = megaDrawer && megaDrawer.contains(event.target);
    const clickedCart = cartDrawer && cartDrawer.contains(event.target);
    const clickedCartButton = cartTrigger && cartTrigger.contains(event.target);

    if (!clickedMegaButton && !clickedDesktopMega && isDesktop()) {
      closeDesktopMegaMenu();
    }

    if (!clickedCart && !clickedCartButton) {
      closeCartDrawer();
    }

    if (!clickedMegaButton && !clickedMobileMega && !isDesktop()) {
      closeMegaDrawer();
    }
  });

  window.addEventListener('resize', function () {
    closeMegaDrawer();
    closeDesktopMegaMenu();
  });

  if (mainBtn && stickyBtn) {
    stickyBtn.addEventListener('click', function () {
      mainBtn.click();
    });
  }

  if (sticky && mainBtn) {
    const observer = new IntersectionObserver(
      function ([entry]) {
        sticky.style.transform = entry.isIntersecting ? 'translateY(100%)' : 'translateY(0)';
      },
      { threshold: 0 }
    );

    observer.observe(mainBtn);
  }

  if (variationForm && mainImage && window.jQuery) {
    const originalSrc = mainImage.getAttribute('src');
    const originalAlt = mainImage.getAttribute('alt');

    jQuery(variationForm).on('found_variation', function (event, variation) {
      if (!variation || !variation.image || !variation.image.src) {
        return;
      }

      mainImage.setAttribute('src', variation.image.src);
      mainImage.setAttribute('alt', variation.image.alt || originalAlt);

      thumbs.forEach(function (thumb) {
        thumb.classList.toggle('is-active', thumb.dataset.image === variation.image.src);
      });
    });

    jQuery(variationForm).on('reset_image', function () {
      mainImage.setAttribute('src', originalSrc);
      mainImage.setAttribute('alt', originalAlt);

      thumbs.forEach(function (thumb, index) {
        thumb.classList.toggle('is-active', index === 0);
      });
    });
  }

  if (mainImage && thumbs.length) {
    thumbs.forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        const newSrc = this.dataset.image;
        const newAlt = this.dataset.alt || mainImage.getAttribute('alt');

        if (!newSrc) {
          return;
        }

        mainImage.setAttribute('src', newSrc);
        mainImage.setAttribute('alt', newAlt);

        thumbs.forEach(function (item) {
          item.classList.remove('is-active');
        });

        this.classList.add('is-active');
      });
    });
  }

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeCartDrawer();
      closeMegaDrawer();
      closeDesktopMegaMenu();
    }
  });

  const params = new URLSearchParams(window.location.search);

  if (params.has('add-to-cart')) {
    openCartDrawer();
  }
});