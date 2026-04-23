/* ========================================================================
FILE: assets/js/account-drawer.js
========================================================================= */
jQuery(function ($) {
  const selectors = {
    drawer: '#account-drawer',
    message: '[data-account-message]',
    authWrap: '[data-account-auth]',
    loggedInWrap: '[data-account-logged-in]',
    summary: '[data-account-summary]'
  };

  const $drawer = $(selectors.drawer);

  if (!$drawer.length) {
    return;
  }

  function getAjaxUrl() {
    return window.kangooAccount && kangooAccount.ajax_url ? kangooAccount.ajax_url : '';
  }

  function getAccountUrl() {
    return window.kangooAccount && kangooAccount.account_url ? kangooAccount.account_url : '/my-account/';
  }

  function openDrawer(defaultTab) {
    $drawer.addClass('is-open').attr('aria-hidden', 'false');
    $('body').addClass('no-scroll');

    if (defaultTab) {
      switchTab(defaultTab);
    }

    refreshStatus();
  }

  function closeDrawer() {
    $drawer.removeClass('is-open').attr('aria-hidden', 'true');
    $('body').removeClass('no-scroll');
    clearMessage();
  }

  function switchTab(tab) {
    $drawer.find('[data-account-tab]').removeClass('is-active').attr('aria-selected', 'false');
    $drawer.find(`[data-account-tab="${tab}"]`).addClass('is-active').attr('aria-selected', 'true');

    $drawer.find('[data-account-pane]').removeClass('is-active');
    $drawer.find(`[data-account-pane="${tab}"]`).addClass('is-active');

    clearMessage();
  }

  function clearMessage() {
    $drawer.find(selectors.message).removeClass('is-error is-success').empty();
  }

  function showMessage(text, type) {
    const $message = $drawer.find(selectors.message);
    $message.removeClass('is-error is-success');

    if (!text) {
      $message.empty();
      return;
    }

    $message.addClass(type === 'success' ? 'is-success' : 'is-error').text(text);
  }

  function renderLoggedIn(user) {
    const name = user && user.display_name ? user.display_name : 'Customer';
    const email = user && user.email ? user.email : '';
    const accountUrl = user && user.account_url ? user.account_url : getAccountUrl();

    $drawer.find(selectors.authWrap).addClass('is-hidden');
    $drawer.find(selectors.loggedInWrap).removeClass('is-hidden');
    $drawer.find(selectors.summary).html(`
      <div class="account-summary__name">${name}</div>
      <div class="account-summary__email">${email}</div>
      <div class="account-summary__link"><a href="${accountUrl}">Open my account</a></div>
    `);
  }

  function renderLoggedOut() {
    $drawer.find(selectors.loggedInWrap).addClass('is-hidden');
    $drawer.find(selectors.authWrap).removeClass('is-hidden');
  }

  function setFormLoading($form, loading) {
    const $submit = $form.find('.account-submit');
    const originalText = $submit.data('original-text') || $.trim($submit.text());

    if (!$submit.data('original-text')) {
      $submit.data('original-text', originalText);
    }

    if (loading) {
      $submit.prop('disabled', true).addClass('is-disabled').text($submit.data('loading-text') || 'Loading...');
      return;
    }

    $submit.prop('disabled', false).removeClass('is-disabled').text($submit.data('original-text'));
  }

  function ajaxPost(action, nonce, payload) {
    return $.ajax({
      url: getAjaxUrl(),
      type: 'POST',
      dataType: 'json',
      data: $.extend({}, payload, {
        action: action,
        nonce: nonce
      })
    });
  }

  function refreshStatus() {
    if (!window.kangooAccount) {
      return;
    }

    ajaxPost('kangoo_account_status', kangooAccount.status_nonce, {})
      .done(function (response) {
        const data = response && response.data ? response.data : {};

        if (response && response.success && data.logged_in) {
          renderLoggedIn(data.user || {});
          return;
        }

        renderLoggedOut();
      })
      .fail(function () {
        renderLoggedOut();
      });
  }

  $(document).on('click', '[data-account-open]', function (event) {
    event.preventDefault();
    const tab = $(this).data('account-open') || 'login';
    openDrawer(tab);
  });

  $drawer.on('click', '[data-account-close]', function () {
    closeDrawer();
  });

  $(document).on('keydown', function (event) {
    if (event.key === 'Escape' && $drawer.hasClass('is-open')) {
      closeDrawer();
    }
  });

  $drawer.on('click', '[data-account-tab]', function () {
    switchTab($(this).data('account-tab'));
  });

  $drawer.on('submit', '[data-account-form="login"]', function (event) {
    event.preventDefault();

    const $form = $(this);
    clearMessage();
    setFormLoading($form, true);

    ajaxPost('kangoo_account_login', kangooAccount.login_nonce, {
      login: $form.find('[name="login"]').val(),
      password: $form.find('[name="password"]').val(),
      remember: $form.find('[name="remember"]').is(':checked') ? 1 : 0
    }).done(function (response) {
      const data = response && response.data ? response.data : {};
      showMessage(data.message || kangooAccount.login_success, 'success');
      renderLoggedIn(data.user || {});

      window.setTimeout(function () {
        window.location.href = (data.user && data.user.account_url) ? data.user.account_url : getAccountUrl();
      }, 500);
    }).fail(function (xhr) {
      const response = xhr && xhr.responseJSON ? xhr.responseJSON : {};
      const data = response && response.data ? response.data : {};
      showMessage(data.message || 'Unable to sign in.', 'error');
    }).always(function () {
      setFormLoading($form, false);
    });
  });

  $drawer.on('submit', '[data-account-form="register"]', function (event) {
    event.preventDefault();

    const $form = $(this);
    clearMessage();
    setFormLoading($form, true);

    ajaxPost('kangoo_account_register', kangooAccount.register_nonce, {
      first_name: $form.find('[name="first_name"]').val(),
      last_name: $form.find('[name="last_name"]').val(),
      email: $form.find('[name="email"]').val(),
      password: $form.find('[name="password"]').val(),
      confirm_password: $form.find('[name="confirm_password"]').val()
    }).done(function (response) {
      const data = response && response.data ? response.data : {};
      showMessage(data.message || kangooAccount.register_success, 'success');
      renderLoggedIn(data.user || {});

      window.setTimeout(function () {
        window.location.href = (data.user && data.user.account_url) ? data.user.account_url : getAccountUrl();
      }, 500);
    }).fail(function (xhr) {
      const response = xhr && xhr.responseJSON ? xhr.responseJSON : {};
      const data = response && response.data ? response.data : {};
      showMessage(data.message || 'Unable to create account.', 'error');
    }).always(function () {
      setFormLoading($form, false);
    });
  });

  $drawer.on('click', '[data-account-logout]', function () {
    clearMessage();

    ajaxPost('kangoo_account_logout', kangooAccount.logout_nonce, {})
      .done(function () {
        renderLoggedOut();
        switchTab('login');
        showMessage('Signed out successfully.', 'success');

        window.setTimeout(function () {
          window.location.href = window.location.href;
        }, 300);
      })
      .fail(function () {
        showMessage('Unable to sign out right now.', 'error');
      });
  });
});