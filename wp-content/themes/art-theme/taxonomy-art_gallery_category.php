<?php
/**
 * Gallery category archive template.
 *
 * @package ArtTheme
 */

declare(strict_types=1);

use ArtCms\Gallery\GalleryMedia;
use ArtCms\PostTypes\GalleryItemPostType;
use ArtCms\Taxonomies\GalleryCategoryFields;
use ArtCms\Taxonomies\GalleryCategoryTaxonomy;

$term = get_queried_object();

get_header();
?>

<main id="site-content" class="site-main">
	<?php if ($term instanceof WP_Term) : ?>
		<?php
		$cover_id = class_exists(GalleryCategoryFields::class)
			? absint(get_term_meta($term->term_id, GalleryCategoryFields::META_COVER_ID, true))
			: 0;
		$children = get_terms(
			array(
				'taxonomy'   => GalleryCategoryTaxonomy::KEY,
				'parent'     => $term->term_id,
				'hide_empty' => false,
			)
		);

		$get_gallery_cover_id = static function (int $post_id): int {
			if (! class_exists(GalleryMedia::class)) {
				return 0;
			}

			$cover_media_item_id = get_post_meta($post_id, GalleryMedia::META_COVER_MEDIA_ITEM_ID, true);
			$cover_media_item_id = is_string($cover_media_item_id) ? $cover_media_item_id : '';
			$media_items = get_post_meta($post_id, GalleryMedia::META_MEDIA_ITEMS, true);

			if ('' === $cover_media_item_id || ! is_array($media_items)) {
				return 0;
			}

			foreach ($media_items as $media_item) {
				if (! is_array($media_item)) {
					continue;
				}

				if (
					$cover_media_item_id === ($media_item['id'] ?? '')
					&& 'image' === ($media_item['type'] ?? '')
					&& isset($media_item['attachment_id'])
				) {
					return absint($media_item['attachment_id']);
				}
			}

			return 0;
		};
		?>
		<section class="gallery-category">
			<?php if ($cover_id > 0 && wp_attachment_is_image($cover_id)) : ?>
				<figure class="gallery-category__cover">
					<?php echo wp_get_attachment_image($cover_id, 'large'); ?>
				</figure>
			<?php endif; ?>

			<header class="gallery-category__header">
				<h1 class="gallery-category__title"><?php echo esc_html($term->name); ?></h1>

				<?php if ('' !== trim((string) $term->description)) : ?>
					<div class="gallery-category__description">
						<?php echo wp_kses_post(wpautop($term->description)); ?>
					</div>
				<?php endif; ?>
			</header>

			<?php if (! is_wp_error($children) && ! empty($children)) : ?>
				<section class="gallery-category__section">
					<h2><?php esc_html_e('Subcategories', 'art-theme'); ?></h2>
					<ul class="gallery-category__children">
						<?php foreach ($children as $child) : ?>
							<?php
							$child_link = get_term_link($child);
							$child_cover_id = class_exists(GalleryCategoryFields::class)
								? absint(get_term_meta($child->term_id, GalleryCategoryFields::META_COVER_ID, true))
								: 0;
							?>
							<?php if (! is_wp_error($child_link)) : ?>
								<li class="gallery-category__child">
									<a href="<?php echo esc_url($child_link); ?>">
										<?php if ($child_cover_id > 0 && wp_attachment_is_image($child_cover_id)) : ?>
											<?php echo wp_get_attachment_image($child_cover_id, 'medium'); ?>
										<?php endif; ?>
										<h3><?php echo esc_html($child->name); ?></h3>
									</a>

									<?php if ('' !== trim((string) $child->description)) : ?>
										<div class="gallery-category__child-description">
											<?php echo wp_kses_post(wpautop($child->description)); ?>
										</div>
									<?php endif; ?>
								</li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<section class="gallery-category__section">
				<h2><?php esc_html_e('Gallery Collections', 'art-theme'); ?></h2>

				<?php if (have_posts()) : ?>
					<div class="gallery-category__collections">
						<?php
						while (have_posts()) :
							the_post();
							?>
							<?php if (GalleryItemPostType::KEY === get_post_type()) : ?>
								<?php $gallery_cover_id = $get_gallery_cover_id(get_the_ID()); ?>
								<article id="post-<?php the_ID(); ?>" <?php post_class('gallery-category__collection'); ?>>
									<a href="<?php the_permalink(); ?>">
										<?php if ($gallery_cover_id > 0 && wp_attachment_is_image($gallery_cover_id)) : ?>
											<?php echo wp_get_attachment_image($gallery_cover_id, 'medium'); ?>
										<?php endif; ?>
										<h3><?php the_title(); ?></h3>
									</a>
								</article>
							<?php endif; ?>
						<?php endwhile; ?>
					</div>
				<?php else : ?>
					<p><?php esc_html_e('No gallery collections found.', 'art-theme'); ?></p>
				<?php endif; ?>
			</section>
		</section>
	<?php endif; ?>
</main>

<?php
get_footer();
