<?php
defined('ABSPATH') || exit;

get_header();

$term = get_queried_object();
$term_id = $term instanceof WP_Term ? $term->term_id : 0;

$category_intro = $term_id ? get_field('category_intro', 'product_cat_' . $term_id) : '';
$category_seo_title = $term_id ? get_field('category_seo_title', 'product_cat_' . $term_id) : '';
$category_seo_content = $term_id ? get_field('category_seo_content', 'product_cat_' . $term_id) : '';

$term_description = '';
if ($term instanceof WP_Term) {
    $term_description = term_description($term, 'product_cat');
}
?>

<main class="category-page">
    <section class="section category-page__hero">
        <div class="container">
            <div class="category-page__hero-inner">
                <header class="category-page__header">
                    <span class="eyebrow">Category</span>
                    <h1><?php woocommerce_page_title(); ?></h1>

                    <?php if ($category_intro) : ?>
                        <p class="category-page__intro">
                            <?php echo esc_html($category_intro); ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($term_description) : ?>
                        <div class="category-page__description wysiwyg">
                            <?php echo wp_kses_post($term_description); ?>
                        </div>
                    <?php endif; ?>
                </header>
            </div>
        </div>
    </section>

    <section class="section category-page__products">
        <div class="container">
            <?php if (woocommerce_product_loop()) : ?>
                <div class="woo-grid">
                    <?php while (have_posts()) : the_post(); ?>
                        <?php wc_get_template_part('content', 'product'); ?>
                    <?php endwhile; ?>
                </div>
            <?php else : ?>
                <div class="category-page__empty card">
                    <h2>No products found</h2>
                    <p>We are adding products to this category soon.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($category_seo_title || $category_seo_content) : ?>
        <section class="section category-page__seo">
            <div class="container container--narrow">
                <header class="section-header section-header--left">
                    <?php if ($category_seo_title) : ?>
                        <h2><?php echo esc_html($category_seo_title); ?></h2>
                    <?php endif; ?>
                </header>

                <?php if ($category_seo_content) : ?>
                    <div class="wysiwyg category-page__seo-content">
                        <?php echo wp_kses_post($category_seo_content); ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($term_id && have_rows('category_faq', 'product_cat_' . $term_id)) : ?>
        <section class="section category-page__faq">
            <div class="container container--narrow">
                <header class="section-header section-header--left">
                    <span class="eyebrow">FAQ</span>
                    <h2>Frequently asked questions</h2>
                </header>

                <div class="faq-list">
                    <?php while (have_rows('category_faq', 'product_cat_' . $term_id)) : the_row(); ?>
                        <details>
                            <summary><?php the_sub_field('question'); ?></summary>
                            <div class="wysiwyg">
                                <p><?php the_sub_field('answer'); ?></p>
                            </div>
                        </details>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php get_footer(); ?>