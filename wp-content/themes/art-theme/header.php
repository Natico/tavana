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
	<div class="site-header__inner">
		<a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
		<?php if (is_front_page() && is_home()) : ?>
			<h1 class="site-brand__name"><?php bloginfo('name'); ?></h1>
		<?php else : ?>
			<p class="site-brand__name"><?php bloginfo('name'); ?></p>
		<?php endif; ?>
		</a>

		<?php
		if (has_nav_menu('primary')) {
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => 'nav',
					'container_class' => 'site-navigation',
					'menu_class'     => 'site-navigation__menu',
					'depth'          => 1,
				)
			);
		}
		?>
	</div>
</header>
