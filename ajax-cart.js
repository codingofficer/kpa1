// FILE: assets/js/ajax-cart.js
jQuery(function ($) {
  const singleAddLabel = 'ADD TO CART';

  function getPriceEl($form) {
    return $form.closest('.product-page').find('#product-price').first();
  }

  function ensureDefaultPriceStored($price) {
    if (!$price.length) {
      return;
    }

    if (!$price.attr('data-default-html')) {
      $price.attr('data-default-html', $price.html());
    }
  }

  function parsePriceNumber(input) {
    const text = $.trim($('<div>').html(input || '').text());

    if (!text) {
      return null;
    }

    const match = text.match(/-?\d[\d,.]*/);
    if (!match) {
      return null;
    }

    let value = match[0];

    if (value.indexOf(',') > -1 && value.indexOf('.') > -1) {
      value = value.replace(/,/g, '');
    } else {
      value = value.replace(/,/g, '.');
    }

    const parsed = parseFloat(value);
    return Number.isFinite(parsed) ? parsed : null;
  }

  function formatCurrency(amount) {
    if (!Number.isFinite(amount)) {
      return '';
    }

    try {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP'
      }).format(amount);
    } catch (error) {
      return `£${amount.toFixed(2)}`;
    }
  }

	function buildButtonHtml(label, total) {
	  const text = total ? `${label} - ${total}` : label;
	  return `<span class="button-text">${text}</span>`;
	}

  function syncStrengthPills($form) {
    $form.closest('.product-page').find('.strength-options').each(function () {
      const $group = $(this);
      const attribute = $group.data('attribute');

      if (!attribute) {
        return;
      }

      const $select = $form.find(`select[name="${attribute}"]`);

      if (!$select.length) {
        return;
      }

      const selectedValue = String($select.val() || '');

      $group.find('.strength-option').each(function () {
        const $button = $(this);
        const isActive = String($button.data('value')) === selectedValue;

        $button.toggleClass('is-active', isActive);
        $button.attr('aria-pressed', isActive ? 'true' : 'false');
      });
    });
  }

  function updateDisplayedPrice($form, variation) {
    const $price = getPriceEl($form);

    if (!$price.length) {
      return;
    }

    ensureDefaultPriceStored($price);

    if (variation && variation.price_html) {
      $price.html(variation.price_html);
      return;
    }

    $price.html($price.attr('data-default-html'));
  }

  function getCurrentSingleUnitPrice($form, variation) {
    if (variation && typeof variation.display_price === 'number') {
      return variation.display_price;
    }

    if ($form.hasClass('variations_form')) {
      const variationId = parseInt($form.find('input[name="variation_id"]').val(), 10) || 0;

      if (!variationId) {
        return null;
      }
    }

    const $price = getPriceEl($form);
    if (!$price.length) {
      return null;
    }

    return parsePriceNumber($price.html());
  }

  function syncSingleAddButtonTotal($form, variation) {
    const $button = $form.find('.single_add_to_cart_button').first();

    if (!$button.length) {
      return;
    }

    const qty = Math.max(1, parseInt($form.find('input.qty').val(), 10) || 1);
    const unitPrice = getCurrentSingleUnitPrice($form, variation);

    if (!Number.isFinite(unitPrice)) {
      const idleHtml = buildButtonHtml(singleAddLabel, '');
      $button.data('idle-html', idleHtml);

      if (!$button.hasClass('is-loading') && !$button.hasClass('is-added')) {
        $button.html(idleHtml);
      }

      return;
    }

    const total = formatCurrency(unitPrice * qty);
    const idleHtml = buildButtonHtml(singleAddLabel, total);

    $button.data('idle-html', idleHtml);

    if (!$button.hasClass('is-loading') && !$button.hasClass('is-added')) {
      $button.html(idleHtml);
    }
  }

  function enhanceQuantityButtons() {
    $('.product-cart .quantity').each(function () {
      const $qty = $(this);

      if ($qty.find('.qty-btn').length) {
        return;
      }

      $qty.prepend('<button type="button" class="qty-btn qty-btn--minus" aria-label="Decrease quantity">-</button>');
      $qty.append('<button type="button" class="qty-btn qty-btn--plus" aria-label="Increase quantity">+</button>');
    });
  }

  function showTemporaryButtonState($button, html, duration) {
    if (!$button.length) {
      return;
    }

    if (!$button.data('original-html')) {
      $button.data('original-html', $button.html());
    }

    $button.addClass('is-added').html(html);

    window.setTimeout(function () {
      $button.removeClass('is-added').html($button.data('original-html'));
    }, duration || 1200);
  }

  $(document).on('click', '.strength-option', function () {
    const $button = $(this);
    const $group = $button.closest('.strength-options');
    const attribute = $group.data('attribute');
    const $form = $button.closest('.product-page').find('.variations_form').first();

    if (!attribute || !$form.length) {
      return;
    }

    const $select = $form.find(`select[name="${attribute}"]`);

    if (!$select.length) {
      return;
    }

    $select.val($button.data('value')).trigger('change');
  });

  $(document).on('woocommerce_variation_has_changed', '.variations_form', function () {
    syncStrengthPills($(this));
    syncSingleAddButtonTotal($(this), null);
  });

  $(document).on('found_variation', '.variations_form', function (event, variation) {
    const $form = $(this);
    syncStrengthPills($form);
    updateDisplayedPrice($form, variation);
    syncSingleAddButtonTotal($form, variation);
  });

  $(document).on('reset_data hide_variation', '.variations_form', function () {
    const $form = $(this);
    syncStrengthPills($form);
    updateDisplayedPrice($form, null);
    syncSingleAddButtonTotal($form, null);
  });

  $(document).on('click', '.product-cart .qty-btn', function () {
    const $wrap = $(this).closest('.quantity');
    const $input = $wrap.find('input.qty');

    if (!$input.length) {
      return;
    }

    let value = parseInt($input.val(), 10) || 1;
    const min = parseInt($input.attr('min'), 10) || 1;
    const maxAttr = parseInt($input.attr('max'), 10);
    const max = Number.isFinite(maxAttr) && maxAttr > 0 ? maxAttr : 999;

    if ($(this).hasClass('qty-btn--plus')) {
      value = Math.min(value + 1, max);
    } else {
      value = Math.max(value - 1, min);
    }

    $input.val(value).trigger('change');
  });

  $(document).on('change input', '.product-cart input.qty', function () {
    const $form = $(this).closest('form.cart');

    if (!$form.length) {
      return;
    }

    syncSingleAddButtonTotal($form, null);
  });

  $(document.body).on('added_to_cart', function (event, fragments, cartHash, $button) {
    if ($button && $button.length && !$button.hasClass('single_add_to_cart_button')) {
      showTemporaryButtonState($button, 'Added', 1200);
    }
  });

  $('.variations_form').each(function () {
    const $form = $(this);
    const $price = getPriceEl($form);

    ensureDefaultPriceStored($price);
    syncStrengthPills($form);
    updateDisplayedPrice($form, null);
    syncSingleAddButtonTotal($form, null);
  });

  $('.product-page .product-cart form.cart').each(function () {
    syncSingleAddButtonTotal($(this), null);
  });

  enhanceQuantityButtons();
});

