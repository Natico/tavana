<?php
/**
 * Site footer.
 *
 * @package ArtTheme
 */

declare(strict_types=1);
?>
<footer id="site-footer" class="site-footer">
	<div class="site-footer__inner">
		<p>&copy; <?php echo esc_html(gmdate('Y')); ?> <?php bloginfo('name'); ?></p>
		<a href="#site-header"><?php esc_html_e('Back to top', 'art-theme'); ?></a>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
