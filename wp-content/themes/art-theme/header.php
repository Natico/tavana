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
	<a class="site-header__brand" href="<?php echo esc_url(art_theme_get_home_url()); ?>" rel="home"><?php bloginfo('name'); ?></a>

	<?php
	$nav_items = array(
		array(
			'label' => __('Home', 'art-theme'),
			'url'   => art_theme_get_home_url(),
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
	$language_items = art_theme_get_language_switcher_items();
	?>
	<div class="site-header__tools">
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

		<?php if (! empty($language_items)) : ?>
			<nav class="language-switcher" aria-label="<?php echo esc_attr__('Language switcher', 'art-theme'); ?>">
				<ul class="language-switcher__list">
					<?php foreach ($language_items as $language_item) : ?>
						<li class="language-switcher__item">
							<a
								class="language-switcher__link<?php echo $language_item['current'] ? ' language-switcher__link--current' : ''; ?>"
								href="<?php echo esc_url($language_item['url']); ?>"
								<?php echo $language_item['current'] ? 'aria-current="true"' : ''; ?>
							>
								<?php echo esc_html(strtoupper($language_item['slug'])); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>
	</div>
</header>
