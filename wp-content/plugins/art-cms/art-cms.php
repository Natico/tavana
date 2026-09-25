<?php
/**
 * Plugin Name: Art CMS
 * Description: Content models and business rules for the Art website.
 * Version: 0.1.0
 * Requires PHP: 8.3
 * Author: Tavana
 * Text Domain: art-cms
 *
 * @package ArtCms
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

define('ART_CMS_VERSION', '0.1.0');
define('ART_CMS_FILE', __FILE__);
define('ART_CMS_PATH', plugin_dir_path(__FILE__));

require_once ART_CMS_PATH . 'src/Plugin.php';
require_once ART_CMS_PATH . 'src/Admin/AdminMenu.php';
require_once ART_CMS_PATH . 'src/Template/ProductTemplateRegistry.php';
require_once ART_CMS_PATH . 'src/Template/GalleryTemplateRegistry.php';
require_once ART_CMS_PATH . 'src/Integrations/PolylangIntegration.php';
require_once ART_CMS_PATH . 'src/Product/ProductEditor.php';
require_once ART_CMS_PATH . 'src/Product/ProductFields.php';
require_once ART_CMS_PATH . 'src/Product/ProductHomepageFields.php';
require_once ART_CMS_PATH . 'src/Gallery/GalleryEditor.php';
require_once ART_CMS_PATH . 'src/Gallery/GalleryMedia.php';
require_once ART_CMS_PATH . 'src/Gallery/GalleryMediaEditor.php';
require_once ART_CMS_PATH . 'src/Gallery/GalleryRelationships.php';
require_once ART_CMS_PATH . 'src/PostTypes/ProductPostType.php';
require_once ART_CMS_PATH . 'src/PostTypes/GalleryItemPostType.php';
require_once ART_CMS_PATH . 'src/Taxonomies/ProductCategoryFields.php';
require_once ART_CMS_PATH . 'src/Taxonomies/GalleryCategoryFields.php';
require_once ART_CMS_PATH . 'src/Taxonomies/ProductCategoryTaxonomy.php';
require_once ART_CMS_PATH . 'src/Taxonomies/GalleryCategoryTaxonomy.php';

register_activation_hook(
	ART_CMS_FILE,
	static function (): void {
		$plugin = new ArtCms\Plugin();
		$plugin->register_content_types();

		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	ART_CMS_FILE,
	static function (): void {
		flush_rewrite_rules();
	}
);

add_action(
	'init',
	static function (): void {
		$plugin = new ArtCms\Plugin();
		$plugin->register_content_types();
	}
);

add_action(
	'plugins_loaded',
	static function (): void {
		$plugin = new ArtCms\Plugin();
		$plugin->register_hooks();
	}
);
