<?php
/* FILE: header.php */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
$kangoo_mega_menu = function_exists('kangoo_get_mega_menu_settings')
    ? kangoo_get_mega_menu_settings()
    : array();

$kangoo_mega_enabled = !empty($kangoo_mega_menu['enabled']);

$kangoo_trigger_label   = isset($kangoo_mega_menu['trigger_label']) ? (string) $kangoo_mega_menu['trigger_label'] : '';
$kangoo_mobile_title    = isset($kangoo_mega_menu['mobile_drawer_title']) && $kangoo_mega_menu['mobile_drawer_title'] !== ''
    ? (string) $kangoo_mega_menu['mobile_drawer_title']
    : __('Browse', 'kangoo');

$kangoo_top_links       = !empty($kangoo_mega_menu['top_links']) && is_array($kangoo_mega_menu['top_links']) ? $kangoo_mega_menu['top_links'] : array();
$kangoo_sidebar_links   = !empty($kangoo_mega_menu['desktop_sidebar_links']) && is_array($kangoo_mega_menu['desktop_sidebar_links']) ? $kangoo_mega_menu['desktop_sidebar_links'] : array();
$kangoo_brand_cards     = !empty($kangoo_mega_menu['brand_cards']) && is_array($kangoo_mega_menu['brand_cards']) ? $kangoo_mega_menu['brand_cards'] : array();
$kangoo_strength_cards  = !empty($kangoo_mega_menu['strength_cards']) && is_array($kangoo_mega_menu['strength_cards']) ? $kangoo_mega_menu['strength_cards'] : array();
$kangoo_flavour_cards   = !empty($kangoo_mega_menu['flavour_cards']) && is_array($kangoo_mega_menu['flavour_cards']) ? $kangoo_mega_menu['flavour_cards'] : array();
$kangoo_mobile_sections = !empty($kangoo_mega_menu['mobile_sections']) && is_array($kangoo_mega_menu['mobile_sections']) ? $kangoo_mega_menu['mobile_sections'] : array();

$kangoo_default_panel = 'brands';

foreach ($kangoo_sidebar_links as $kangoo_item) {
    if (
        is_array($kangoo_item) &&
        isset($kangoo_item['type'], $kangoo_item['panel_key']) &&
        $kangoo_item['type'] === 'panel' &&
        $kangoo_item['panel_key'] !== ''
    ) {
        $kangoo_default_panel = (string) $kangoo_item['panel_key'];
        break;
    }
}

if (current_user_can('manage_options')) {
    echo "\n<!-- mega-enabled: " . ($kangoo_mega_enabled ? 'yes' : 'no') . " -->\n";
}
?>
	
<?php if (current_user_can('manage_options')) : ?>
    <!-- mega-enabled: <?php echo !empty($kangoo_mega_menu['enabled']) ? 'yes' : 'no'; ?> -->
    <!-- mega-desktop-links-count: <?php echo isset($kangoo_mega_menu['desktop_sidebar_links']) && is_array($kangoo_mega_menu['desktop_sidebar_links']) ? count($kangoo_mega_menu['desktop_sidebar_links']) : 0; ?> -->
    <!-- mega-brand-cards-count: <?php echo isset($kangoo_mega_menu['brand_cards']) && is_array($kangoo_mega_menu['brand_cards']) ? count($kangoo_mega_menu['brand_cards']) : 0; ?> -->
    <!-- mega-strength-cards-count: <?php echo isset($kangoo_mega_menu['strength_cards']) && is_array($kangoo_mega_menu['strength_cards']) ? count($kangoo_mega_menu['strength_cards']) : 0; ?> -->
    <!-- mega-flavour-cards-count: <?php echo isset($kangoo_mega_menu['flavour_cards']) && is_array($kangoo_mega_menu['flavour_cards']) ? count($kangoo_mega_menu['flavour_cards']) : 0; ?> -->
<?php endif; ?>

