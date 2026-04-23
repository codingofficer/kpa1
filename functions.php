<?php
if (!defined('ABSPATH')) {
    exit;
}

function kangoo_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('woocommerce');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));
    add_theme_support('custom-logo', array(
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    register_nav_menus(array(
        'primary' => __('Primary Menu', 'kangoo'),
        'footer'  => __('Footer Menu', 'kangoo'),
    ));
}
add_action('after_setup_theme', 'kangoo_theme_setup');

function kangoo_enqueue_assets() {
    $theme_version = wp_get_theme()->get('Version');
    $css_uri = get_template_directory_uri() . '/assets/css/';
    $js_uri  = get_template_directory_uri() . '/assets/js/';

    wp_enqueue_style('kangoo-base', $css_uri . 'base.css', array(), $theme_version);
    wp_enqueue_style('kangoo-components', $css_uri . 'components.css', array('kangoo-base'), $theme_version);
    wp_enqueue_style('kangoo-header-footer', $css_uri . 'header-footer.css', array('kangoo-components'), $theme_version);
    wp_enqueue_style('kangoo-home', $css_uri . 'home.css', array('kangoo-header-footer'), $theme_version);
    wp_enqueue_style('kangoo-shop', $css_uri . 'shop.css', array('kangoo-home'), $theme_version);
    wp_enqueue_style('kangoo-product', $css_uri . 'product.css', array('kangoo-shop'), $theme_version);
    wp_enqueue_style('kangoo-woocommerce', $css_uri . 'woocommerce.css', array('kangoo-product'), $theme_version);
    wp_enqueue_style('kangoo-account-drawer', $css_uri . 'account-drawer.css', array('kangoo-woocommerce'), $theme_version);

    wp_enqueue_script(
        'kangoo-main',
        $js_uri . 'main.js',
        array(),
        $theme_version,
        true
    );

    wp_enqueue_script(
        'kangoo-ajax-cart',
        $js_uri . 'ajax-cart.js',
        array('jquery'),
        $theme_version,
        true
    );

    wp_enqueue_script(
        'kangoo-account-drawer',
        $js_uri . 'account-drawer.js',
        array('jquery'),
        $theme_version,
        true
    );

    wp_localize_script('kangoo-ajax-cart', 'kangooAjaxCart', array(
        'ajax_url'          => admin_url('admin-ajax.php'),
        'nonce'             => wp_create_nonce('kangoo_update_mini_cart_qty'),
        'add_to_cart_nonce' => wp_create_nonce('kangoo_ajax_add_to_cart'),
        'remove_nonce'      => wp_create_nonce('kangoo_remove_mini_cart_item'),
        'cart_url'          => function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/'),
    ));

    wp_localize_script('kangoo-account-drawer', 'kangooAccount', array(
        'ajax_url'        => admin_url('admin-ajax.php'),
        'login_nonce'     => wp_create_nonce('kangoo_account_login'),
        'register_nonce'  => wp_create_nonce('kangoo_account_register'),
        'logout_nonce'    => wp_create_nonce('kangoo_account_logout'),
        'status_nonce'    => wp_create_nonce('kangoo_account_status'),
        'is_logged_in'    => is_user_logged_in(),
        'account_url'     => function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/'),
        'login_title'     => __('Do you have an account with us?', 'kangoo'),
        'login_success'   => __('Signed in successfully.', 'kangoo'),
        'register_success'=> __('Account created successfully.', 'kangoo'),
    ));

    if (function_exists('is_product') && is_product()) {
        wp_enqueue_script('wc-add-to-cart-variation');
        wp_enqueue_script('wc-cart-fragments');
    }
}
add_action('wp_enqueue_scripts', 'kangoo_enqueue_assets');

function kangoo_body_classes($classes) {
    if (is_front_page()) {
        $classes[] = 'is-front-page';
    }

    if (function_exists('is_woocommerce') && is_woocommerce()) {
        $classes[] = 'is-woocommerce';
    }

    return $classes;
}
add_filter('body_class', 'kangoo_body_classes');

function kangoo_get_cart_badge_html() {
    ob_start();
    ?>
    <span class="cart-badge">
        <?php echo function_exists('WC') && WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0; ?>
    </span>
    <?php
    return ob_get_clean();
}

function kangoo_get_mini_cart_container_html() {
    ob_start();
    ?>
    <div class="cart-drawer__content">
        <?php woocommerce_mini_cart(); ?>
    </div>
    <?php
    return ob_get_clean();
}

function kangoo_get_mini_cart_html() {
    ob_start();
    woocommerce_mini_cart();
    return ob_get_clean();
}

add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    $fragments['.cart-badge'] = kangoo_get_cart_badge_html();
    $fragments['.cart-drawer__content'] = kangoo_get_mini_cart_container_html();

    return $fragments;
});

