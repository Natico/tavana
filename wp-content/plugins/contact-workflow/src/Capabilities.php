<?php
/**
 * Contact Workflow capability management.
 *
 * @package ContactWorkflow
 */

declare(strict_types=1);

namespace ContactWorkflow;

/**
 * Defines and grants Contact Workflow capabilities.
 */
final class Capabilities {
	public const VIEW_SUBMISSIONS = 'cw_view_submissions';
	public const EDIT_SUBMISSION_STATUS = 'cw_edit_submission_status';
	public const DELETE_SUBMISSIONS = 'cw_delete_submissions';
	public const MANAGE_DEPARTMENTS = 'cw_manage_departments';

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_action('admin_init', array($this, 'grant_administrator_capabilities'));
	}

	/**
	 * Grant Contact Workflow capabilities to administrators.
	 */
	public function grant_administrator_capabilities(): void {
		$role = get_role('administrator');

		if (null === $role) {
			return;
		}

		foreach (self::get_administrator_capabilities() as $capability) {
			if (! $role->has_cap($capability)) {
				$role->add_cap($capability);
			}
		}
	}

	/**
	 * Get administrator Contact Workflow capabilities.
	 *
	 * @return string[]
	 */
	public static function get_administrator_capabilities(): array {
		return array(
			self::VIEW_SUBMISSIONS,
			self::EDIT_SUBMISSION_STATUS,
			self::DELETE_SUBMISSIONS,
			self::MANAGE_DEPARTMENTS,
		);
	}
}
