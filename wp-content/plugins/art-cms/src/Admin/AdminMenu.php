<?php
/**
 * Admin menu registration.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Admin;

use ArtCms\PostTypes\ContactSubmissionPostType;
use ArtCms\PostTypes\GalleryItemPostType;
use ArtCms\PostTypes\ProductPostType;
use ArtCms\Taxonomies\GalleryCategoryTaxonomy;
use ArtCms\Taxonomies\ProductCategoryTaxonomy;

/**
 * Registers the plugin parent admin menu.
 */
final class AdminMenu {
	public const SLUG = 'art-cms';

	/**
	 * Register the parent menu used by Art CMS post types.
	 */
	public function register(): void {
		add_menu_page(
			__('ART', 'art-cms'),
			__('ART', 'art-cms'),
			'edit_posts',
			self::SLUG,
			array($this, 'render'),
			'dashicons-art',
			26
		);

		remove_submenu_page(self::SLUG, self::SLUG);
		remove_submenu_page(self::SLUG, 'edit.php?post_type=' . ProductPostType::KEY);
		remove_submenu_page(self::SLUG, 'edit.php?post_type=' . GalleryItemPostType::KEY);
		remove_submenu_page(self::SLUG, 'edit.php?post_type=' . ContactSubmissionPostType::KEY);

		add_submenu_page(
			self::SLUG,
			__('Products', 'art-cms'),
			__('Products', 'art-cms'),
			'edit_posts',
			'edit.php?post_type=' . ProductPostType::KEY
		);

		add_submenu_page(
			self::SLUG,
			__('Product Categories', 'art-cms'),
			__('Product Categories', 'art-cms'),
			'manage_categories',
			'edit-tags.php?taxonomy=' . ProductCategoryTaxonomy::KEY . '&post_type=' . ProductPostType::KEY
		);

		add_submenu_page(
			self::SLUG,
			__('Gallery Collections', 'art-cms'),
			__('Gallery Collections', 'art-cms'),
			'edit_posts',
			'edit.php?post_type=' . GalleryItemPostType::KEY
		);

		add_submenu_page(
			self::SLUG,
			__('Gallery Categories', 'art-cms'),
			__('Gallery Categories', 'art-cms'),
			'manage_categories',
			'edit-tags.php?taxonomy=' . GalleryCategoryTaxonomy::KEY . '&post_type=' . GalleryItemPostType::KEY
		);

		add_submenu_page(
			self::SLUG,
			__('Contact Submissions', 'art-cms'),
			__('Contact Submissions', 'art-cms'),
			'edit_posts',
			'edit.php?post_type=' . ContactSubmissionPostType::KEY
		);
	}

	/**
	 * Render the parent menu landing page.
	 */
	public function render(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e('ART', 'art-cms'); ?></h1>
			<p><?php esc_html_e('Manage ART project content from the menu items below.', 'art-cms'); ?></p>
		</div>
		<?php
	}
}
