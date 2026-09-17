<?php
/**
 * Default content template part.
 *
 * @package ArtTheme
 */

declare(strict_types=1);
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('content-entry'); ?>>
	<?php if (has_post_thumbnail()) : ?>
		<a class="content-entry__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail('large'); ?>
		</a>
	<?php endif; ?>

	<div class="content-entry__body">
		<header class="content-entry__header">
			<?php if (is_singular()) : ?>
				<h1 class="content-entry__title"><?php the_title(); ?></h1>
			<?php else : ?>
				<h2 class="content-entry__title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>
			<?php endif; ?>
		</header>

		<div class="content-entry__content">
			<?php
			if (is_singular()) {
				the_content();
			} else {
				the_excerpt();
			}
			?>
		</div>
	</div>
</article>
