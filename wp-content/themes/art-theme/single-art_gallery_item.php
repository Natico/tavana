<?php
/**
 * Single gallery collection template.
 *
 * @package ArtTheme
 */

declare(strict_types=1);

use ArtCms\Template\GalleryTemplateRegistry;

get_header();
?>

<main id="site-content" class="site-main">
	<?php
	if (have_posts()) {
		while (have_posts()) {
			the_post();

			$template_key = '';
			$template_definition = null;
			$template_part = '';

			if (class_exists(GalleryTemplateRegistry::class)) {
				$template_registry = new GalleryTemplateRegistry();
				$raw_template_key = get_post_meta(get_the_ID(), GalleryTemplateRegistry::META_SELECTED_TEMPLATE, true);
				$template_key = $template_registry->sanitize_template_key(is_string($raw_template_key) ? $raw_template_key : '');

				$template_parts = array(
					'standard' => 'standard',
					'minimal'  => 'minimal',
				);

				if ('' !== $template_key && $template_registry->has_template($template_key) && isset($template_parts[$template_key])) {
					$template_definition = $template_registry->get_templates()[$template_key];
					$template_part = $template_parts[$template_key];
				}
			}

			if ('' === $template_part || null === $template_definition) {
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class('gallery-single gallery-single--fallback'); ?>>
					<header class="gallery-single__header">
						<h1 class="gallery-single__title"><?php the_title(); ?></h1>
					</header>
					<p><?php esc_html_e('Gallery layout has not been configured yet.', 'art-theme'); ?></p>
				</article>
				<?php
				continue;
			}

			get_template_part(
				'template-parts/gallery/' . $template_part,
				null,
				array(
					'template_key'        => $template_key,
					'template_definition' => $template_definition,
					'template_registry'   => $template_registry,
				)
			);
		}
	}
	?>
</main>

<?php
get_footer();
