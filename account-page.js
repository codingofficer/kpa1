/* ========================================================================
FILE: assets/js/account-page.js
========================================================================= */

document.addEventListener('DOMContentLoaded', function () {
  const accountRoot = document.querySelector('.woocommerce-account .woocommerce');

  if (!accountRoot) {
    return;
  }

  const nav = accountRoot.querySelector('.woocommerce-MyAccount-navigation');
  const content = accountRoot.querySelector('.woocommerce-MyAccount-content');

  if (!nav || !content) {
    return;
  }

  const currentLink = nav.querySelector('li.is-active a');
  const currentLabel = currentLink ? currentLink.textContent.trim() : (window.kangooAccountPage?.menu_label || 'Account menu');

  const toggle = document.createElement('button');
  toggle.type = 'button';
  toggle.className = 'account-mobile-toggle';
  toggle.setAttribute('aria-expanded', 'false');
  toggle.innerHTML = `
    <span class="account-mobile-toggle__label">${currentLabel}</span>
    <span class="account-mobile-toggle__icon" aria-hidden="true">+</span>
  `;

  nav.parentNode.insertBefore(toggle, nav);

  function closeMenu() {
    accountRoot.classList.remove('is-account-menu-open');
    toggle.setAttribute('aria-expanded', 'false');
    const icon = toggle.querySelector('.account-mobile-toggle__icon');
    if (icon) {
      icon.textContent = '+';
    }
  }

  function openMenu() {
    accountRoot.classList.add('is-account-menu-open');
    toggle.setAttribute('aria-expanded', 'true');
    const icon = toggle.querySelector('.account-mobile-toggle__icon');
    if (icon) {
      icon.textContent = '−';
    }
  }

  toggle.addEventListener('click', function () {
    if (accountRoot.classList.contains('is-account-menu-open')) {
      closeMenu();
      return;
    }

    openMenu();
  });

  nav.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', function () {
      closeMenu();
    });
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeMenu();
    }
  });

  const passwordInputs = content.querySelectorAll('input[type="password"]');

  passwordInputs.forEach(function (input) {
    const wrapper = document.createElement('div');
    wrapper.className = 'account-password-wrap';
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'account-password-toggle';
    button.setAttribute('aria-label', 'Toggle password visibility');
    button.textContent = 'Show';

    button.addEventListener('click', function () {
      const isPassword = input.getAttribute('type') === 'password';
      input.setAttribute('type', isPassword ? 'text' : 'password');
      button.textContent = isPassword ? 'Hide' : 'Show';
    });

    wrapper.appendChild(button);
  });
});