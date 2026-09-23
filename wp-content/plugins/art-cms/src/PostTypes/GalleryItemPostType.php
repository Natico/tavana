<?php
/**
 * Gallery collection custom post type.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\PostTypes;

use ArtCms\Admin\AdminMenu;

/**
 * Registers gallery collections.
 */
final class GalleryItemPostType {
	public const KEY = 'art_gallery_item';

	/**
	 * Register the post type.
	 */
	public function register(): void {
		register_post_type(
			self::KEY,
			array(
				'labels'       => array(
					'name'                  => __('Gallery Collections', 'art-cms'),
					'singular_name'         => __('Gallery Collection', 'art-cms'),
					'add_new'               => __('Add New', 'art-cms'),
					'add_new_item'          => __('Add New Gallery Collection', 'art-cms'),
					'edit_item'             => __('Edit Gallery Collection', 'art-cms'),
					'new_item'              => __('New Gallery Collection', 'art-cms'),
					'view_item'             => __('View Gallery Collection', 'art-cms'),
					'view_items'            => __('View Gallery Collections', 'art-cms'),
					'search_items'          => __('Search Gallery Collections', 'art-cms'),
					'not_found'             => __('No Gallery Collections found.', 'art-cms'),
					'not_found_in_trash'    => __('No Gallery Collections found in Trash.', 'art-cms'),
					'all_items'             => __('All Gallery Collections', 'art-cms'),
					'archives'              => __('Gallery Collection Archives', 'art-cms'),
					'attributes'            => __('Gallery Collection Attributes', 'art-cms'),
					'insert_into_item'      => __('Insert into Gallery Collection', 'art-cms'),
					'uploaded_to_this_item' => __('Uploaded to this Gallery Collection', 'art-cms'),
					'filter_items_list'     => __('Filter Gallery Collections list', 'art-cms'),
					'items_list_navigation' => __('Gallery Collections list navigation', 'art-cms'),
					'items_list'            => __('Gallery Collections list', 'art-cms'),
					'menu_name'             => __('Gallery Collections', 'art-cms'),
					'name_admin_bar'        => __('Gallery Collection', 'art-cms'),
				),
				'public'       => true,
				'show_ui'      => true,
				'show_in_menu' => AdminMenu::SLUG,
				'show_in_rest' => true,
				'has_archive'  => false,
				'supports'     => array(
					'title',
					'revisions',
				),
			)
		);
	}
}
