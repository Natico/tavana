<?php
/**
 * Coming Soon frontend interception.
 *
 * @package SiteMode
 */

declare(strict_types=1);

namespace SiteMode;

use WP_Post;

/**
 * Renders the Coming Soon page for public frontend requests.
 */
final class ComingSoon {
	private const RETRY_AFTER_SECONDS = 3600;

	/**
	 * Settings service.
	 */
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Settings service.
	 */
	public function __construct(Settings $settings) {
		$this->settings = $settings;
	}

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_action('template_redirect', array($this, 'maybe_render'), 0);
	}

	/**
	 * Render Coming Soon when public frontend requests are blocked.
	 */
	public function maybe_render(): void {
		$options = $this->settings->get_options();

		if (Settings::MODE_COMING_SOON !== $options['mode'] || $this->should_bypass()) {
			return;
		}

		$page = $this->resolve_coming_soon_page($options['coming_soon_page_id']);
		$document_language = $this->get_document_language();
		$is_rtl = $this->is_rtl_request();
		$stylesheet_url = SITE_MODE_URL . 'assets/coming-soon.css?ver=' . rawurlencode(SITE_MODE_VERSION);

		$document_title = __('Coming Soon', 'site-mode');

		if ($page instanceof WP_Post) {
			$content = apply_filters('the_content', $page->post_content);
			$content = is_string($content) ? $content : '';
		} else {
			$content = '<p>' . esc_html__('This site is temporarily unavailable.', 'site-mode') . '</p>';
		}

		$this->send_headers();

		include SITE_MODE_PATH . 'templates/coming-soon.php';
		exit;
	}

	/**
	 * Check whether the current request should bypass Coming Soon mode.
	 */
	private function should_bypass(): bool {
		if (is_admin()) {
			return true;
		}

		if (current_user_can('manage_options')) {
			return true;
		}

		if (wp_doing_ajax() || wp_doing_cron()) {
			return true;
		}

		if ((defined('REST_REQUEST') && REST_REQUEST) || (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)) {
			return true;
		}

		return false;
	}

	/**
	 * Resolve the configured Coming Soon Page for the current Polylang language.
	 *
	 * @param int $base_page_id Configured base Page ID.
	 */
	private function resolve_coming_soon_page(int $base_page_id): ?WP_Post {
		$base_page = $this->get_valid_page($base_page_id);

		if (! $base_page instanceof WP_Post) {
			return null;
		}

		$language = $this->get_polylang_language_slug();

		if ('' === $language || ! function_exists('pll_get_post')) {
			return $base_page;
		}

		$translated_page_id = pll_get_post($base_page->ID, $language);
		$translated_page = is_numeric($translated_page_id) ? $this->get_valid_page(absint($translated_page_id)) : null;

		return $translated_page instanceof WP_Post ? $translated_page : $base_page;
	}

	/**
	 * Get a non-trash Page by ID.
	 *
	 * @param int $page_id Page ID.
	 */
	private function get_valid_page(int $page_id): ?WP_Post {
		if (0 === $page_id) {
			return null;
		}

		$page = get_post($page_id);

		if (! $page instanceof WP_Post || 'page' !== $page->post_type || 'trash' === $page->post_status) {
			return null;
		}

		return $page;
	}

	/**
	 * Get the current Polylang language slug when Polylang is available.
	 */
	private function get_polylang_language_slug(): string {
		if (! function_exists('pll_current_language')) {
			return '';
		}

		$language = pll_current_language('slug');

		return is_string($language) ? $language : '';
	}

	/**
	 * Get the document language attribute value.
	 */
	private function get_document_language(): string {
		if (function_exists('pll_current_language')) {
			$locale = pll_current_language('locale');

			if (is_string($locale) && '' !== $locale) {
				return str_replace('_', '-', $locale);
			}
		}

		$language = get_bloginfo('language');

		return is_string($language) && '' !== $language ? $language : 'und';
	}

	/**
	 * Determine whether the Coming Soon document should be RTL.
	 */
	private function is_rtl_request(): bool {
		if (function_exists('pll_current_language')) {
			$is_rtl = pll_current_language('is_rtl');

			if (is_bool($is_rtl)) {
				return $is_rtl;
			}
		}

		return is_rtl();
	}

	/**
	 * Send Coming Soon response headers.
	 */
	private function send_headers(): void {
		status_header(503);
		nocache_headers();
		header('Retry-After: ' . self::RETRY_AFTER_SECONDS);
		header('Content-Type: text/html; charset=' . get_bloginfo('charset'));
	}
}
