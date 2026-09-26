<?php
/**
 * Polylang integration.
 *
 * @package WelcomeScreen
 */

declare(strict_types=1);

namespace WelcomeScreen\Integrations;

use WelcomeScreen\PostTypes\WelcomeScreenPostType;

/**
 * Makes the internal Welcome Screen CPT available to Polylang.
 */
final class PolylangIntegration {
	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_filter('pll_get_post_types', array($this, 'add_translatable_post_types'), 10, 2);
	}

	/**
	 * Add internal Welcome Screen content to Polylang's custom post type list.
	 *
	 * @param array<int|string, string> $post_types Post type keys selected by Polylang.
	 * @param bool                     $hide Whether Polylang is requesting hidden programmatic types.
	 * @return array<int|string, string>
	 */
	public function add_translatable_post_types(array $post_types, bool $hide): array {
		if (! $hide) {
			return $post_types;
		}

		$post_types[] = WelcomeScreenPostType::KEY;

		return array_values(array_unique($post_types));
	}
}
