<?php
/**
 * Gallery item custom post type.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\PostTypes;

use ArtCms\Admin\AdminMenu;

/**
 * Registers gallery items.
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
					'name'                  => __('Gallery Items', 'art-cms'),
					'singular_name'         => __('Gallery Item', 'art-cms'),
					'add_new'               => __('Add New', 'art-cms'),
					'add_new_item'          => __('Add New Gallery Item', 'art-cms'),
					'edit_item'             => __('Edit Gallery Item', 'art-cms'),
					'new_item'              => __('New Gallery Item', 'art-cms'),
					'view_item'             => __('View Gallery Item', 'art-cms'),
					'view_items'            => __('View Gallery Items', 'art-cms'),
					'search_items'          => __('Search Gallery Items', 'art-cms'),
					'not_found'             => __('No gallery items found.', 'art-cms'),
					'not_found_in_trash'    => __('No gallery items found in Trash.', 'art-cms'),
					'all_items'             => __('All Gallery Items', 'art-cms'),
					'archives'              => __('Gallery Item Archives', 'art-cms'),
					'attributes'            => __('Gallery Item Attributes', 'art-cms'),
					'insert_into_item'      => __('Insert into gallery item', 'art-cms'),
					'uploaded_to_this_item' => __('Uploaded to this gallery item', 'art-cms'),
					'filter_items_list'     => __('Filter gallery items list', 'art-cms'),
					'items_list_navigation' => __('Gallery items list navigation', 'art-cms'),
					'items_list'            => __('Gallery items list', 'art-cms'),
					'menu_name'             => __('Gallery Items', 'art-cms'),
					'name_admin_bar'        => __('Gallery Item', 'art-cms'),
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
