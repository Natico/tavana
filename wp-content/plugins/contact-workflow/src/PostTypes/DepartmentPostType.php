<?php
/**
 * Contact department custom post type.
 *
 * @package ContactWorkflow
 */

declare(strict_types=1);

namespace ContactWorkflow\PostTypes;

use ContactWorkflow\Admin\AdminMenu;
use ContactWorkflow\Capabilities;

/**
 * Registers admin-only contact departments.
 */
final class DepartmentPostType {
	public const KEY = 'cw_department';

	/**
	 * Register the post type.
	 */
	public function register(): void {
		register_post_type(
			self::KEY,
			array(
				'labels'              => array(
					'name'                  => __('Contact Departments', 'contact-workflow'),
					'singular_name'         => __('Contact Department', 'contact-workflow'),
					'add_new'               => __('Add New', 'contact-workflow'),
					'add_new_item'          => __('Add New Contact Department', 'contact-workflow'),
					'edit_item'             => __('Edit Contact Department', 'contact-workflow'),
					'new_item'              => __('New Contact Department', 'contact-workflow'),
					'view_item'             => __('View Contact Department', 'contact-workflow'),
					'view_items'            => __('View Contact Departments', 'contact-workflow'),
					'search_items'          => __('Search Contact Departments', 'contact-workflow'),
					'not_found'             => __('No contact departments found.', 'contact-workflow'),
					'not_found_in_trash'    => __('No contact departments found in Trash.', 'contact-workflow'),
					'all_items'             => __('Departments', 'contact-workflow'),
					'attributes'            => __('Contact Department Attributes', 'contact-workflow'),
					'filter_items_list'     => __('Filter contact departments list', 'contact-workflow'),
					'items_list_navigation' => __('Contact departments list navigation', 'contact-workflow'),
					'items_list'            => __('Contact departments list', 'contact-workflow'),
					'menu_name'             => __('Departments', 'contact-workflow'),
					'name_admin_bar'        => __('Contact Department', 'contact-workflow'),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_menu'        => AdminMenu::SLUG,
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'rewrite'             => false,
				'supports'            => array('title'),
				'capabilities'        => array(
					'edit_post'              => Capabilities::MANAGE_DEPARTMENTS,
					'read_post'              => Capabilities::MANAGE_DEPARTMENTS,
					'delete_post'            => Capabilities::MANAGE_DEPARTMENTS,
					'edit_posts'             => Capabilities::MANAGE_DEPARTMENTS,
					'edit_others_posts'      => Capabilities::MANAGE_DEPARTMENTS,
					'delete_posts'           => Capabilities::MANAGE_DEPARTMENTS,
					'delete_others_posts'    => Capabilities::MANAGE_DEPARTMENTS,
					'publish_posts'          => Capabilities::MANAGE_DEPARTMENTS,
					'read_private_posts'     => Capabilities::MANAGE_DEPARTMENTS,
					'delete_private_posts'   => Capabilities::MANAGE_DEPARTMENTS,
					'delete_published_posts' => Capabilities::MANAGE_DEPARTMENTS,
					'edit_private_posts'     => Capabilities::MANAGE_DEPARTMENTS,
					'edit_published_posts'   => Capabilities::MANAGE_DEPARTMENTS,
					'create_posts'           => Capabilities::MANAGE_DEPARTMENTS,
				),
				'map_meta_cap'        => false,
			)
		);
	}
}
