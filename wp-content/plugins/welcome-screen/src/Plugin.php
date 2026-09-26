<?php
/**
 * Plugin coordinator.
 *
 * @package WelcomeScreen
 */

declare(strict_types=1);

namespace WelcomeScreen;

use WelcomeScreen\Admin\Settings;
use WelcomeScreen\Frontend\Overlay;
use WelcomeScreen\Integrations\PolylangIntegration;
use WelcomeScreen\PostTypes\WelcomeScreenPostType;

/**
 * Coordinates Welcome Screen services.
 */
final class Plugin {
	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		$settings = new Settings();
		$overlay = new Overlay($settings);
		$polylang_integration = new PolylangIntegration();

		$settings->register_hooks();
		$overlay->register_hooks();
		$polylang_integration->register_hooks();
	}

	/**
	 * Register plugin-owned content types.
	 */
	public function register_content_types(): void {
		$post_type = new WelcomeScreenPostType();
		$post_type->register();
	}
}