add_filter('woocommerce_product_single_add_to_cart_text', function () {
    return __('ADD TO CART', 'kangoo');
});

function kangoo_ajax_update_mini_cart_quantity() {
    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array(
            'message' => __('Cart is unavailable.', 'kangoo'),
        ), 400);
    }

    check_ajax_referer('kangoo_update_mini_cart_qty', 'nonce');

    $cart_item_key = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';
    $quantity = isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : 0;
    $quantity = max(0, (int) $quantity);

    if ('' === $cart_item_key || !array_key_exists($cart_item_key, WC()->cart->get_cart())) {
        wp_send_json_error(array(
            'message' => __('Unable to update this cart item.', 'kangoo'),
        ), 400);
    }

    WC()->cart->set_quantity($cart_item_key, $quantity, true);
    WC()->cart->calculate_totals();

    wp_send_json_success(array(
        'mini_cart_html'  => kangoo_get_mini_cart_html(),
        'cart_badge_html' => kangoo_get_cart_badge_html(),
    ));
}
add_action('wp_ajax_kangoo_update_mini_cart_quantity', 'kangoo_ajax_update_mini_cart_quantity');
add_action('wp_ajax_nopriv_kangoo_update_mini_cart_quantity', 'kangoo_ajax_update_mini_cart_quantity');

function kangoo_ajax_remove_mini_cart_item() {
    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array(
            'message' => __('Cart is unavailable.', 'kangoo'),
        ), 400);
    }

    check_ajax_referer('kangoo_remove_mini_cart_item', 'nonce');

    $cart_item_key = isset($_POST['cart_item_key']) ? wc_clean(wp_unslash($_POST['cart_item_key'])) : '';

    if ('' === $cart_item_key || !array_key_exists($cart_item_key, WC()->cart->get_cart())) {
        wp_send_json_error(array(
            'message' => __('Unable to remove this cart item.', 'kangoo'),
        ), 400);
    }

    WC()->cart->remove_cart_item($cart_item_key);
    WC()->cart->calculate_totals();

    wp_send_json_success(array(
        'mini_cart_html'  => kangoo_get_mini_cart_html(),
        'cart_badge_html' => kangoo_get_cart_badge_html(),
    ));
}
add_action('wp_ajax_kangoo_remove_mini_cart_item', 'kangoo_ajax_remove_mini_cart_item');
add_action('wp_ajax_nopriv_kangoo_remove_mini_cart_item', 'kangoo_ajax_remove_mini_cart_item');

add_filter('woocommerce_add_to_cart_quantity', function ($quantity) {
    error_log('woocommerce_add_to_cart_quantity => ' . $quantity);
    return $quantity;
}, 9999);

add_filter('woocommerce_add_cart_item_data', function ($cart_item_data, $product_id, $variation_id, $quantity) {
    error_log('woocommerce_add_cart_item_data quantity => ' . $quantity);
    error_log('woocommerce_add_cart_item_data product_id => ' . $product_id);
    error_log('woocommerce_add_cart_item_data variation_id => ' . $variation_id);
    return $cart_item_data;
}, 9999, 4);