jQuery(function ($) {
  $(document).on('click', '.cart-drawer .woocommerce-mini-cart-item .remove, .cart-drawer .mini-cart-remove', function (e) {
    e.preventDefault();
    e.stopPropagation();

    if (!window.kangooAjaxCart || !kangooAjaxCart.ajax_url || !kangooAjaxCart.remove_nonce) {
      return;
    }

    const url = this.getAttribute('href');
    if (!url) {
      return;
    }

    const parsedUrl = new URL(url, window.location.origin);
    const key = parsedUrl.searchParams.get('remove_item');

    if (!key) {
      return;
    }

    const $drawer = $('#cart-drawer');
    const $content = $drawer.find('.cart-drawer__content');
    const $cartBadge = $('.cart-badge');
    const $remove = $(this);

    $remove.css('pointer-events', 'none').css('opacity', 0.45);

    $.ajax({
      url: kangooAjaxCart.ajax_url,
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'kangoo_remove_mini_cart_item',
        nonce: kangooAjaxCart.remove_nonce,
        cart_item_key: key
      }
    }).done(function (response) {
      if (!response || !response.success || !response.data) {
        return;
      }

      if (response.data.mini_cart_html) {
        $content.html(response.data.mini_cart_html);
      }

      if (response.data.cart_badge_html) {
        $cartBadge.replaceWith(response.data.cart_badge_html);
      }

      $(document.body).trigger('wc_fragments_refreshed');
    }).always(function () {
      $remove.css('pointer-events', '').css('opacity', '');
    });
  });
});

