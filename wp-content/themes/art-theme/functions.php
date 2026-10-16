<?php
/**
 * Theme bootstrap.
 *
 * @package ArtTheme
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

add_action(
	'after_setup_theme',
	static function (): void {
		add_theme_support('title-tag');
		add_theme_support('post-thumbnails');
		add_theme_support('html5', array('comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
	}
);

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		wp_enqueue_style(
			'art-theme-style',
			get_stylesheet_uri(),
			array(),
			wp_get_theme()->get('Version')
		);
	}
);


/**
 * Resolve the current language home URL when Polylang is available.
 */
function art_theme_get_home_url(): string {
	if (function_exists('pll_current_language') && function_exists('pll_home_url')) {
		$language = pll_current_language('slug');
		$url = is_string($language) && '' !== $language ? pll_home_url($language) : pll_home_url();

		if (is_string($url) && '' !== $url) {
			return $url;
		}
	}

	return home_url('/');
}

/**
 * Resolve a temporary navigation URL by page slug for the current language.
 *
 * @param string $page_path Page path.
 */
function art_theme_get_temporary_nav_url(string $page_path): string {
	$page = get_page_by_path($page_path);

	if (! $page instanceof WP_Post || 'publish' !== $page->post_status) {
		return '';
	}

	if (function_exists('pll_current_language') && function_exists('pll_get_post')) {
		$language = pll_current_language('slug');
		$translated_page_id = is_string($language) && '' !== $language ? pll_get_post($page->ID, $language) : 0;
		$translated_page = is_numeric($translated_page_id) ? get_post(absint($translated_page_id)) : null;

		if ($translated_page instanceof WP_Post && 'publish' === $translated_page->post_status) {
			$page = $translated_page;
		}
	}

	$permalink = get_permalink($page);

	return is_string($permalink) ? $permalink : '';
}

/**
 * Get language switcher links from Polylang when available.
 *
 * @return array<int, array{slug: string, url: string, current: bool}>
 */
function art_theme_get_language_switcher_items(): array {
	if (! function_exists('pll_the_languages')) {
		return array();
	}

	$languages = pll_the_languages(
		array(
			'raw'                    => 1,
			'show_flags'             => 0,
			'show_names'             => 0,
			'hide_if_no_translation' => 0,
			'hide_current'           => 0,
		)
	);

	if (! is_array($languages)) {
		return array();
	}

	$items = array();

	foreach ($languages as $language) {
		if (! is_array($language)) {
			continue;
		}

		$slug = isset($language['slug']) && is_string($language['slug']) ? $language['slug'] : '';
		$url = isset($language['url']) && is_string($language['url']) ? $language['url'] : '';

		if ('' === $slug || '' === $url) {
			continue;
		}

		$items[] = array(
			'slug'    => $slug,
			'url'     => $url,
			'current' => ! empty($language['current_lang']),
		);
	}

	return $items;
}
