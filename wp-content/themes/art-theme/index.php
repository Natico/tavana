<?php
/**
 * Main template file.
 *
 * @package ArtTheme
 */

declare(strict_types=1);

get_header();
?>

<main id="site-content" class="site-main">
	<div class="site-main__inner">
	<?php
	if (have_posts()) {
		while (have_posts()) {
			the_post();

			get_template_part('template-parts/content', get_post_type());
		}
	} else {
		?>
		<section class="empty-state">
			<h1><?php esc_html_e('No content found.', 'art-theme'); ?></h1>
			<p><?php esc_html_e('There is no published content to display yet.', 'art-theme'); ?></p>
		</section>
		<?php
	}
	?>
	</div>
</main>

<?php
get_footer();