add_action('woocommerce_add_to_cart', function ($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    error_log('woocommerce_add_to_cart action quantity => ' . $quantity);
    error_log('woocommerce_add_to_cart action product_id => ' . $product_id);
    error_log('woocommerce_add_to_cart action variation_id => ' . $variation_id);
}, 9999, 6);

function kangoo_ajax_add_to_cart() {
    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array(
            'message' => __('Cart is unavailable.', 'kangoo'),
        ), 400);
    }

    check_ajax_referer('kangoo_ajax_add_to_cart', 'nonce');

    // Prevent WooCommerce form-handler logic from auto-processing add-to-cart on this AJAX endpoint.
    if (isset($_REQUEST['add-to-cart'])) {
        unset($_REQUEST['add-to-cart']);
    }

    $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
    if (!$product_id && isset($_POST['add-to-cart'])) {
        $product_id = absint(wp_unslash($_POST['add-to-cart']));
    }

    $quantity = isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : 1;
    $quantity = max(1, (int) $quantity);


    $variation_id = isset($_POST['variation_id']) ? absint(wp_unslash($_POST['variation_id'])) : 0;
    $variation = array();

    foreach ($_POST as $key => $value) {
        if (0 === strpos($key, 'attribute_')) {
            $variation[wc_clean(wp_unslash($key))] = wc_clean(wp_unslash($value));
        }
    }

    $cart_id = WC()->cart->generate_cart_id($product_id, $variation_id, $variation);
    $cart_item_key = WC()->cart->find_product_in_cart($cart_id);

    error_log(
        sprintf(
            'kangoo_ajax_add_to_cart request => product_id:%d variation_id:%d quantity:%d cart_id:%s cart_item_key:%s',
            (int) $product_id,
            (int) $variation_id,
            (int) $quantity,
            (string) $cart_id,
            $cart_item_key ? (string) $cart_item_key : 'none'
        )
    );

    if ($cart_item_key) {
        $cart = WC()->cart->get_cart();
        $existing_quantity = isset($cart[$cart_item_key]['quantity']) ? (int) $cart[$cart_item_key]['quantity'] : 0;
        $new_quantity = $existing_quantity + $quantity;

        error_log(
            sprintf(
                'kangoo_ajax_add_to_cart set_quantity => key:%s existing:%d requested:%d new:%d',
                (string) $cart_item_key,
                (int) $existing_quantity,
                (int) $quantity,
                (int) $new_quantity
            )
        );

        WC()->cart->set_quantity($cart_item_key, $new_quantity, true);
        $added = $cart_item_key;
    } else {
        error_log(
            sprintf(
                'kangoo_ajax_add_to_cart add_to_cart => product_id:%d variation_id:%d quantity:%d',
                (int) $product_id,
                (int) $variation_id,
                (int) $quantity
            )
        );
        $added = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation);
    }

    if (!$added) {
        wp_send_json_error(array(
            'message'     => __('Unable to add this item to the cart.', 'kangoo'),
            'product_url' => get_permalink($product_id),
        ), 400);
    }

    wp_send_json_success(array(
        'fragments' => apply_filters('woocommerce_add_to_cart_fragments', array()),
        'cart_hash' => WC()->cart->get_cart_hash(),
    ));
}
add_action('wp_ajax_kangoo_ajax_add_to_cart', 'kangoo_ajax_add_to_cart');
add_action('wp_ajax_nopriv_kangoo_ajax_add_to_cart', 'kangoo_ajax_add_to_cart');

/* =========================================================================
AUTH DRAWER HELPERS
========================================================================= */

function kangoo_account_get_redirect_url() {
    if (function_exists('wc_get_page_permalink')) {
        $url = wc_get_page_permalink('myaccount');
        if ($url) {
            return $url;
        }
    }

    return home_url('/my-account/');
}

function kangoo_account_get_user_payload($user) {
    return array(
        'id'           => (int) $user->ID,
        'display_name' => $user->display_name,
        'email'        => $user->user_email,
        'account_url'  => kangoo_account_get_redirect_url(),
    );
}

