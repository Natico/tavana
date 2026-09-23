<?php
/**
 * Standard gallery collection frontend template.
 *
 * @package ArtTheme
 */

declare(strict_types=1);

use ArtCms\Gallery\GalleryMedia;
use ArtCms\Gallery\GalleryRelationships;
use ArtCms\PostTypes\ProductPostType;
use ArtCms\Taxonomies\GalleryCategoryTaxonomy;
use ArtCms\Template\GalleryTemplateRegistry;

$template_registry = $args['template_registry'] ?? null;
$template_key = isset($args['template_key']) && is_string($args['template_key']) ? $args['template_key'] : '';

if (! $template_registry instanceof GalleryTemplateRegistry || '' === $template_key) {
	return;
}

$post_id = get_the_ID();
$description = get_the_content(null, false, $post_id);
$media_items = get_post_meta($post_id, GalleryMedia::META_MEDIA_ITEMS, true);
$media_items = is_array($media_items) ? $media_items : array();
$cover_media_item_id = get_post_meta($post_id, GalleryMedia::META_COVER_MEDIA_ITEM_ID, true);
$cover_media_item_id = is_string($cover_media_item_id) ? $cover_media_item_id : '';
$categories = get_the_terms($post_id, GalleryCategoryTaxonomy::KEY);
$related_product_ids = get_post_meta($post_id, GalleryRelationships::META_RELATED_PRODUCTS, true);
$related_product_ids = is_array($related_product_ids) ? array_map('absint', $related_product_ids) : array();

$render_media_item = static function (array $item, bool $show_metadata): void {
	$type = isset($item['type']) && is_string($item['type']) ? $item['type'] : '';
	$title = isset($item['title']) && is_string($item['title']) ? $item['title'] : '';
	$caption = isset($item['caption']) && is_string($item['caption']) ? $item['caption'] : '';
	$credit = isset($item['credit']) && is_string($item['credit']) ? $item['credit'] : '';
	?>
	<figure class="gallery-single__media-item gallery-single__media-item--<?php echo esc_attr($type); ?>">
		<?php if ('image' === $type && isset($item['attachment_id'])) : ?>
			<?php $image = wp_get_attachment_image(absint($item['attachment_id']), 'large'); ?>
			<?php if ('' === $image) : ?>
				<?php return; ?>
			<?php endif; ?>
			<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php elseif ('uploaded_video' === $type && isset($item['attachment_id'])) : ?>
			<?php $video_url = wp_get_attachment_url(absint($item['attachment_id'])); ?>
			<?php if (is_string($video_url) && '' !== $video_url) : ?>
				<video controls src="<?php echo esc_url($video_url); ?>"></video>
			<?php else : ?>
				<?php return; ?>
			<?php endif; ?>
		<?php elseif ('external_video' === $type && isset($item['url']) && is_string($item['url']) && '' !== $item['url']) : ?>
			<a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['url']); ?></a>
		<?php else : ?>
			<?php return; ?>
		<?php endif; ?>

		<?php if ($show_metadata && ('' !== $title || '' !== $caption || '' !== $credit)) : ?>
			<figcaption class="gallery-single__media-meta">
				<?php if ('' !== $title) : ?>
					<strong><?php echo esc_html($title); ?></strong>
				<?php endif; ?>
				<?php if ('' !== $caption) : ?>
					<p><?php echo esc_html($caption); ?></p>
				<?php endif; ?>
				<?php if ('' !== $credit) : ?>
					<p><?php echo esc_html($credit); ?></p>
				<?php endif; ?>
			</figcaption>
		<?php endif; ?>
	</figure>
	<?php
};

$cover_item = null;

foreach ($media_items as $media_item) {
	if (! is_array($media_item)) {
		continue;
	}

	if (
		$cover_media_item_id === ($media_item['id'] ?? '')
		&& 'image' === ($media_item['type'] ?? '')
		&& isset($media_item['attachment_id'])
	) {
		$cover_item = $media_item;
		break;
	}
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('gallery-single gallery-template gallery-template--standard'); ?>>
	<header class="gallery-single__header">
		<h1 class="gallery-single__title"><?php the_title(); ?></h1>
	</header>

	<?php if (is_array($cover_item)) : ?>
		<?php $cover_image = wp_get_attachment_image(absint($cover_item['attachment_id']), 'large'); ?>
		<?php if ('' !== $cover_image) : ?>
		<figure class="gallery-single__cover">
			<?php echo $cover_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</figure>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ('' !== trim($description)) : ?>
		<section class="gallery-single__section gallery-single__description">
			<h2><?php esc_html_e('Description', 'art-theme'); ?></h2>
			<div><?php echo wp_kses_post(wpautop(esc_html($description))); ?></div>
		</section>
	<?php endif; ?>

	<?php if (! empty($media_items)) : ?>
		<section class="gallery-single__section">
			<h2><?php esc_html_e('Media', 'art-theme'); ?></h2>
			<div class="gallery-single__media-list">
				<?php foreach ($media_items as $media_item) : ?>
					<?php if (is_array($media_item)) : ?>
						<?php $render_media_item($media_item, true); ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if (is_array($categories) && ! empty($categories)) : ?>
		<section class="gallery-single__section">
			<h2><?php esc_html_e('Gallery Categories', 'art-theme'); ?></h2>
			<ul class="gallery-single__terms">
				<?php foreach ($categories as $category) : ?>
					<?php $category_link = get_term_link($category); ?>
					<?php if (! is_wp_error($category_link)) : ?>
						<li><a href="<?php echo esc_url($category_link); ?>"><?php echo esc_html($category->name); ?></a></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<?php if (! empty($related_product_ids)) : ?>
		<section class="gallery-single__section">
			<h2><?php esc_html_e('Related Products', 'art-theme'); ?></h2>
			<ul class="gallery-single__related-products">
				<?php foreach ($related_product_ids as $related_product_id) : ?>
					<?php $related_product = get_post($related_product_id); ?>
					<?php if ($related_product instanceof WP_Post && ProductPostType::KEY === $related_product->post_type && 'trash' !== $related_product->post_status) : ?>
						<li><a href="<?php echo esc_url(get_permalink($related_product)); ?>"><?php echo esc_html(get_the_title($related_product)); ?></a></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>
</article>
