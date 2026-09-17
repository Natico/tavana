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
