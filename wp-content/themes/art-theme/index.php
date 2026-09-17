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
	<?php
	if (have_posts()) {
		while (have_posts()) {
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
				<?php the_content(); ?>
			</article>
			<?php
		}
	} else {
		?>
		<section>
			<h1><?php esc_html_e('No content found.', 'art-theme'); ?></h1>
			<p><?php esc_html_e('There is no published content to display yet.', 'art-theme'); ?></p>
		</section>
		<?php
	}
	?>
</main>

<?php
get_footer();
