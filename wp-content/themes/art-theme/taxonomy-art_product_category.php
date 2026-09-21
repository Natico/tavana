<?php
/**
 * Product category archive template.
 *
 * @package ArtTheme
 */

declare(strict_types=1);

use ArtCms\PostTypes\ProductPostType;
use ArtCms\Taxonomies\ProductCategoryFields;
use ArtCms\Taxonomies\ProductCategoryTaxonomy;

$term = get_queried_object();

get_header();
?>

<main id="site-content" class="site-main">
	<?php if ($term instanceof WP_Term) : ?>
		<?php
		$cover_id = class_exists(ProductCategoryFields::class)
			? absint(get_term_meta($term->term_id, ProductCategoryFields::META_COVER_ID, true))
			: 0;
		$children = get_terms(
			array(
				'taxonomy'   => ProductCategoryTaxonomy::KEY,
				'parent'     => $term->term_id,
				'hide_empty' => false,
			)
		);
		?>
		<section class="product-category">
			<?php if ($cover_id > 0 && wp_attachment_is_image($cover_id)) : ?>
				<figure class="product-category__cover">
					<?php echo wp_get_attachment_image($cover_id, 'large'); ?>
				</figure>
			<?php endif; ?>

			<header class="product-category__header">
				<h1 class="product-category__title"><?php echo esc_html($term->name); ?></h1>

				<?php if ('' !== trim((string) $term->description)) : ?>
					<div class="product-category__description">
						<?php echo wp_kses_post(wpautop($term->description)); ?>
					</div>
				<?php endif; ?>
			</header>

			<?php if (! is_wp_error($children) && ! empty($children)) : ?>
				<section class="product-category__section">
					<h2><?php esc_html_e('Subcategories', 'art-theme'); ?></h2>
					<ul class="product-category__children">
						<?php foreach ($children as $child) : ?>
							<?php $child_link = get_term_link($child); ?>
							<?php if (! is_wp_error($child_link)) : ?>
								<li>
									<a href="<?php echo esc_url($child_link); ?>"><?php echo esc_html($child->name); ?></a>
								</li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<section class="product-category__section">
				<h2><?php esc_html_e('Products', 'art-theme'); ?></h2>

				<?php if (have_posts()) : ?>
					<div class="product-category__products">
						<?php
						while (have_posts()) :
							the_post();
							?>
							<?php if (ProductPostType::KEY === get_post_type()) : ?>
								<article id="post-<?php the_ID(); ?>" <?php post_class('product-category__product'); ?>>
									<a href="<?php the_permalink(); ?>">
										<?php if (has_post_thumbnail()) : ?>
											<?php the_post_thumbnail('medium'); ?>
										<?php endif; ?>
										<h3><?php the_title(); ?></h3>
									</a>
								</article>
							<?php endif; ?>
						<?php endwhile; ?>
					</div>
				<?php else : ?>
					<p><?php esc_html_e('No products found in this category yet.', 'art-theme'); ?></p>
				<?php endif; ?>
			</section>
		</section>
	<?php endif; ?>
</main>

<?php
get_footer();