jQuery(function ($) {
  function enhanceMiniCartQty() {
    $('.cart-drawer .woocommerce-mini-cart-item').each(function () {
      const $item = $(this);
      const $qty = $item.find('.quantity').first();

      if (!$qty.length || $item.find('.mini-cart-remove').length) {
        return;
      }

      const text = $.trim($qty.text());
      const match = text.match(/^(\d+)/);
      const value = match ? parseInt(match[1], 10) : 1;

      const $remove = $item.find('.remove').first();
      const removeUrl = $remove.attr('href') || '';

      $qty.html(`
        <button type="button" class="qty-btn qty-btn--minus" aria-label="Decrease quantity">-</button>
        <input type="number" class="mini-qty" value="${value}" min="1" aria-label="Quantity">
        <button type="button" class="qty-btn qty-btn--plus" aria-label="Increase quantity">+</button>
      `);

      $qty.attr('data-remove-url', removeUrl);

      if (removeUrl) {
        $qty.after(`
          <a href="${removeUrl}" class="mini-cart-remove" aria-label="Remove item from cart">
            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
              <path d="M9 3h6l1 2h4v2H4V5h4l1-2zm1 6h2v8h-2V9zm4 0h2v8h-2V9zM7 9h2v8H7V9zm1 11h8a2 2 0 0 0 2-2V8H6v10a2 2 0 0 0 2 2z" fill="currentColor"></path>
            </svg>
          </a>
        `);
      }

      $remove.remove();
    });
  }

  function scheduleEnhanceMiniCartQty() {
    enhanceMiniCartQty();
    window.setTimeout(enhanceMiniCartQty, 50);
    window.setTimeout(enhanceMiniCartQty, 150);
    window.setTimeout(enhanceMiniCartQty, 300);
  }

  scheduleEnhanceMiniCartQty();

  $(window).on('load', function () {
    scheduleEnhanceMiniCartQty();
  });

  $(document.body).on('wc_fragments_refreshed added_to_cart', function () {
    scheduleEnhanceMiniCartQty();
  });
});

jQuery(function ($) {
  $(document).on('click', '.cart-drawer .qty-btn', function () {
    const $wrap = $(this).closest('.quantity');
    const $input = $wrap.find('.mini-qty');

    if (!$input.length) {
      return;
    }

    let val = parseInt($input.val(), 10) || 1;

    if ($(this).hasClass('qty-btn--plus')) {
      val += 1;
    } else {
      val = Math.max(1, val - 1);
    }

    $input.val(val).trigger('change');
  });
});

