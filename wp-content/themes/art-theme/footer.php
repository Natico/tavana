<?php
/**
 * Site footer.
 *
 * @package ArtTheme
 */

declare(strict_types=1);
?>
<footer id="site-footer" class="site-footer">
	<p>&copy; <?php echo esc_html(gmdate('Y')); ?> <?php bloginfo('name'); ?></p>
</footer>

<?php wp_footer(); ?>
</body>
</html>
