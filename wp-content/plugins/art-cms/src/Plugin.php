<?php
/**
 * Central plugin coordinator.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms;

use ArtCms\Admin\AdminMenu;
use ArtCms\Gallery\GalleryEditor;
use ArtCms\Gallery\GalleryMedia;
use ArtCms\Gallery\GalleryMediaEditor;
use ArtCms\Gallery\GalleryRelationships;
use ArtCms\Integrations\PolylangIntegration;
use ArtCms\PostTypes\GalleryItemPostType;
use ArtCms\PostTypes\ProductPostType;
use ArtCms\Product\ProductEditor;
use ArtCms\Product\ProductFields;
use ArtCms\Product\ProductHomepageFields;
use ArtCms\Taxonomies\GalleryCategoryFields;
use ArtCms\Taxonomies\ProductCategoryFields;
use ArtCms\Template\GalleryTemplateRegistry;
use ArtCms\Template\ProductTemplateRegistry;
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
		$product_templates = new ProductTemplateRegistry();
		$product_editor = new ProductEditor($product_templates);
		$product_fields = new ProductFields($product_templates);
		$product_homepage_fields = new ProductHomepageFields();
		$product_category_fields = new ProductCategoryFields();
		$gallery_category_fields = new GalleryCategoryFields();
		$gallery_templates = new GalleryTemplateRegistry();
		$gallery_editor = new GalleryEditor($gallery_templates);
		$gallery_media = new GalleryMedia();
		$gallery_media_editor = new GalleryMediaEditor($gallery_media);
		$gallery_relationships = new GalleryRelationships();
		$polylang_integration = new PolylangIntegration();

		add_action('admin_menu', array($admin_menu, 'register'));
		$product_editor->register_hooks();
		$product_fields->register_hooks();
		$product_homepage_fields->register_hooks();
		$product_category_fields->register_hooks();
		$gallery_category_fields->register_hooks();
		$gallery_editor->register_hooks();
		$gallery_media->register_hooks();
		$gallery_media_editor->register_hooks();
		$gallery_relationships->register_hooks();
		$polylang_integration->register_hooks();
	}

	/**
	 * Register content-related WordPress objects.
	 */
	public function register_content_types(): void {
		$post_types = array(
			new ProductPostType(),
			new GalleryItemPostType(),
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
