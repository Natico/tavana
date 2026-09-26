<?php
/**
 * Plugin Name: Welcome Screen
 * Description: Standalone frontend welcome overlay with Gutenberg-managed content.
 * Version: 0.1.0
 * Requires PHP: 8.3
 * Author: Tavana
 * Text Domain: welcome-screen
 * Domain Path: /languages
 *
 * @package WelcomeScreen
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

define('WELCOME_SCREEN_VERSION', '0.1.0');
define('WELCOME_SCREEN_FILE', __FILE__);
define('WELCOME_SCREEN_PATH', plugin_dir_path(__FILE__));
define('WELCOME_SCREEN_URL', plugin_dir_url(__FILE__));

require_once WELCOME_SCREEN_PATH . 'src/Plugin.php';
require_once WELCOME_SCREEN_PATH . 'src/PostTypes/WelcomeScreenPostType.php';
require_once WELCOME_SCREEN_PATH . 'src/Admin/Settings.php';
require_once WELCOME_SCREEN_PATH . 'src/Frontend/Overlay.php';
require_once WELCOME_SCREEN_PATH . 'src/Integrations/PolylangIntegration.php';

add_action(
	'init',
	static function (): void {
		$plugin = new WelcomeScreen\Plugin();
		$plugin->register_content_types();
	}
);

add_action(
	'plugins_loaded',
	static function (): void {
		load_plugin_textdomain('welcome-screen', false, dirname(plugin_basename(WELCOME_SCREEN_FILE)) . '/languages');

		$plugin = new WelcomeScreen\Plugin();
		$plugin->register_hooks();
	}
);
