<?php
defined('ABSPATH') || exit;

global $product;

if (empty($product) || !$product->is_visible()) {
    return;
}

$badge = get_field('badge');
$strength = get_field('strength');

$is_home_card = is_front_page();
$is_variable = $product->is_type('variable');
$modal_id = 'quick-add-' . $product->get_id();

$available_variations = array();
$has_purchasable_variation = false;

if ($is_home_card && $is_variable) {
    $available_variations = $product->get_available_variations();

    foreach ($available_variations as $variation_data) {
        $is_in_stock = !empty($variation_data['is_in_stock']);
        $is_purchasable = !isset($variation_data['is_purchasable']) || !empty($variation_data['is_purchasable']);

        if ($is_in_stock && $is_purchasable) {
            $has_purchasable_variation = true;
            break;
        }
    }
}
?>

<article <?php wc_product_class('product-card', $product); ?>>
    <?php if ($badge && $badge !== 'none') : ?>
        <div class="product-badge product-badge--<?php echo esc_attr($badge); ?>">
            <?php echo esc_html(ucfirst(str_replace('_', ' ', $badge))); ?>
        </div>
    <?php endif; ?>

    <a href="<?php the_permalink(); ?>" class="product-card__media">
        <?php echo woocommerce_get_product_thumbnail('woocommerce_thumbnail'); ?>
    </a>

    <div class="product-card__content">
        <h3 class="product-card__title">
            <a href="<?php the_permalink(); ?>">
                <?php the_title(); ?>
            </a>
        </h3>

        <?php if ($strength) : ?>
            <div class="product-card__strength">
                <?php echo esc_html(ucfirst($strength)); ?>
            </div>
        <?php endif; ?>

        <div class="product-card__price">
            <?php echo wp_kses_post($product->get_price_html()); ?>
        </div>

        <div class="product-card__actions">
            <?php if ($is_home_card && $is_variable) : ?>
                <?php if ($has_purchasable_variation && !empty($available_variations)) : ?>
                    <button
                        type="button"
                        class="btn btn--primary quick-add-open"
                        data-quick-add-target="<?php echo esc_attr($modal_id); ?>"
                    >
                        Choose options
                    </button>
                <?php else : ?>
                    <button type="button" class="btn btn--primary is-disabled" disabled>
                        Out of stock
                    </button>
                <?php endif; ?>
            <?php else : ?>
                <?php
                echo apply_filters(
                    'woocommerce_loop_add_to_cart_link',
                    sprintf(
                        '<a href="%s" data-quantity="1" class="btn btn--primary">%s</a>',
                        esc_url($product->add_to_cart_url()),
                        esc_html($product->add_to_cart_text())
                    ),
                    $product
                );
                ?>
            <?php endif; ?>
        </div>
    </div>
</article>

<?php if ($is_home_card && $is_variable && $has_purchasable_variation && !empty($available_variations)) : ?>
    <div
        class="quick-add-modal"
        id="<?php echo esc_attr($modal_id); ?>"
        aria-hidden="true"
        data-product-id="<?php echo esc_attr($product->get_id()); ?>"
    >
        <div class="quick-add-modal__overlay" data-quick-add-close></div>

        <div class="quick-add-modal__panel" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr(get_the_title()); ?>">
            <button type="button" class="quick-add-modal__close" data-quick-add-close aria-label="Close">
                &times;
            </button>

            <div class="quick-add-modal__top">
                <div class="quick-add-modal__image">
                    <?php echo woocommerce_get_product_thumbnail('woocommerce_thumbnail'); ?>
                </div>

                <div class="quick-add-modal__summary">
                    <div class="quick-add-modal__title"><?php the_title(); ?></div>
                    <div class="quick-add-modal__price" data-quick-add-price>
                        <?php echo wp_kses_post($product->get_price_html()); ?>
                    </div>
                </div>
            </div>

            <form class="quick-add-form">
                <input type="hidden" name="product_id" value="<?php echo esc_attr($product->get_id()); ?>">
                <input type="hidden" name="variation_id" value="">
                <input type="hidden" name="quantity" value="1">

                <?php foreach ($product->get_variation_attributes() as $attribute_name => $options) : ?>
                    <?php
                    $label = wc_attribute_label($attribute_name);
                    ?>
                    <div class="quick-add-group">
                        <div class="quick-add-group__label"><?php echo esc_html($label); ?></div>

                        <div class="quick-add-pills" data-attribute="<?php echo esc_attr('attribute_' . $attribute_name); ?>">
                            <?php foreach ($options as $option) : ?>
                                <?php
                                $term = taxonomy_exists($attribute_name) ? get_term_by('slug', $option, $attribute_name) : false;
                                $option_label = $term && !is_wp_error($term) ? $term->name : $option;
                                ?>
                                <button
                                    type="button"
                                    class="quick-add-pill"
                                    data-name="<?php echo esc_attr('attribute_' . $attribute_name); ?>"
                                    data-value="<?php echo esc_attr($option); ?>"
                                >
                                    <?php echo esc_html($option_label); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="quick-add-qty">
                    <button type="button" class="qty-btn qty-btn--minus" data-quick-add-minus>-</button>
                    <input type="number" class="quick-add-qty__input" value="1" min="1" aria-label="Quantity">
                    <button type="button" class="qty-btn qty-btn--plus" data-quick-add-plus>+</button>
                </div>

                <button type="submit" class="btn btn--primary quick-add-submit is-disabled" disabled>
                    Add to cart
                </button>
            </form>

            <script type="application/json" class="quick-add-variations">
                <?php echo wp_json_encode($available_variations); ?>
            </script>
        </div>
    </div>
<?php endif; ?>