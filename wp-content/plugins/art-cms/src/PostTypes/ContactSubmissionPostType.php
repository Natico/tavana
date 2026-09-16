<?php
/**
 * Contact submission custom post type.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\PostTypes;

/**
 * Registers admin-only contact submission records.
 */
final class ContactSubmissionPostType {
	public const KEY = 'art_contact_msg';

	/**
	 * Register the post type.
	 */
	public function register(): void {
		register_post_type(
			self::KEY,
			array(
				'labels'              => array(
					'name'                  => __('Contact Submissions', 'art-cms'),
					'singular_name'         => __('Contact Submission', 'art-cms'),
					'add_new'               => __('Add New', 'art-cms'),
					'add_new_item'          => __('Add New Contact Submission', 'art-cms'),
					'edit_item'             => __('Edit Contact Submission', 'art-cms'),
					'new_item'              => __('New Contact Submission', 'art-cms'),
					'view_item'             => __('View Contact Submission', 'art-cms'),
					'view_items'            => __('View Contact Submissions', 'art-cms'),
					'search_items'          => __('Search Contact Submissions', 'art-cms'),
					'not_found'             => __('No contact submissions found.', 'art-cms'),
					'not_found_in_trash'    => __('No contact submissions found in Trash.', 'art-cms'),
					'all_items'             => __('All Contact Submissions', 'art-cms'),
					'attributes'            => __('Contact Submission Attributes', 'art-cms'),
					'filter_items_list'     => __('Filter contact submissions list', 'art-cms'),
					'items_list_navigation' => __('Contact submissions list navigation', 'art-cms'),
					'items_list'            => __('Contact submissions list', 'art-cms'),
					'menu_name'             => __('Contact Submissions', 'art-cms'),
					'name_admin_bar'        => __('Contact Submission', 'art-cms'),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'rewrite'             => false,
				'supports'            => array('title'),
			)
		);
	}
}
