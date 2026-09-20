<?php
/**
 * Minimal product frontend template.
 *
 * @package ArtTheme
 */

declare(strict_types=1);

use ArtCms\Taxonomies\ProductCategoryTaxonomy;
use ArtCms\Template\ProductTemplateRegistry;

$template_registry = $args['template_registry'] ?? null;
$template_key = isset($args['template_key']) && is_string($args['template_key']) ? $args['template_key'] : '';

if (! $template_registry instanceof ProductTemplateRegistry || '' === $template_key) {
	return;
}

$field_state = static function (string $field_key) use ($template_registry, $template_key): string {
	return $template_registry->get_field_state($template_key, $field_key);
};

$is_visible = static function (string $field_key) use ($field_state): bool {
	return ProductTemplateRegistry::STATE_HIDDEN !== $field_state($field_key);
};

$post_id = get_the_ID();
$short_description = get_post_meta($post_id, '_art_product_short_description', true);
$description = get_the_content(null, false, $post_id);
$categories = get_the_terms($post_id, ProductCategoryTaxonomy::KEY);
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('product-single product-template product-template--minimal'); ?>>
	<header class="product-single__header">
		<h1 class="product-single__title"><?php the_title(); ?></h1>

		<?php if ($is_visible(ProductTemplateRegistry::FIELD_SHORT_DESCRIPTION) && is_string($short_description) && '' !== $short_description) : ?>
			<p class="product-single__short-description"><?php echo esc_html($short_description); ?></p>
		<?php endif; ?>
	</header>

	<?php if ($is_visible(ProductTemplateRegistry::FIELD_COVER) && has_post_thumbnail()) : ?>
		<figure class="product-single__cover">
			<?php the_post_thumbnail('large'); ?>
		</figure>
	<?php endif; ?>

	<?php if ($is_visible(ProductTemplateRegistry::FIELD_DESCRIPTION) && '' !== trim($description)) : ?>
		<section class="product-single__section product-single__description">
			<h2><?php esc_html_e('Description', 'art-theme'); ?></h2>
			<div><?php echo wp_kses_post(wpautop(esc_html($description))); ?></div>
		</section>
	<?php endif; ?>

	<?php if ($is_visible(ProductTemplateRegistry::FIELD_CATEGORIES) && is_array($categories) && ! empty($categories)) : ?>
		<section class="product-single__section">
			<h2><?php esc_html_e('Product Categories', 'art-theme'); ?></h2>
			<ul class="product-single__terms">
				<?php foreach ($categories as $category) : ?>
					<li><?php echo esc_html($category->name); ?></li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>
</article>
