<?php
/**
 * Site header.
 *
 * @package ArtTheme
 */

declare(strict_types=1);
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#site-content">
	<?php esc_html_e('Skip to content', 'art-theme'); ?>
</a>

<header id="site-header">
	<div>
		<?php if (is_front_page() && is_home()) : ?>
			<h1><?php bloginfo('name'); ?></h1>
		<?php else : ?>
			<p><?php bloginfo('name'); ?></p>
		<?php endif; ?>
	</div>
</header>