function kangoo_ajax_account_status() {
    check_ajax_referer('kangoo_account_status', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_success(array(
            'logged_in' => false,
        ));
    }

    $user = wp_get_current_user();

    wp_send_json_success(array(
        'logged_in' => true,
        'user'      => kangoo_account_get_user_payload($user),
    ));
}
add_action('wp_ajax_kangoo_account_status', 'kangoo_ajax_account_status');
add_action('wp_ajax_nopriv_kangoo_account_status', 'kangoo_ajax_account_status');

function kangoo_ajax_account_login() {
    check_ajax_referer('kangoo_account_login', 'nonce');

    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        wp_send_json_success(array(
            'message' => __('You are already signed in.', 'kangoo'),
            'user'    => kangoo_account_get_user_payload($user),
        ));
    }

    $email_or_username = isset($_POST['login']) ? sanitize_text_field(wp_unslash($_POST['login'])) : '';
    $password = isset($_POST['password']) ? (string) wp_unslash($_POST['password']) : '';
    $remember = !empty($_POST['remember']);

    if ('' === $email_or_username || '' === $password) {
        wp_send_json_error(array(
            'message' => __('Please enter your email/username and password.', 'kangoo'),
            'field'   => 'login',
        ), 400);
    }

    $user_login = $email_or_username;
    if (is_email($email_or_username)) {
        $user = get_user_by('email', $email_or_username);
        if ($user) {
            $user_login = $user->user_login;
        }
    }

    $creds = array(
        'user_login'    => $user_login,
        'user_password' => $password,
        'remember'      => $remember,
    );

    $signed_in_user = wp_signon($creds, is_ssl());

    if (is_wp_error($signed_in_user)) {
        wp_send_json_error(array(
            'message' => $signed_in_user->get_error_message(),
            'field'   => 'login',
        ), 400);
    }

    wp_set_current_user($signed_in_user->ID);

    wp_send_json_success(array(
        'message' => __('Signed in successfully.', 'kangoo'),
        'user'    => kangoo_account_get_user_payload($signed_in_user),
    ));
}
add_action('wp_ajax_nopriv_kangoo_account_login', 'kangoo_ajax_account_login');
add_action('wp_ajax_kangoo_account_login', 'kangoo_ajax_account_login');

function kangoo_ajax_account_register() {
    check_ajax_referer('kangoo_account_register', 'nonce');

    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        wp_send_json_success(array(
            'message' => __('You are already signed in.', 'kangoo'),
            'user'    => kangoo_account_get_user_payload($user),
        ));
    }

    $first_name = isset($_POST['first_name']) ? sanitize_text_field(wp_unslash($_POST['first_name'])) : '';
    $last_name  = isset($_POST['last_name']) ? sanitize_text_field(wp_unslash($_POST['last_name'])) : '';
    $email      = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $password   = isset($_POST['password']) ? (string) wp_unslash($_POST['password']) : '';
    $confirm    = isset($_POST['confirm_password']) ? (string) wp_unslash($_POST['confirm_password']) : '';

    if ('' === $first_name) {
        wp_send_json_error(array(
            'message' => __('Please enter your first name.', 'kangoo'),
            'field'   => 'first_name',
        ), 400);
    }

    if ('' === $last_name) {
        wp_send_json_error(array(
            'message' => __('Please enter your last name.', 'kangoo'),
            'field'   => 'last_name',
        ), 400);
    }

    if ('' === $email || !is_email($email)) {
        wp_send_json_error(array(
            'message' => __('Please enter a valid email address.', 'kangoo'),
            'field'   => 'email',
        ), 400);
    }

    if (email_exists($email)) {
        wp_send_json_error(array(
            'message' => __('An account with this email already exists.', 'kangoo'),
            'field'   => 'email',
        ), 400);
    }

    if (strlen($password) < 8) {
        wp_send_json_error(array(
            'message' => __('Your password must be at least 8 characters long.', 'kangoo'),
            'field'   => 'password',
        ), 400);
    }

    if ($password !== $confirm) {
        wp_send_json_error(array(
            'message' => __('Passwords do not match.', 'kangoo'),
            'field'   => 'confirm_password',
        ), 400);
    }

    $base_username = sanitize_user(current(explode('@', $email)), true);
    $username = $base_username ? $base_username : 'customer';
    $suffix = 1;

    while (username_exists($username)) {
        $username = $base_username . $suffix;
        $suffix++;
    }

    $user_id = wp_insert_user(array(
        'user_login'   => $username,
        'user_pass'    => $password,
        'user_email'   => $email,
        'first_name'   => $first_name,
        'last_name'    => $last_name,
        'display_name' => trim($first_name . ' ' . $last_name),
        'role'         => 'customer',
    ));

    if (is_wp_error($user_id)) {
        wp_send_json_error(array(
            'message' => $user_id->get_error_message(),
        ), 400);
    }

    $user = get_user_by('id', $user_id);

    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true);

    do_action('wp_login', $user->user_login, $user);

    wp_send_json_success(array(
        'message' => __('Account created successfully.', 'kangoo'),
        'user'    => kangoo_account_get_user_payload($user),
    ));
}
add_action('wp_ajax_nopriv_kangoo_account_register', 'kangoo_ajax_account_register');
add_action('wp_ajax_kangoo_account_register', 'kangoo_ajax_account_register');

