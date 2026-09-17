<?php
/**
 * Product custom post type.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\PostTypes;

use ArtCms\Admin\AdminMenu;

/**
 * Registers showcase products.
 */
final class ProductPostType {
	public const KEY = 'art_product';

	/**
	 * Register the post type.
	 */
	public function register(): void {
		register_post_type(
			self::KEY,
			array(
				'labels'       => array(
					'name'                  => __('Products', 'art-cms'),
					'singular_name'         => __('Product', 'art-cms'),
					'add_new'               => __('Add New', 'art-cms'),
					'add_new_item'          => __('Add New Product', 'art-cms'),
					'edit_item'             => __('Edit Product', 'art-cms'),
					'new_item'              => __('New Product', 'art-cms'),
					'view_item'             => __('View Product', 'art-cms'),
					'view_items'            => __('View Products', 'art-cms'),
					'search_items'          => __('Search Products', 'art-cms'),
					'not_found'             => __('No products found.', 'art-cms'),
					'not_found_in_trash'    => __('No products found in Trash.', 'art-cms'),
					'all_items'             => __('All Products', 'art-cms'),
					'archives'              => __('Product Archives', 'art-cms'),
					'attributes'            => __('Product Attributes', 'art-cms'),
					'insert_into_item'      => __('Insert into product', 'art-cms'),
					'uploaded_to_this_item' => __('Uploaded to this product', 'art-cms'),
					'filter_items_list'     => __('Filter products list', 'art-cms'),
					'items_list_navigation' => __('Products list navigation', 'art-cms'),
					'items_list'            => __('Products list', 'art-cms'),
					'menu_name'             => __('Products', 'art-cms'),
					'name_admin_bar'        => __('Product', 'art-cms'),
				),
				'public'       => true,
				'show_ui'      => true,
				'show_in_menu' => AdminMenu::SLUG,
				'show_in_rest' => true,
				'has_archive'  => false,
				'supports'     => array(
					'title',
					'editor',
					'revisions',
					'author',
					'thumbnail',
					'custom-fields',
				),
			)
		);
	}
}
