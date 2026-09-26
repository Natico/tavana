<?php
/**
 * Plugin Name: Site Mode
 * Description: Controls whether the public WordPress frontend is live or shows a coming soon page.
 * Version: 0.1.0
 * Requires PHP: 8.3
 * Author: Tavana
 * Text Domain: site-mode
 *
 * @package SiteMode
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

define('SITE_MODE_VERSION', '0.1.0');
define('SITE_MODE_FILE', __FILE__);
define('SITE_MODE_PATH', plugin_dir_path(__FILE__));
define('SITE_MODE_URL', plugin_dir_url(__FILE__));

require_once SITE_MODE_PATH . 'src/Plugin.php';
require_once SITE_MODE_PATH . 'src/Settings.php';
require_once SITE_MODE_PATH . 'src/ComingSoon.php';

add_action(
	'plugins_loaded',
	static function (): void {
		$plugin = new SiteMode\Plugin();
		$plugin->register_hooks();
	}
);
