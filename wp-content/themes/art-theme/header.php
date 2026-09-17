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

<header id="site-header" class="site-header">
	<a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a>
</header>
