<?php
/**
 * Contact submission custom post type.
 *
 * @package ContactWorkflow
 */

declare(strict_types=1);

namespace ContactWorkflow\PostTypes;

use ContactWorkflow\Admin\AdminMenu;
use ContactWorkflow\Capabilities;

/**
 * Registers admin-only contact submission records.
 */
final class SubmissionPostType {
	public const KEY = 'cw_submission';

	/**
	 * Register the post type.
	 */
	public function register(): void {
		register_post_type(
			self::KEY,
			array(
				'labels'              => array(
					'name'                  => __('Contact Submissions', 'contact-workflow'),
					'singular_name'         => __('Contact Submission', 'contact-workflow'),
					'add_new'               => __('Add New', 'contact-workflow'),
					'add_new_item'          => __('Add New Contact Submission', 'contact-workflow'),
					'edit_item'             => __('View Contact Submission', 'contact-workflow'),
					'new_item'              => __('New Contact Submission', 'contact-workflow'),
					'view_item'             => __('View Contact Submission', 'contact-workflow'),
					'view_items'            => __('View Contact Submissions', 'contact-workflow'),
					'search_items'          => __('Search Contact Submissions', 'contact-workflow'),
					'not_found'             => __('No contact submissions found.', 'contact-workflow'),
					'not_found_in_trash'    => __('No contact submissions found in Trash.', 'contact-workflow'),
					'all_items'             => __('Submissions', 'contact-workflow'),
					'attributes'            => __('Contact Submission Attributes', 'contact-workflow'),
					'filter_items_list'     => __('Filter contact submissions list', 'contact-workflow'),
					'items_list_navigation' => __('Contact submissions list navigation', 'contact-workflow'),
					'items_list'            => __('Contact submissions list', 'contact-workflow'),
					'menu_name'             => __('Submissions', 'contact-workflow'),
					'name_admin_bar'        => __('Contact Submission', 'contact-workflow'),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_menu'        => AdminMenu::SLUG,
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'rewrite'             => false,
				'supports'            => false,
				'capabilities'        => array(
					'edit_post'              => Capabilities::VIEW_SUBMISSIONS,
					'read_post'              => Capabilities::VIEW_SUBMISSIONS,
					'delete_post'            => Capabilities::DELETE_SUBMISSIONS,
					'edit_posts'             => Capabilities::VIEW_SUBMISSIONS,
					'edit_others_posts'      => Capabilities::VIEW_SUBMISSIONS,
					'delete_posts'           => Capabilities::DELETE_SUBMISSIONS,
					'delete_others_posts'    => Capabilities::DELETE_SUBMISSIONS,
					'publish_posts'          => 'do_not_allow',
					'read_private_posts'     => Capabilities::VIEW_SUBMISSIONS,
					'delete_private_posts'   => Capabilities::DELETE_SUBMISSIONS,
					'delete_published_posts' => Capabilities::DELETE_SUBMISSIONS,
					'edit_private_posts'     => Capabilities::VIEW_SUBMISSIONS,
					'edit_published_posts'   => Capabilities::VIEW_SUBMISSIONS,
					'create_posts'           => 'do_not_allow',
				),
				'map_meta_cap'        => false,
			)
		);
	}
}
