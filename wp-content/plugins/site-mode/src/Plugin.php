<?php
/**
 * Plugin coordinator.
 *
 * @package SiteMode
 */

declare(strict_types=1);

namespace SiteMode;

/**
 * Coordinates Site Mode services.
 */
final class Plugin {
	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		$settings = new Settings();
		$coming_soon = new ComingSoon($settings);

		$settings->register_hooks();
		$coming_soon->register_hooks();
	}
}