jQuery(function ($) {
  $(document).on('change', '.cart-drawer .mini-qty', function () {
    const $input = $(this);
    const qty = Math.max(1, parseInt($input.val(), 10) || 1);

    const $wrap = $input.closest('.quantity');
    const removeUrl = $wrap.data('remove-url');

    if (!removeUrl || !window.kangooAjaxCart) {
      return;
    }

    const url = new URL(removeUrl, window.location.origin);
    const key = url.searchParams.get('remove_item');

    if (!key) {
      return;
    }

    const $drawer = $('#cart-drawer');
    const $content = $drawer.find('.cart-drawer__content');
    const $cartBadge = $('.cart-badge');

    $input.prop('disabled', true);
    $wrap.addClass('is-updating');

    $.ajax({
      url: kangooAjaxCart.ajax_url,
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'kangoo_update_mini_cart_quantity',
        nonce: kangooAjaxCart.nonce,
        cart_item_key: key,
        quantity: qty
      }
    }).done(function (response) {
      if (!response || !response.success || !response.data) {
        return;
      }

      if (response.data.mini_cart_html) {
        $content.html(response.data.mini_cart_html);
      }

      if (response.data.cart_badge_html) {
        $cartBadge.replaceWith(response.data.cart_badge_html);
      }

      $(document.body).trigger('wc_fragments_refreshed');
    }).fail(function () {
      window.location.reload();
    }).always(function () {
      $input.prop('disabled', false);
      $wrap.removeClass('is-updating');
    });
  });
});

jQuery(function ($) {
  $(document)
    .off('submit.kangooSingleAddBlock', 'body.single-product form.cart')
    .on('submit.kangooSingleAddBlock', 'body.single-product form.cart', function (event) {
      const $form = $(this);

      if ($form.data('is-adding')) {
        event.preventDefault();
        event.stopImmediatePropagation();
        return false;
      }
    });
});

jQuery(function ($) {
  const singleAddLabel = 'ADD TO CART';
  const singleAddingLabel = 'ADDING...';
  const singleAddedLabel = 'ADDED';

  function openCartDrawer() {
    const $drawer = $('#cart-drawer');

    if ($drawer.length) {
      $drawer.addClass('is-open');
      $('body').addClass('no-scroll');
    }
  }

  // Prevent native form submission from firing in parallel with AJAX add-to-cart.
  $('body.single-product form.cart .single_add_to_cart_button').attr('type', 'button');

  $(document)
    .off('click.kangooSingleAdd', 'body.single-product form.cart .single_add_to_cart_button')
    .on('click.kangooSingleAdd', 'body.single-product form.cart .single_add_to_cart_button', function (event) {
      const $button = $(this);
      const $form = $button.closest('form.cart');

      if (!$button.length || $button.is(':disabled') || $button.hasClass('disabled')) {
        return;
      }

      if (!window.kangooAjaxCart || !kangooAjaxCart.ajax_url || !kangooAjaxCart.add_to_cart_nonce) {
        return;
      }

      if ($form.data('is-adding') || $button.data('request-lock')) {
        event.preventDefault();
        event.stopImmediatePropagation();
        return false;
      }

      const payload = $form.serializeArray().filter(function (item) {
        return item.name !== 'add-to-cart';
      });
      const hasProductId = payload.some(function (item) {
        return item.name === 'product_id';
      });

      if (!hasProductId) {
        payload.push({
          name: 'product_id',
          value: $form.find('input[name="add-to-cart"]').val() || $button.val()
        });
      }

	  $button.data('original-html', $button.data('idle-html') || $button.html());

      event.preventDefault();
      event.stopImmediatePropagation();

      $button.data('request-lock', true);
      $form.data('is-adding', true);
      $button
        .removeClass('is-added')
        .addClass('is-loading')
        .prop('disabled', true)
        .html(singleAddingLabel);

      payload.push(
        {
          name: 'action',
          value: 'kangoo_ajax_add_to_cart'
        },
        {
          name: 'nonce',
          value: kangooAjaxCart.add_to_cart_nonce
        }
      );

      console.log('[kangooSingleAdd] request', {
        productId: $form.find('input[name="product_id"]').val() || $form.find('input[name="add-to-cart"]').val() || $button.val(),
        variationId: $form.find('input[name="variation_id"]').val() || '',
        quantity: $form.find('input[name="quantity"]').val() || '1',
        payload: payload
      });

      $.ajax({
        type: 'POST',
        url: kangooAjaxCart.ajax_url,
        data: $.param(payload),
        dataType: 'json'
      }).done(function (response) {
        console.log('[kangooSingleAdd] response', response);
        const payloadData = response && response.data ? response.data : {};
        const fragments = payloadData.fragments || {};
        const hasFragments = Object.keys(fragments).length > 0;

        if (!response || response.success === false || !hasFragments) {
          $button
            .removeClass('is-loading is-added')
            .prop('disabled', false)
            .html('TRY AGAIN');

          window.setTimeout(function () {
            $button.html($button.data('original-html'));
          }, 1400);

          return;
        }

        $.each(fragments, function (selector, html) {
          $(selector).replaceWith(html);
        });

        $(document.body).trigger('added_to_cart', [fragments, payloadData.cart_hash, $button]);
        $(document.body).trigger('wc_fragments_refreshed');

        openCartDrawer();

        $button
          .removeClass('is-loading')
          .addClass('is-added')
          .prop('disabled', false)
          .html(singleAddedLabel);

        window.setTimeout(function () {
          $button
            .removeClass('is-added')
            .html($button.data('original-html'));
        }, 1200);
      }).fail(function () {
        $button
          .removeClass('is-loading is-added')
          .prop('disabled', false)
          .html('TRY AGAIN');

        window.setTimeout(function () {
          $button.html($button.data('original-html'));
        }, 1400);
      }).always(function () {
        $form.data('is-adding', false);
        $button.removeData('request-lock');
      });

      return false;
    });
});

