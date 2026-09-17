<?php
/**
 * Central plugin coordinator.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms;

use ArtCms\Admin\AdminColumns;
use ArtCms\Admin\AdminMenu;
use ArtCms\PostTypes\ContactSubmissionPostType;
use ArtCms\PostTypes\GalleryItemPostType;
use ArtCms\PostTypes\ProductPostType;
use ArtCms\Taxonomies\GalleryCategoryTaxonomy;
use ArtCms\Taxonomies\ProductCategoryTaxonomy;

/**
 * Coordinates plugin services.
 */
final class Plugin {
	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		$admin_menu = new AdminMenu();
		$admin_columns = new AdminColumns();

		add_action('admin_menu', array($admin_menu, 'register'));
		$admin_columns->register_hooks();
	}

	/**
	 * Register content-related WordPress objects.
	 */
	public function register_content_types(): void {
		$post_types = array(
			new ProductPostType(),
			new GalleryItemPostType(),
			new ContactSubmissionPostType(),
		);

		foreach ($post_types as $post_type) {
			$post_type->register();
		}

		$taxonomies = array(
			new ProductCategoryTaxonomy(),
			new GalleryCategoryTaxonomy(),
		);

		foreach ($taxonomies as $taxonomy) {
			$taxonomy->register();
		}
	}
}
