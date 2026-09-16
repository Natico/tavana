<?php
/**
 * Main template file.
 *
 * @package ArtTheme
 */

declare(strict_types=1);

get_header();
?>

<main id="site-content">
	<?php
	if (have_posts()) {
		while (have_posts()) {
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header>
					<h1><?php the_title(); ?></h1>
				</header>

				<div>
					<?php the_content(); ?>
				</div>
			</article>
			<?php
		}
	} else {
		?>
		<p><?php esc_html_e('No content found.', 'art-theme'); ?></p>
		<?php
	}
	?>
</main>

<?php
get_footer();
