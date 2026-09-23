<?php
/**
 * Minimal gallery collection frontend template.
 *
 * @package ArtTheme
 */

declare(strict_types=1);

use ArtCms\Gallery\GalleryMedia;
use ArtCms\Template\GalleryTemplateRegistry;

$template_registry = $args['template_registry'] ?? null;
$template_key = isset($args['template_key']) && is_string($args['template_key']) ? $args['template_key'] : '';

if (! $template_registry instanceof GalleryTemplateRegistry || '' === $template_key) {
	return;
}

$post_id = get_the_ID();
$media_items = get_post_meta($post_id, GalleryMedia::META_MEDIA_ITEMS, true);
$media_items = is_array($media_items) ? $media_items : array();

$render_media_item = static function (array $item): void {
	$type = isset($item['type']) && is_string($item['type']) ? $item['type'] : '';
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
	</figure>
	<?php
};
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('gallery-single gallery-template gallery-template--minimal'); ?>>
	<header class="gallery-single__header">
		<h1 class="gallery-single__title"><?php the_title(); ?></h1>
	</header>

	<?php if (! empty($media_items)) : ?>
		<section class="gallery-single__section">
			<h2><?php esc_html_e('Media', 'art-theme'); ?></h2>
			<div class="gallery-single__media-list">
				<?php foreach ($media_items as $media_item) : ?>
					<?php if (is_array($media_item)) : ?>
						<?php $render_media_item($media_item); ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>
</article>