function kangoo_ajax_account_logout() {
    check_ajax_referer('kangoo_account_logout', 'nonce');

    wp_logout();

    wp_send_json_success(array(
        'message' => __('Signed out successfully.', 'kangoo'),
    ));
}
add_action('wp_ajax_kangoo_account_logout', 'kangoo_ajax_account_logout');

/* =========================================================================
MEGA MENU HELPERS
========================================================================= */

function kangoo_acf_option_value($keys, $default = null) {
    if (!function_exists('get_field')) {
        return $default;
    }

    foreach ((array) $keys as $key) {
        $value = get_field($key, 'option');

        if ($value === null) {
            continue;
        }

        if (is_string($value) && trim($value) === '') {
            continue;
        }

        if (is_array($value) && empty($value)) {
            continue;
        }

        return $value;
    }

    return $default;
}

function kangoo_acf_link_url($link) {
    if (is_array($link) && !empty($link['url'])) {
        return (string) $link['url'];
    }

    if (is_string($link) && $link !== '') {
        return $link;
    }

    return '';
}

function kangoo_acf_link_target($link) {
    if (is_array($link) && !empty($link['target'])) {
        return (string) $link['target'];
    }

    return '_self';
}

function kangoo_get_mega_menu_settings() {
    $settings = array(
        'enabled'               => 0,
        'trigger_label'         => '',
        'mobile_drawer_title'   => 'Browse',
        'top_links'             => array(),
        'desktop_sidebar_links' => array(),
        'brands_panel_title'    => 'Brands',
        'brands_view_all_label' => '',
        'brands_view_all_link'  => array(),
        'brand_cards'           => array(),
        'strengths_panel_title' => 'Strengths',
        'strength_cards'        => array(),
        'flavours_panel_title'  => 'Flavours',
        'flavour_cards'         => array(),
        'mobile_sections'       => array(),
    );

    if (!function_exists('get_field')) {
        return $settings;
    }

    $settings['enabled'] = (int) kangoo_acf_option_value(
        array('mega_menu_enabled', 'enable_mega_menu'),
        0
    );

    $settings['trigger_label'] = (string) kangoo_acf_option_value(
        array('mega_menu_trigger_label', 'desktop_trigger_label'),
        ''
    );

    $settings['mobile_drawer_title'] = (string) kangoo_acf_option_value(
        array('mega_menu_mobile_label', 'mobile_drawer_title'),
        'Browse'
    );

    $settings['brands_panel_title'] = (string) kangoo_acf_option_value(
        array('mega_menu_brand_panel_title', 'brands_panel_title'),
        'Brands'
    );

    $settings['brands_view_all_label'] = (string) kangoo_acf_option_value(
        array('mega_menu_brand_view_all_label', 'brands_view_all_label'),
        ''
    );

    $settings['brands_view_all_link'] = kangoo_acf_option_value(
        array('mega_menu_brand_view_all_link', 'brands_view_all_link', 'brands_view_all_url'),
        array()
    );

    $settings['strengths_panel_title'] = (string) kangoo_acf_option_value(
        array('mega_menu_strength_panel_title', 'strengths_panel_title'),
        'Strengths'
    );

    $settings['flavours_panel_title'] = (string) kangoo_acf_option_value(
        array('mega_menu_flavour_panel_title', 'flavours_panel_title'),
        'Flavours'
    );

    $top_links = kangoo_acf_option_value(
        array('mega_menu_top_links', 'top_links'),
        array()
    );

    if (is_array($top_links)) {
        foreach ($top_links as $item) {
            if (!is_array($item)) {
                continue;
            }

            $settings['top_links'][] = array(
                'label' => isset($item['label']) ? (string) $item['label'] : '',
                'link'  => isset($item['link']) ? $item['link'] : (isset($item['url']) ? $item['url'] : array()),
            );
        }
    }

    $desktop_links = kangoo_acf_option_value(
        array('mega_menu_desktop_links', 'desktop_sidebar_links'),
        array()
    );

    if (is_array($desktop_links)) {
        foreach ($desktop_links as $item) {
            if (!is_array($item)) {
                continue;
            }

            $settings['desktop_sidebar_links'][] = array(
                'label'     => isset($item['label']) ? (string) $item['label'] : '',
                'type'      => isset($item['type']) ? (string) $item['type'] : 'panel',
                'panel_key' => isset($item['panel_key']) ? (string) $item['panel_key'] : '',
                'link'      => isset($item['link']) ? $item['link'] : (isset($item['url']) ? $item['url'] : array()),
            );
        }
    }

    $brand_cards = kangoo_acf_option_value(
        array('mega_menu_brand_cards', 'brand_cards'),
        array()
    );

    if (is_array($brand_cards)) {
        foreach ($brand_cards as $item) {
            if (!is_array($item)) {
                continue;
            }

            $settings['brand_cards'][] = array(
                'label'      => isset($item['label']) ? (string) $item['label'] : '',
                'link'       => isset($item['link']) ? $item['link'] : (isset($item['url']) ? $item['url'] : array()),
                'image'      => isset($item['image']) && is_array($item['image']) ? $item['image'] : array(),
                'featured'   => !empty($item['featured']),
                'badge_text' => isset($item['badge_text']) ? (string) $item['badge_text'] : '',
            );
        }
    }

    $strength_cards = kangoo_acf_option_value(
        array('mega_menu_strength_cards', 'strength_cards'),
        array()
    );

    if (is_array($strength_cards)) {
        foreach ($strength_cards as $item) {
            if (!is_array($item)) {
                continue;
            }

            $settings['strength_cards'][] = array(
                'label'       => isset($item['label']) ? (string) $item['label'] : '',
                'description' => isset($item['description']) ? (string) $item['description'] : (isset($item['short_description']) ? (string) $item['short_description'] : ''),
                'mg_range'    => isset($item['mg_range']) ? (string) $item['mg_range'] : '',
                'link'        => isset($item['link']) ? $item['link'] : (isset($item['url']) ? $item['url'] : array()),
                'dots_on'     => isset($item['dots_on']) ? (int) $item['dots_on'] : (isset($item['dots_filled']) ? (int) $item['dots_filled'] : 0),
                'dot_color'   => isset($item['dot_color']) ? (string) $item['dot_color'] : '#4da3ff',
            );
        }
    }

    $flavour_cards = kangoo_acf_option_value(
        array('mega_menu_flavour_cards', 'flavour_cards'),
        array()
    );

    if (is_array($flavour_cards)) {
        foreach ($flavour_cards as $item) {
            if (!is_array($item)) {
                continue;
            }

            $settings['flavour_cards'][] = array(
                'label'            => isset($item['label']) ? (string) $item['label'] : '',
                'link'             => isset($item['link']) ? $item['link'] : (isset($item['url']) ? $item['url'] : array()),
                'background_color' => isset($item['background_color']) ? (string) $item['background_color'] : '#1b1d23',
                'text_color'       => isset($item['text_color']) ? (string) $item['text_color'] : '#ffffff',
                'icon'             => isset($item['icon']) && is_array($item['icon']) ? $item['icon'] : array(),
            );
        }
    }

    $mobile_sections = kangoo_acf_option_value(
        array('mega_menu_mobile_sections', 'mobile_sections'),
        array()
    );

    if (is_array($mobile_sections)) {
        foreach ($mobile_sections as $item) {
            if (!is_array($item)) {
                continue;
            }

            $custom_links = array();

            if (!empty($item['custom_links']) && is_array($item['custom_links'])) {
                foreach ($item['custom_links'] as $custom_item) {
                    if (!is_array($custom_item)) {
                        continue;
                    }

                    $custom_links[] = array(
                        'label' => isset($custom_item['label']) ? (string) $custom_item['label'] : '',
                        'link'  => isset($custom_item['link']) ? $custom_item['link'] : (isset($custom_item['url']) ? $custom_item['url'] : array()),
                    );
                }
            }

            $settings['mobile_sections'][] = array(
                'label'           => isset($item['label']) ? (string) $item['label'] : (isset($item['section_label']) ? (string) $item['section_label'] : ''),
                'source'          => isset($item['source']) ? (string) $item['source'] : '',
                'open_by_default' => !empty($item['open_by_default']),
                'custom_links'    => $custom_links,
            );
        }
    }

    return $settings;
}

