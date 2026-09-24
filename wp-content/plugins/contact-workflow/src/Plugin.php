<?php
/**
 * Central plugin coordinator.
 *
 * @package ContactWorkflow
 */

declare(strict_types=1);

namespace ContactWorkflow;

use ContactWorkflow\Admin\AdminMenu;
use ContactWorkflow\Admin\DepartmentDeletionGuard;
use ContactWorkflow\Admin\DepartmentFields;
use ContactWorkflow\Admin\SubmissionAdmin;
use ContactWorkflow\Data\SubmissionFields;
use ContactWorkflow\Frontend\FormHandler;
use ContactWorkflow\Frontend\FormShortcode;
use ContactWorkflow\PostTypes\DepartmentPostType;
use ContactWorkflow\PostTypes\SubmissionPostType;

/**
 * Coordinates Contact Workflow services.
 */
final class Plugin {
	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		$capabilities = new Capabilities();
		$admin_menu = new AdminMenu();
		$submission_fields = new SubmissionFields();
		$submission_admin = new SubmissionAdmin();
		$department_fields = new DepartmentFields();
		$department_deletion_guard = new DepartmentDeletionGuard();
		$form_handler = new FormHandler();
		$form_shortcode = new FormShortcode($form_handler);

		$capabilities->register_hooks();
		add_action('admin_menu', array($admin_menu, 'register'));
		$submission_fields->register_hooks();
		$submission_admin->register_hooks();
		$department_fields->register_hooks();
		$department_deletion_guard->register_hooks();
		$form_handler->register_hooks();
		$form_shortcode->register_hooks();
	}

	/**
	 * Register Contact Workflow post types.
	 */
	public function register_content_types(): void {
		$post_types = array(
			new SubmissionPostType(),
			new DepartmentPostType(),
		);

		foreach ($post_types as $post_type) {
			$post_type->register();
		}
	}

	/**
	 * Grant plugin capabilities.
	 */
	public function grant_capabilities(): void {
		$capabilities = new Capabilities();
		$capabilities->grant_administrator_capabilities();
	}
}
