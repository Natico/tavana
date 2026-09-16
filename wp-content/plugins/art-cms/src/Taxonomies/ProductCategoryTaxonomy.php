<?php
/**
 * Product category taxonomy.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Taxonomies;

use ArtCms\PostTypes\ProductPostType;

/**
 * Registers product categories.
 */
final class ProductCategoryTaxonomy {
	public const KEY = 'art_product_category';

	/**
	 * Register the taxonomy.
	 */
	public function register(): void {
		register_taxonomy(
			self::KEY,
			array(ProductPostType::KEY),
			array(
				'labels'            => array(
					'name'                       => __('Product Categories', 'art-cms'),
					'singular_name'              => __('Product Category', 'art-cms'),
					'search_items'               => __('Search Product Categories', 'art-cms'),
					'popular_items'              => __('Popular Product Categories', 'art-cms'),
					'all_items'                  => __('All Product Categories', 'art-cms'),
					'parent_item'                => __('Parent Product Category', 'art-cms'),
					'parent_item_colon'          => __('Parent Product Category:', 'art-cms'),
					'edit_item'                  => __('Edit Product Category', 'art-cms'),
					'view_item'                  => __('View Product Category', 'art-cms'),
					'update_item'                => __('Update Product Category', 'art-cms'),
					'add_new_item'               => __('Add New Product Category', 'art-cms'),
					'new_item_name'              => __('New Product Category Name', 'art-cms'),
					'separate_items_with_commas' => __('Separate product categories with commas', 'art-cms'),
					'add_or_remove_items'        => __('Add or remove product categories', 'art-cms'),
					'choose_from_most_used'      => __('Choose from the most used product categories', 'art-cms'),
					'not_found'                  => __('No product categories found.', 'art-cms'),
					'no_terms'                   => __('No product categories', 'art-cms'),
					'filter_by_item'             => __('Filter by product category', 'art-cms'),
					'items_list_navigation'      => __('Product categories list navigation', 'art-cms'),
					'items_list'                 => __('Product categories list', 'art-cms'),
					'back_to_items'              => __('Back to product categories', 'art-cms'),
					'menu_name'                  => __('Product Categories', 'art-cms'),
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