function kangoo_render_account_drawer() {
    get_template_part('template-parts/global/account-drawer');
}
add_action('wp_footer', 'kangoo_render_account_drawer', 30);

function kangoo_account_page_assets() {
    if (!function_exists('is_account_page') || !is_account_page()) {
        return;
    }

    $theme_version = wp_get_theme()->get('Version');
    $css_uri = get_template_directory_uri() . '/assets/css/';
    $js_uri  = get_template_directory_uri() . '/assets/js/';

    wp_enqueue_style(
        'kangoo-account-page',
        $css_uri . 'account-page.css',
        array('kangoo-woocommerce'),
        $theme_version
    );

    wp_enqueue_script(
        'kangoo-account-page',
        $js_uri . 'account-page.js',
        array(),
        $theme_version,
        true
    );

    wp_localize_script('kangoo-account-page', 'kangooAccountPage', array(
        'menu_label' => __('Account menu', 'kangoo'),
        'close_label' => __('Close menu', 'kangoo'),
    ));
}
add_action('wp_enqueue_scripts', 'kangoo_account_page_assets', 30);

function kangoo_account_page_body_class($classes) {
    if (function_exists('is_account_page') && is_account_page()) {
        $classes[] = 'is-account-page';
    }

    return $classes;
}
add_filter('body_class', 'kangoo_account_page_body_class');

function kangoo_account_menu_items($items) {
    if (isset($items['dashboard'])) {
        $items['dashboard'] = __('Dashboard', 'kangoo');
    }

    if (isset($items['orders'])) {
        $items['orders'] = __('Orders', 'kangoo');
    }

    if (isset($items['downloads'])) {
        $items['downloads'] = __('Downloads', 'kangoo');
    }

    if (isset($items['edit-address'])) {
        $items['edit-address'] = __('Addresses', 'kangoo');
    }

    if (isset($items['edit-account'])) {
        $items['edit-account'] = __('Account details', 'kangoo');
    }

    if (isset($items['customer-logout'])) {
        $items['customer-logout'] = __('Log out', 'kangoo');
    }

    return $items;
}
add_filter('woocommerce_account_menu_items', 'kangoo_account_menu_items');

add_filter('woocommerce_get_endpoint_url', function ($url, $endpoint, $value, $permalink) {
    if ($endpoint === 'customer-logout') {
        return wp_logout_url(wc_get_page_permalink('myaccount'));
    }

    return $url;
}, 10, 4);


