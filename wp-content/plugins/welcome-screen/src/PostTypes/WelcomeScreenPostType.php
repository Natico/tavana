<?php
/**
 * Welcome Screen custom post type.
 *
 * @package WelcomeScreen
 */

declare(strict_types=1);

namespace WelcomeScreen\PostTypes;

/**
 * Registers internal Welcome Screen content.
 */
final class WelcomeScreenPostType {
	public const KEY = 'ws_screen';

	/**
	 * Register the post type.
	 */
	public function register(): void {
		register_post_type(
			self::KEY,
			array(
				'labels'             => array(
					'name'                  => __('Welcome Screens', 'welcome-screen'),
					'singular_name'         => __('Welcome Screen', 'welcome-screen'),
					'add_new'               => __('Add New', 'welcome-screen'),
					'add_new_item'          => __('Add New Welcome Screen', 'welcome-screen'),
					'edit_item'             => __('Edit Welcome Screen', 'welcome-screen'),
					'new_item'              => __('New Welcome Screen', 'welcome-screen'),
					'view_item'             => __('View Welcome Screen', 'welcome-screen'),
					'search_items'          => __('Search Welcome Screens', 'welcome-screen'),
					'not_found'             => __('No Welcome Screens found.', 'welcome-screen'),
					'not_found_in_trash'    => __('No Welcome Screens found in Trash.', 'welcome-screen'),
					'all_items'             => __('All Welcome Screens', 'welcome-screen'),
					'insert_into_item'      => __('Insert into Welcome Screen', 'welcome-screen'),
					'uploaded_to_this_item' => __('Uploaded to this Welcome Screen', 'welcome-screen'),
					'filter_items_list'     => __('Filter Welcome Screens list', 'welcome-screen'),
					'items_list_navigation' => __('Welcome Screens list navigation', 'welcome-screen'),
					'items_list'            => __('Welcome Screens list', 'welcome-screen'),
					'menu_name'             => __('Welcome Screens', 'welcome-screen'),
					'name_admin_bar'        => __('Welcome Screen', 'welcome-screen'),
				),
				'public'             => false,
				'publicly_queryable' => false,
				'exclude_from_search' => true,
				'show_ui'            => true,
				'show_in_menu'       => false,
				'show_in_rest'       => true,
				'has_archive'        => false,
				'rewrite'            => false,
				'query_var'          => false,
				'supports'           => array(
					'title',
					'editor',
					'revisions',
					'thumbnail',
				),
			)
		);
	}
}
