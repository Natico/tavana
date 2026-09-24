<?php
/**
 * Contact Workflow admin menu.
 *
 * @package ContactWorkflow
 */

declare(strict_types=1);

namespace ContactWorkflow\Admin;

use ContactWorkflow\Capabilities;
use ContactWorkflow\PostTypes\DepartmentPostType;
use ContactWorkflow\PostTypes\SubmissionPostType;

/**
 * Registers the standalone Contact admin menu.
 */
final class AdminMenu {
	public const SLUG = 'contact-workflow';

	/**
	 * Register Contact Workflow admin navigation.
	 */
	public function register(): void {
		add_menu_page(
			__('Contact', 'contact-workflow'),
			__('Contact', 'contact-workflow'),
			Capabilities::VIEW_SUBMISSIONS,
			self::SLUG,
			array($this, 'render'),
			'dashicons-email-alt2',
			27
		);

		remove_submenu_page(self::SLUG, self::SLUG);
		remove_submenu_page(self::SLUG, 'edit.php?post_type=' . SubmissionPostType::KEY);
		remove_submenu_page(self::SLUG, 'edit.php?post_type=' . DepartmentPostType::KEY);

		add_submenu_page(
			self::SLUG,
			__('Submissions', 'contact-workflow'),
			__('Submissions', 'contact-workflow'),
			Capabilities::VIEW_SUBMISSIONS,
			'edit.php?post_type=' . SubmissionPostType::KEY
		);

		add_submenu_page(
			self::SLUG,
			__('Departments', 'contact-workflow'),
			__('Departments', 'contact-workflow'),
			Capabilities::MANAGE_DEPARTMENTS,
			'edit.php?post_type=' . DepartmentPostType::KEY
		);
	}

	/**
	 * Render fallback parent menu content.
	 */
	public function render(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e('Contact', 'contact-workflow'); ?></h1>
			<p><?php esc_html_e('Manage contact submissions and departments from the menu items below.', 'contact-workflow'); ?></p>
		</div>
		<?php
	}
}