jQuery(function ($) {
  function formatCurrency(amount) {
    if (!Number.isFinite(amount)) {
      return '';
    }

    try {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP'
      }).format(amount);
    } catch (error) {
      return `£${amount.toFixed(2)}`;
    }
  }

	function buildQuickAddButtonHtml(label, total) {
	  const text = total ? `${label} - ${total}` : label;
	  return `<span class="button-text">${text}</span>`;
	}

  function getQuickAddVariations($modal) {
    return JSON.parse($modal.find('.quick-add-variations').text() || '[]');
  }

  function getSelectedAttributes($modal) {
    const selected = {};

    $modal.find('.quick-add-pill.is-active').each(function () {
      const $pill = $(this);
      selected[String($pill.data('name'))] = String($pill.data('value'));
    });

    return selected;
  }

  function getQuickAddQuantity($modal) {
    return Math.max(1, parseInt($modal.find('input[name="quantity"]').val(), 10) || 1);
  }

  function matchesAttributes(variationAttributes, selectedAttributes, ignoredKey) {
    return Object.keys(selectedAttributes).every(function (key) {
      if (ignoredKey && key === ignoredKey) {
        return true;
      }

      return String(variationAttributes[key] || '') === String(selectedAttributes[key] || '');
    });
  }

  function isVariationAvailable(variation) {
    const inStock = !!variation.is_in_stock;
    const purchasable = variation.is_purchasable === undefined ? true : !!variation.is_purchasable;
    const active = variation.variation_is_active === undefined ? true : !!variation.variation_is_active;

    return inStock && purchasable && active;
  }

  function findExactVariation($modal) {
    const variations = getQuickAddVariations($modal);
    const selected = getSelectedAttributes($modal);

    return variations.find(function (variation) {
      if (!isVariationAvailable(variation)) {
        return false;
      }

      return Object.keys(variation.attributes || {}).every(function (key) {
        return String(variation.attributes[key] || '') === String(selected[key] || '');
      });
    }) || null;
  }

  function updateQuickAddPillAvailability($modal) {
    const variations = getQuickAddVariations($modal);
    const selected = getSelectedAttributes($modal);

    $modal.find('.quick-add-pill').each(function () {
      const $pill = $(this);
      const pillKey = String($pill.data('name'));
      const pillValue = String($pill.data('value'));

      const isAvailable = variations.some(function (variation) {
        if (!isVariationAvailable(variation)) {
          return false;
        }

        if (String((variation.attributes || {})[pillKey] || '') !== pillValue) {
          return false;
        }

        return matchesAttributes(variation.attributes || {}, selected, pillKey);
      });

      $pill.toggleClass('is-disabled', !isAvailable);

      if (!isAvailable) {
        $pill.removeClass('is-active');
      }
    });
  }

  function syncQuickAddState($modal) {
    const $price = $modal.find('[data-quick-add-price]');
    const $variationId = $modal.find('input[name="variation_id"]');
    const $submit = $modal.find('.quick-add-submit');
    const variations = getQuickAddVariations($modal);
    const initialPriceHtml = $modal.attr('data-initial-price-html') || $price.html();

    updateQuickAddPillAvailability($modal);

    const selected = getSelectedAttributes($modal);
    const attributeCount = $modal.find('.quick-add-pills').length;
    const selectedCount = Object.keys(selected).length;

    if (selectedCount < attributeCount) {
      $variationId.val('');
      $submit
        .prop('disabled', true)
        .addClass('is-disabled')
        .attr('aria-disabled', 'true')
        .html(buildQuickAddButtonHtml('Add to cart', ''));

      $price.html(initialPriceHtml);
      return;
    }

    const variation = findExactVariation($modal);

    if (!variation) {
      $variationId.val('');
      $submit
        .prop('disabled', true)
        .addClass('is-disabled')
        .attr('aria-disabled', 'true')
        .html(buildQuickAddButtonHtml('Add to cart', ''));

      $price.html(initialPriceHtml);
      return;
    }

    const quantity = getQuickAddQuantity($modal);
    const displayPrice = typeof variation.display_price === 'number' ? variation.display_price : null;
    const total = Number.isFinite(displayPrice) ? formatCurrency(displayPrice * quantity) : '';

    $variationId.val(variation.variation_id || '');
    $submit
      .prop('disabled', false)
      .removeClass('is-disabled')
      .attr('aria-disabled', 'false')
      .html(buildQuickAddButtonHtml('Add to cart', total));

    if (variation.price_html) {
      $price.html(variation.price_html);
      return;
    }

    const firstPrice = variations.find(function (item) {
      return item.price_html;
    });

    if (firstPrice) {
      $price.html(firstPrice.price_html);
    } else {
      $price.html(initialPriceHtml);
    }
  }

  $(document).on('click', '.quick-add-open', function () {
    const target = $(this).data('quick-add-target');
    const $modal = $('#' + target);

    if (!$modal.length) {
      return;
    }

    const $price = $modal.find('[data-quick-add-price]');

    if (!$modal.attr('data-initial-price-html')) {
      $modal.attr('data-initial-price-html', $price.html());
    }

    $modal.addClass('is-open').attr('aria-hidden', 'false');
    $('body').addClass('no-scroll');

    $modal.find('.quick-add-pill').removeClass('is-active');
    $modal.find('input[name="variation_id"]').val('');
    $modal.find('input[name="quantity"]').val('1');
    $modal.find('.quick-add-qty__input').val('1');
    $modal.find('.quick-add-submit')
      .prop('disabled', true)
      .addClass('is-disabled')
      .attr('aria-disabled', 'true')
      .html(buildQuickAddButtonHtml('Add to cart', ''));

    $price.html($modal.attr('data-initial-price-html'));

    syncQuickAddState($modal);
  });

  $(document).on('click', '[data-quick-add-close]', function () {
    $('.quick-add-modal.is-open').removeClass('is-open').attr('aria-hidden', 'true');
    $('body').removeClass('no-scroll');
  });

  $(document).on('click', '.quick-add-pill', function () {
    const $pill = $(this);

    if ($pill.hasClass('is-disabled')) {
      return;
    }

    const $group = $pill.closest('.quick-add-pills');
    const $modal = $pill.closest('.quick-add-modal');

    $group.find('.quick-add-pill').removeClass('is-active');
    $pill.addClass('is-active');

    syncQuickAddState($modal);
  });

  $(document).on('click', '[data-quick-add-minus], [data-quick-add-plus]', function () {
    const $modal = $(this).closest('.quick-add-modal');
    const $input = $modal.find('.quick-add-qty__input');
    let value = parseInt($input.val(), 10) || 1;

    if ($(this).is('[data-quick-add-plus]')) {
      value += 1;
    } else {
      value = Math.max(1, value - 1);
    }

    $input.val(value);
    $modal.find('input[name="quantity"]').val(value);
    syncQuickAddState($modal);
  });

  $(document).on('change input', '.quick-add-qty__input', function () {
    const $input = $(this);
    const $modal = $input.closest('.quick-add-modal');
    const value = Math.max(1, parseInt($input.val(), 10) || 1);

    $input.val(value);
    $modal.find('input[name="quantity"]').val(value);
    syncQuickAddState($modal);
  });

  $(document).on('submit', '.quick-add-form', function (event) {
    event.preventDefault();

    const $form = $(this);
    const $modal = $form.closest('.quick-add-modal');
    const $submit = $form.find('.quick-add-submit');
    const variation = findExactVariation($modal);

    if (!window.kangooAjaxCart || !kangooAjaxCart.ajax_url || !kangooAjaxCart.add_to_cart_nonce) {
      return;
    }

    if (!variation || $submit.prop('disabled')) {
      return;
    }

    const payload = [
      { name: 'action', value: 'kangoo_ajax_add_to_cart' },
      { name: 'nonce', value: kangooAjaxCart.add_to_cart_nonce },
      { name: 'product_id', value: $form.find('input[name="product_id"]').val() },
      { name: 'variation_id', value: variation.variation_id },
      { name: 'quantity', value: $form.find('input[name="quantity"]').val() }
    ];

    Object.keys(variation.attributes || {}).forEach(function (key) {
      payload.push({
        name: key,
        value: variation.attributes[key]
      });
    });

    $submit
      .prop('disabled', true)
      .addClass('is-disabled')
      .html(buildQuickAddButtonHtml('Adding...', ''));

    $.ajax({
      type: 'POST',
      url: kangooAjaxCart.ajax_url,
      data: $.param(payload),
      dataType: 'json'
    }).done(function (response) {
      const payloadData = response && response.data ? response.data : {};
      const fragments = payloadData.fragments || {};

      if (!response || response.success === false) {
        $submit
          .prop('disabled', false)
          .removeClass('is-disabled')
          .html(buildQuickAddButtonHtml('Try again', ''));

        return;
      }

      $.each(fragments, function (selector, html) {
        $(selector).replaceWith(html);
      });

      $(document.body).trigger('added_to_cart', [fragments, payloadData.cart_hash, $submit]);
      $(document.body).trigger('wc_fragments_refreshed');

      $('.quick-add-modal.is-open').removeClass('is-open').attr('aria-hidden', 'true');
      $('body').removeClass('no-scroll');

      const $drawer = $('#cart-drawer');
      if ($drawer.length) {
        $drawer.addClass('is-open');
        $('body').addClass('no-scroll');
      }
    }).fail(function () {
      $submit
        .prop('disabled', false)
        .removeClass('is-disabled')
        .html(buildQuickAddButtonHtml('Try again', ''));
    }).always(function () {
      window.setTimeout(function () {
        syncQuickAddState($modal);
      }, 400);
    });
  });

  $(document).on('keydown', function (event) {
    if (event.key === 'Escape') {
      $('.quick-add-modal.is-open').removeClass('is-open').attr('aria-hidden', 'true');
      $('body').removeClass('no-scroll');
    }
  });
});

jQuery(function ($) {
  $(document).on('click', '.cart-drawer a.checkout', function (event) {
    if (!window.kangooAjaxCart || !kangooAjaxCart.cart_url) {
      return;
    }

    event.preventDefault();
    window.location.href = kangooAjaxCart.cart_url;
  });
});
