<?php
$eyebrow       = get_sub_field('eyebrow');
$heading       = get_sub_field('heading');
$subheading    = get_sub_field('subheading');
$source        = get_sub_field('source');
$limit         = (int) get_sub_field('limit');
$limit         = $limit > 0 ? $limit : 6;
$category      = get_sub_field('category');
$show_view_all = (bool) get_sub_field('show_view_all');
$view_all_text = get_sub_field('view_all_text') ?: __('View all', 'kangoo');
$view_all_link = get_sub_field('view_all_link');

$args = array(
    'post_type'      => 'product',
    'posts_per_page' => $limit,
    'post_status'    => 'publish',
);

switch ($source) {
    case 'latest':
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
        break;

    case 'best_selling':
        $args['meta_key'] = 'total_sales';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        break;

    case 'top_rated':
        $args['meta_key'] = '_wc_average_rating';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        break;

    case 'featured':
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_visibility',
                'field'    => 'name',
                'terms'    => array('featured'),
            ),
        );
        break;

    case 'category':
        if (!empty($category)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => array((int) $category),
                ),
            );
        }
        break;

    case 'strong':
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'pa_strength',
                'field'    => 'slug',
                'terms'    => array('strong'),
            ),
        );
        break;
}

$query = new WP_Query($args);
?>

<section class="section">
    <div class="container">
        <?php if ($eyebrow || $heading || $subheading) : ?>
            <header class="section-header">
                <?php if ($eyebrow) : ?>
                    <span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
                <?php endif; ?>

                <?php if ($heading) : ?>
                    <h2><?php echo esc_html($heading); ?></h2>
                <?php endif; ?>

                <?php if ($subheading) : ?>
                    <p><?php echo esc_html($subheading); ?></p>
                <?php endif; ?>
            </header>
        <?php endif; ?>

        <?php if ($query->have_posts()) : ?>
            <div class="grid grid--products">
                <?php
                while ($query->have_posts()) :
                    $query->the_post();
                    wc_get_template_part('content', 'product');
                endwhile;
                wp_reset_postdata();
                ?>
            </div>

            <?php if ($show_view_all && $view_all_link) : ?>
                <div class="section-actions">
                    <a class="btn btn--ghost" href="<?php echo esc_url($view_all_link); ?>">
                        <?php echo esc_html($view_all_text); ?>
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>