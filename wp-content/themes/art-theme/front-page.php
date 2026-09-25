<?php
/**
 * Front page template for the Tavana home sketch.
 *
 * @package ArtTheme
 */

use ArtCms\PostTypes\ProductPostType;
use ArtCms\Product\ProductFields;
use ArtCms\Product\ProductHomepageFields;
use ArtCms\Taxonomies\ProductCategoryTaxonomy;

get_header();

$product_post_type       = class_exists(ProductPostType::class) ? ProductPostType::KEY : 'art_product';
$homepage_featured_meta  = class_exists(ProductHomepageFields::class) ? ProductHomepageFields::META_FEATURED : '_art_product_featured_on_homepage';
$homepage_image_meta     = class_exists(ProductHomepageFields::class) ? ProductHomepageFields::META_IMAGE_ID : '_art_product_homepage_image_id';
$product_code_meta       = class_exists(ProductFields::class) ? ProductFields::META_PRODUCT_CODE : '_art_product_code';
$product_design_year_meta = class_exists(ProductFields::class) ? ProductFields::META_DESIGN_YEAR : '_art_product_design_year';
$product_category_taxonomy = class_exists(ProductCategoryTaxonomy::class) ? ProductCategoryTaxonomy::KEY : 'art_product_category';

$featured_products = new WP_Query(
    array(
        'post_type'      => $product_post_type,
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => $homepage_featured_meta,
                'value'   => '1',
                'compare' => '=',
            ),
        ),
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    )
);
?>

<main id="primary" class="site-main home-sketch">
    <section class="home-sketch__intro" aria-labelledby="home-sketch-title">
        <p class="home-sketch__eyebrow"><?php esc_html_e( 'Tavana', 'art-theme' ); ?></p>
        <h1 id="home-sketch-title"><?php bloginfo( 'name' ); ?></h1>
        <?php
        $site_description = get_bloginfo( 'description' );
        if ( '' !== $site_description ) :
            ?>
            <p class="home-sketch__description"><?php echo esc_html( $site_description ); ?></p>
        <?php endif; ?>
    </section>

    <section class="home-product-stream" aria-labelledby="home-products-title">
        <div class="home-product-stream__heading">
            <p class="home-sketch__eyebrow"><?php esc_html_e( 'Featured Products', 'art-theme' ); ?></p>
            <h2 id="home-products-title"><?php esc_html_e( 'Selected products', 'art-theme' ); ?></h2>
        </div>

        <?php if ( $featured_products->have_posts() ) : ?>
            <div class="home-product-stream__list">
                <?php
                $product_number = 1;
                while ( $featured_products->have_posts() ) :
                    $featured_products->the_post();

                    $homepage_image_id = absint( get_post_meta( get_the_ID(), $homepage_image_meta, true ) );
                    $image_id          = 0 < $homepage_image_id && wp_attachment_is_image( $homepage_image_id ) ? $homepage_image_id : get_post_thumbnail_id();
                    $product_code      = trim( (string) get_post_meta( get_the_ID(), $product_code_meta, true ) );
                    $design_year       = get_post_meta( get_the_ID(), $product_design_year_meta, true );
                    $terms             = get_the_terms( get_the_ID(), $product_category_taxonomy );
                    ?>
                    <article <?php post_class( 'home-product-stream__item' ); ?>>
                        <span class="home-product-stream__number"><?php echo esc_html( str_pad( (string) $product_number, 2, '0', STR_PAD_LEFT ) ); ?></span>

                        <div class="home-product-stream__media">
                            <?php if ( $image_id && wp_attachment_is_image( $image_id ) ) : ?>
                                <?php
                                echo wp_get_attachment_image(
                                    $image_id,
                                    'large',
                                    false,
                                    array(
                                        'class' => 'home-product-stream__image',
                                    )
                                );
                                ?>
                            <?php else : ?>
                                <div class="home-product-stream__image-placeholder" aria-hidden="true"></div>
                            <?php endif; ?>
                        </div>

                        <div class="home-product-stream__content">
                            <h3 class="home-product-stream__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

                            <?php if ( '' !== $product_code || '' !== (string) $design_year ) : ?>
                                <div class="home-product-stream__meta">
                                    <?php if ( '' !== $product_code ) : ?>
                                        <span><?php echo esc_html( $product_code ); ?></span>
                                    <?php endif; ?>

                                    <?php if ( '' !== (string) $design_year ) : ?>
                                        <span><?php echo esc_html( (string) $design_year ); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) : ?>
                                <div class="home-product-stream__categories" aria-label="<?php esc_attr_e( 'Product categories', 'art-theme' ); ?>">
                                    <?php foreach ( $terms as $term ) : ?>
                                        <?php
                                        $term_link = get_term_link( $term );
                                        if ( is_wp_error( $term_link ) ) {
                                            continue;
                                        }
                                        ?>
                                        <a href="<?php echo esc_url( $term_link ); ?>"><?php echo esc_html( $term->name ); ?></a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ( has_excerpt() ) : ?>
                                <p class="home-product-stream__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
                            <?php endif; ?>

                            <a class="home-product-stream__more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View product', 'art-theme' ); ?></a>
                        </div>
                    </article>
                    <?php
                    ++$product_number;
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        <?php else : ?>
            <p class="home-product-stream__empty"><?php esc_html_e( 'No featured products have been selected for the homepage yet.', 'art-theme' ); ?></p>
        <?php endif; ?>
    </section>
</main>

<?php
get_footer();