<header class="site-header">
    <div class="site-header__inner">
        <div class="container">
            <div class="site-logo">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        <?php bloginfo('name'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <nav class="site-nav" aria-label="<?php esc_attr_e('Primary Menu', 'kangoo'); ?>">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'site-nav__menu',
                    'fallback_cb'    => false,
                ));
                ?>
            </nav>

            <div class="site-header__actions">
                <button
                    type="button"
                    class="site-header__account"
                    data-account-open="login"
                    aria-label="<?php esc_attr_e('Open account panel', 'kangoo'); ?>"
                >
                    <span class="site-header__account-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" focusable="false">
                            <path d="M12 12a4.5 4.5 0 1 0-4.5-4.5A4.5 4.5 0 0 0 12 12Zm0 2.25c-4.14 0-7.5 2.59-7.5 5.78V21h15v-.97c0-3.19-3.36-5.78-7.5-5.78Z" fill="currentColor"/>
                        </svg>
                    </span>
                    <span class="site-header__account-text"><?php esc_html_e('Account', 'kangoo'); ?></span>
                </button>

                <?php if ($kangoo_mega_enabled) : ?>
                    <button
                        type="button"
                        class="site-header__menu-toggle"
                        id="header-menu-toggle"
                        data-mega-menu-open
                        aria-controls="kangoo-mega-menu-drawer"
                        aria-expanded="false"
                        aria-label="<?php esc_attr_e('Open menu', 'kangoo'); ?>"
                    >
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                <?php endif; ?>

                <?php if (function_exists('WC') && function_exists('wc_get_cart_url')) : ?>
                    <button
                        type="button"
                        class="site-header__cart"
                        id="header-cart-trigger"
                        aria-label="<?php esc_attr_e('Open cart', 'kangoo'); ?>"
                    >
                        <span class="cart-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" focusable="false">
                                <path d="M6 6h15l-1.5 8.5a2 2 0 0 1-2 1.5H9a2 2 0 0 1-2-1.3L4.3 4H2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="9" cy="20" r="1.75" fill="currentColor"/>
                                <circle cx="18" cy="20" r="1.75" fill="currentColor"/>
                            </svg>
                        </span>
                        <span class="cart-badge">
                            <?php echo function_exists('WC') && WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0; ?>
                        </span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($kangoo_mega_enabled) : ?>
        <div
            class="kangoo-mega-menu"
            id="kangoo-mega-menu-desktop"
            data-trigger-label="<?php echo esc_attr(strtolower(trim($kangoo_trigger_label))); ?>"
        >
            <div class="container">
                <div class="kangoo-mega-menu__grid">
                    <aside class="kangoo-mega-menu__sidebar">
                        <?php foreach ($kangoo_sidebar_links as $item) :
                            $label  = isset($item['label']) ? (string) $item['label'] : '';
                            $type   = isset($item['type']) ? (string) $item['type'] : 'link';
                            $panel  = isset($item['panel_key']) ? (string) $item['panel_key'] : '';
                            $link   = isset($item['link']) ? $item['link'] : array();
                            $url    = function_exists('kangoo_acf_link_url') ? kangoo_acf_link_url($link) : '';
                            $target = function_exists('kangoo_acf_link_target') ? kangoo_acf_link_target($link) : '_self';

                            if ($label === '') {
                                continue;
                            }
                            ?>
                            <?php if ($type === 'panel' && $panel !== '') : ?>
                                <button
                                    type="button"
                                    class="kangoo-mega-menu__sidebar-link<?php echo $panel === $kangoo_default_panel ? ' is-active' : ''; ?>"
                                    data-mega-panel-trigger="<?php echo esc_attr($panel); ?>"
                                >
                                    <?php echo esc_html($label); ?>
                                </button>
                            <?php elseif ($url !== '') : ?>
                                <a
                                    href="<?php echo esc_url($url); ?>"
                                    target="<?php echo esc_attr($target); ?>"
                                    class="kangoo-mega-menu__sidebar-link"
                                >
                                    <?php echo esc_html($label); ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </aside>

                    <div class="kangoo-mega-menu__panels">
                        <section
                            class="kangoo-mega-menu__panel<?php echo $kangoo_default_panel === 'brands' ? ' is-active' : ''; ?>"
                            data-mega-panel="brands"
                        >
                            <?php if (!empty($kangoo_mega_menu['brands_panel_title'])) : ?>
                                <h3><?php echo esc_html($kangoo_mega_menu['brands_panel_title']); ?></h3>
                            <?php endif; ?>

                            <div class="kangoo-mega-menu__brand-grid">
                                <?php foreach ($kangoo_brand_cards as $card) :
                                    $label    = isset($card['label']) ? (string) $card['label'] : '';
                                    $link     = isset($card['link']) ? $card['link'] : array();
                                    $url      = function_exists('kangoo_acf_link_url') ? kangoo_acf_link_url($link) : '';
                                    $target   = function_exists('kangoo_acf_link_target') ? kangoo_acf_link_target($link) : '_self';
                                    $image    = isset($card['image']) && is_array($card['image']) ? $card['image'] : array();
                                    $featured = !empty($card['featured']);
                                    $badge    = isset($card['badge_text']) ? (string) $card['badge_text'] : '';

                                    if ($label === '' || $url === '') {
                                        continue;
                                    }
                                    ?>
                                    <a
                                        href="<?php echo esc_url($url); ?>"
                                        target="<?php echo esc_attr($target); ?>"
                                        class="kangoo-mega-menu__brand-card<?php echo $featured ? ' is-featured' : ''; ?>"
                                    >
                                        <?php if ($featured && $badge !== '') : ?>
                                            <span class="kangoo-mega-menu__brand-badge"><?php echo esc_html($badge); ?></span>
                                        <?php endif; ?>

                                        <?php if (!empty($image['url'])) : ?>
                                            <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($label); ?>">
                                        <?php endif; ?>

                                        <span><?php echo esc_html($label); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>

                            <?php
                            $brands_view_all_link   = isset($kangoo_mega_menu['brands_view_all_link']) ? $kangoo_mega_menu['brands_view_all_link'] : array();
                            $brands_view_all_url    = function_exists('kangoo_acf_link_url') ? kangoo_acf_link_url($brands_view_all_link) : '';
                            $brands_view_all_target = function_exists('kangoo_acf_link_target') ? kangoo_acf_link_target($brands_view_all_link) : '_self';
                            $brands_view_all_label  = isset($kangoo_mega_menu['brands_view_all_label']) ? (string) $kangoo_mega_menu['brands_view_all_label'] : '';
                            ?>

                            <?php if ($brands_view_all_url !== '' && $brands_view_all_label !== '') : ?>
                                <a
                                    href="<?php echo esc_url($brands_view_all_url); ?>"
                                    target="<?php echo esc_attr($brands_view_all_target); ?>"
                                    class="kangoo-mega-menu__view-all"
                                >
                                    <?php echo esc_html($brands_view_all_label); ?>
                                </a>
                            <?php endif; ?>
                        </section>

                        <section
                            class="kangoo-mega-menu__panel<?php echo $kangoo_default_panel === 'strengths' ? ' is-active' : ''; ?>"
                            data-mega-panel="strengths"
                        >
                            <?php if (!empty($kangoo_mega_menu['strengths_panel_title'])) : ?>
                                <h3><?php echo esc_html($kangoo_mega_menu['strengths_panel_title']); ?></h3>
                            <?php endif; ?>

                            <div class="kangoo-mega-menu__strength-grid">
                                <?php foreach ($kangoo_strength_cards as $card) :
                                    $label  = isset($card['label']) ? (string) $card['label'] : '';
                                    $desc   = isset($card['description']) ? (string) $card['description'] : '';
                                    $mg     = isset($card['mg_range']) ? (string) $card['mg_range'] : '';
                                    $link   = isset($card['link']) ? $card['link'] : array();
                                    $url    = function_exists('kangoo_acf_link_url') ? kangoo_acf_link_url($link) : '';
                                    $target = function_exists('kangoo_acf_link_target') ? kangoo_acf_link_target($link) : '_self';
                                    $dots   = isset($card['dots_on']) ? max(0, min(5, (int) $card['dots_on'])) : 0;
                                    $color  = isset($card['dot_color']) ? (string) $card['dot_color'] : '#4da3ff';

                                    if ($label === '' || $url === '') {
                                        continue;
                                    }
                                    ?>
                                    <a
                                        href="<?php echo esc_url($url); ?>"
                                        target="<?php echo esc_attr($target); ?>"
                                        class="kangoo-mega-menu__strength-card"
                                    >
                                        <strong><?php echo esc_html($label); ?></strong>
                                        <?php if ($desc !== '') : ?><span><?php echo esc_html($desc); ?></span><?php endif; ?>
                                        <?php if ($mg !== '') : ?><span><?php echo esc_html($mg); ?></span><?php endif; ?>

                                        <span class="kangoo-mega-menu__dots">
                                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                <span<?php echo $i <= $dots ? ' style="background:' . esc_attr($color) . ';border-color:' . esc_attr($color) . ';"' : ''; ?>></span>
                                            <?php endfor; ?>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </section>

                        <section
                            class="kangoo-mega-menu__panel<?php echo $kangoo_default_panel === 'flavours' ? ' is-active' : ''; ?>"
                            data-mega-panel="flavours"
                        >
                            <?php if (!empty($kangoo_mega_menu['flavours_panel_title'])) : ?>
                                <h3><?php echo esc_html($kangoo_mega_menu['flavours_panel_title']); ?></h3>
                            <?php endif; ?>

                            <div class="kangoo-mega-menu__flavour-grid">
                                <?php foreach ($kangoo_flavour_cards as $card) :
                                    $label  = isset($card['label']) ? (string) $card['label'] : '';
                                    $link   = isset($card['link']) ? $card['link'] : array();
                                    $url    = function_exists('kangoo_acf_link_url') ? kangoo_acf_link_url($link) : '';
                                    $target = function_exists('kangoo_acf_link_target') ? kangoo_acf_link_target($link) : '_self';
                                    $bg     = isset($card['background_color']) ? (string) $card['background_color'] : '#1b1d23';
                                    $text   = isset($card['text_color']) ? (string) $card['text_color'] : '#ffffff';
                                    $icon   = isset($card['icon']) && is_array($card['icon']) ? $card['icon'] : array();

                                    if ($label === '' || $url === '') {
                                        continue;
                                    }
                                    ?>
                                    <a
                                        href="<?php echo esc_url($url); ?>"
                                        target="<?php echo esc_attr($target); ?>"
                                        class="kangoo-mega-menu__flavour-card"
                                        style="background:<?php echo esc_attr($bg); ?>;color:<?php echo esc_attr($text); ?>;"
                                    >
                                        <?php if (!empty($icon['url'])) : ?>
                                            <img src="<?php echo esc_url($icon['url']); ?>" alt="<?php echo esc_attr($label); ?>">
                                        <?php endif; ?>

                                        <span><?php echo esc_html($label); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>

        <div class="kangoo-mega-drawer" id="kangoo-mega-menu-drawer" aria-hidden="true">
            <div class="kangoo-mega-drawer__overlay" data-mega-menu-close></div>

            <div class="kangoo-mega-drawer__panel">
                <div class="kangoo-mega-drawer__header">
                    <strong><?php echo esc_html($kangoo_mobile_title); ?></strong>
                    <button
                        type="button"
                        class="kangoo-mega-drawer__close"
                        data-mega-menu-close
                        aria-label="<?php esc_attr_e('Close menu', 'kangoo'); ?>"
                    >×</button>
                </div>

                <?php if (!empty($kangoo_top_links)) : ?>
                    <div class="kangoo-mega-drawer__top-links">
                        <?php foreach ($kangoo_top_links as $item) :
                            $label  = isset($item['label']) ? (string) $item['label'] : '';
                            $link   = isset($item['link']) ? $item['link'] : array();
                            $url    = function_exists('kangoo_acf_link_url') ? kangoo_acf_link_url($link) : '';
                            $target = function_exists('kangoo_acf_link_target') ? kangoo_acf_link_target($link) : '_self';

                            if ($label === '' || $url === '') {
                                continue;
                            }
                            ?>
                            <a href="<?php echo esc_url($url); ?>" target="<?php echo esc_attr($target); ?>">
                                <?php echo esc_html($label); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="kangoo-mega-drawer__sections">
                    <?php foreach ($kangoo_mobile_sections as $section) :
                        $label  = isset($section['label']) ? (string) $section['label'] : '';
                        $source = isset($section['source']) ? (string) $section['source'] : '';
                        $open   = !empty($section['open_by_default']);

                        if ($label === '' || $source === '') {
                            continue;
                        }
                        ?>
                        <details class="kangoo-mega-drawer__section"<?php echo $open ? ' open' : ''; ?>>
                            <summary><?php echo esc_html($label); ?></summary>

                            <div class="kangoo-mega-drawer__section-body">
                                <?php if ($source === 'brands') : ?>
                                    <div class="kangoo-mega-drawer__brand-list">
                                        <?php foreach ($kangoo_brand_cards as $card) :
                                            $card_label  = isset($card['label']) ? (string) $card['label'] : '';
                                            $card_link   = isset($card['link']) ? $card['link'] : array();
                                            $card_url    = function_exists('kangoo_acf_link_url') ? kangoo_acf_link_url($card_link) : '';
                                            $card_target = function_exists('kangoo_acf_link_target') ? kangoo_acf_link_target($card_link) : '_self';

                                            if ($card_label === '' || $card_url === '') {
                                                continue;
                                            }
                                            ?>
                                            <a href="<?php echo esc_url($card_url); ?>" target="<?php echo esc_attr($card_target); ?>">
                                                <?php echo esc_html($card_label); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>

                                <?php elseif ($source === 'strengths') : ?>
                                    <div class="kangoo-mega-drawer__strength-list">
                                        <?php foreach ($kangoo_strength_cards as $card) :
                                            $card_label  = isset($card['label']) ? (string) $card['label'] : '';
                                            $card_desc   = isset($card['description']) ? (string) $card['description'] : '';
                                            $card_mg     = isset($card['mg_range']) ? (string) $card['mg_range'] : '';
                                            $card_link   = isset($card['link']) ? $card['link'] : array();
                                            $card_url    = function_exists('kangoo_acf_link_url') ? kangoo_acf_link_url($card_link) : '';
                                            $card_target = function_exists('kangoo_acf_link_target') ? kangoo_acf_link_target($card_link) : '_self';

                                            if ($card_label === '' || $card_url === '') {
                                                continue;
                                            }
                                            ?>
                                            <a href="<?php echo esc_url($card_url); ?>" target="<?php echo esc_attr($card_target); ?>" class="kangoo-mega-drawer__strength-item">
                                                <strong><?php echo esc_html($card_label); ?></strong>
                                                <?php if ($card_desc !== '') : ?><span><?php echo esc_html($card_desc); ?></span><?php endif; ?>
                                                <?php if ($card_mg !== '') : ?><span><?php echo esc_html($card_mg); ?></span><?php endif; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>

                                <?php elseif ($source === 'flavours') : ?>
                                    <div class="kangoo-mega-drawer__flavour-list">
                                        <?php foreach ($kangoo_flavour_cards as $card) :
                                            $card_label  = isset($card['label']) ? (string) $card['label'] : '';
                                            $card_link   = isset($card['link']) ? $card['link'] : array();
                                            $card_url    = function_exists('kangoo_acf_link_url') ? kangoo_acf_link_url($card_link) : '';
                                            $card_target = function_exists('kangoo_acf_link_target') ? kangoo_acf_link_target($card_link) : '_self';

                                            if ($card_label === '' || $card_url === '') {
                                                continue;
                                            }
                                            ?>
                                            <a href="<?php echo esc_url($card_url); ?>" target="<?php echo esc_attr($card_target); ?>">
                                                <?php echo esc_html($card_label); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>

                                <?php elseif ($source === 'custom_links') : ?>
                                    <?php
                                    $custom_links = isset($section['custom_links']) && is_array($section['custom_links'])
                                        ? $section['custom_links']
                                        : array();
                                    ?>
                                    <?php if (!empty($custom_links)) : ?>
                                        <div class="kangoo-mega-drawer__custom-links">
                                            <?php foreach ($custom_links as $item) :
                                                $item_label  = isset($item['label']) ? (string) $item['label'] : '';
                                                $item_link   = isset($item['link']) ? $item['link'] : array();
                                                $item_url    = function_exists('kangoo_acf_link_url') ? kangoo_acf_link_url($item_link) : '';
                                                $item_target = function_exists('kangoo_acf_link_target') ? kangoo_acf_link_target($item_link) : '_self';

                                                if ($item_label === '' || $item_url === '') {
                                                    continue;
                                                }
                                                ?>
                                                <a href="<?php echo esc_url($item_url); ?>" target="<?php echo esc_attr($item_target); ?>">
                                                    <?php echo esc_html($item_label); ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</header>

<div id="cart-drawer" class="cart-drawer">
    <div class="cart-drawer__overlay"></div>

    <div class="cart-drawer__panel">
        <div class="cart-drawer__header">
            <h3><?php esc_html_e('Your cart', 'kangoo'); ?></h3>
            <button class="cart-drawer__close" type="button" aria-label="<?php esc_attr_e('Close cart', 'kangoo'); ?>">×</button>
        </div>

        <div class="cart-drawer__content">
            <?php woocommerce_mini_cart(); ?>
        </div>

        <div class="cart-drawer__footer">
            <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="btn btn--ghost"><?php esc_html_e('View cart', 'kangoo'); ?></a>
            <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="btn btn--primary"><?php esc_html_e('Checkout', 'kangoo'); ?></a>
        </div>
    </div>
</div>