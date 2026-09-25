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
	<a class="site-header__brand" href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a>

	<?php
	$nav_items = array(
		array(
			'label' => __('Home', 'art-theme'),
			'url'   => home_url('/'),
		),
		array(
			'label' => __('Products', 'art-theme'),
			'url'   => art_theme_get_temporary_nav_url('products'),
		),
		array(
			'label' => __('Gallery', 'art-theme'),
			'url'   => art_theme_get_temporary_nav_url('gallery'),
		),
		array(
			'label' => __('About', 'art-theme'),
			'url'   => art_theme_get_temporary_nav_url('about'),
		),
		array(
			'label' => __('Contact', 'art-theme'),
			'url'   => art_theme_get_temporary_nav_url('contact'),
		),
	);
	?>
	<nav class="site-navigation" aria-label="<?php echo esc_attr__('Primary navigation', 'art-theme'); ?>">
		<ul class="site-navigation__list">
			<?php foreach ($nav_items as $nav_item) : ?>
				<li class="site-navigation__item">
					<?php if (is_string($nav_item['url']) && '' !== $nav_item['url']) : ?>
						<a class="site-navigation__link" href="<?php echo esc_url($nav_item['url']); ?>"><?php echo esc_html($nav_item['label']); ?></a>
					<?php else : ?>
						<span class="site-navigation__link site-navigation__link--disabled" aria-disabled="true"><?php echo esc_html($nav_item['label']); ?></span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
</header>
