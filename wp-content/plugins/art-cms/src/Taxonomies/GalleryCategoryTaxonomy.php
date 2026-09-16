<?php
/**
 * Gallery category taxonomy.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Taxonomies;

use ArtCms\PostTypes\GalleryItemPostType;

/**
 * Registers gallery categories.
 */
final class GalleryCategoryTaxonomy {
	public const KEY = 'art_gallery_category';

	/**
	 * Register the taxonomy.
	 */
	public function register(): void {
		register_taxonomy(
			self::KEY,
			array(GalleryItemPostType::KEY),
			array(
				'labels'            => array(
					'name'                       => __('Gallery Categories', 'art-cms'),
					'singular_name'              => __('Gallery Category', 'art-cms'),
					'search_items'               => __('Search Gallery Categories', 'art-cms'),
					'popular_items'              => __('Popular Gallery Categories', 'art-cms'),
					'all_items'                  => __('All Gallery Categories', 'art-cms'),
					'parent_item'                => __('Parent Gallery Category', 'art-cms'),
					'parent_item_colon'          => __('Parent Gallery Category:', 'art-cms'),
					'edit_item'                  => __('Edit Gallery Category', 'art-cms'),
					'view_item'                  => __('View Gallery Category', 'art-cms'),
					'update_item'                => __('Update Gallery Category', 'art-cms'),
					'add_new_item'               => __('Add New Gallery Category', 'art-cms'),
					'new_item_name'              => __('New Gallery Category Name', 'art-cms'),
					'separate_items_with_commas' => __('Separate gallery categories with commas', 'art-cms'),
					'add_or_remove_items'        => __('Add or remove gallery categories', 'art-cms'),
					'choose_from_most_used'      => __('Choose from the most used gallery categories', 'art-cms'),
					'not_found'                  => __('No gallery categories found.', 'art-cms'),
					'no_terms'                   => __('No gallery categories', 'art-cms'),
					'filter_by_item'             => __('Filter by gallery category', 'art-cms'),
					'items_list_navigation'      => __('Gallery categories list navigation', 'art-cms'),
					'items_list'                 => __('Gallery categories list', 'art-cms'),
					'back_to_items'              => __('Back to gallery categories', 'art-cms'),
					'menu_name'                  => __('Gallery Categories', 'art-cms'),
				),
				'hierarchical'      => true,
				'public'            => true,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
			)
		);
	}
}
