<?php
/**
 * Single product template.
 *
 * @package ArtTheme
 */

declare(strict_types=1);

use ArtCms\Template\ProductTemplateRegistry;

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

			if (class_exists(ProductTemplateRegistry::class)) {
				$template_registry = new ProductTemplateRegistry();
				$template_key = $template_registry->get_selected_template_key(get_the_ID());

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
				<article id="post-<?php the_ID(); ?>" <?php post_class('product-single product-single--fallback'); ?>>
					<header class="product-single__header">
						<h1 class="product-single__title"><?php the_title(); ?></h1>
					</header>
					<p><?php esc_html_e('Product layout has not been configured yet.', 'art-theme'); ?></p>
				</article>
				<?php
				continue;
			}

			get_template_part(
				'template-parts/product/' . $template_part,
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
