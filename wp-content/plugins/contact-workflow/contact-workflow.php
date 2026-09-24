<?php
/**
 * Plugin Name: Contact Workflow
 * Description: Reusable contact submissions and department workflow management for WordPress.
 * Version: 0.1.0
 * Requires PHP: 8.3
 * Author: Contact Workflow
 * Text Domain: contact-workflow
 *
 * @package ContactWorkflow
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

define('CONTACT_WORKFLOW_VERSION', '0.1.0');
define('CONTACT_WORKFLOW_FILE', __FILE__);
define('CONTACT_WORKFLOW_PATH', plugin_dir_path(__FILE__));

require_once CONTACT_WORKFLOW_PATH . 'src/Plugin.php';
require_once CONTACT_WORKFLOW_PATH . 'src/Capabilities.php';
require_once CONTACT_WORKFLOW_PATH . 'src/Admin/AdminMenu.php';
require_once CONTACT_WORKFLOW_PATH . 'src/Admin/SubmissionAdmin.php';
require_once CONTACT_WORKFLOW_PATH . 'src/Admin/DepartmentFields.php';
require_once CONTACT_WORKFLOW_PATH . 'src/Admin/DepartmentDeletionGuard.php';
require_once CONTACT_WORKFLOW_PATH . 'src/Data/SubmissionFields.php';
require_once CONTACT_WORKFLOW_PATH . 'src/Frontend/FormHandler.php';
require_once CONTACT_WORKFLOW_PATH . 'src/Frontend/FormShortcode.php';
require_once CONTACT_WORKFLOW_PATH . 'src/PostTypes/SubmissionPostType.php';
require_once CONTACT_WORKFLOW_PATH . 'src/PostTypes/DepartmentPostType.php';

register_activation_hook(
	CONTACT_WORKFLOW_FILE,
	static function (): void {
		$plugin = new ContactWorkflow\Plugin();
		$plugin->register_content_types();
		$plugin->grant_capabilities();

		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	CONTACT_WORKFLOW_FILE,
	static function (): void {
		flush_rewrite_rules();
	}
);

add_action(
	'init',
	static function (): void {
		$plugin = new ContactWorkflow\Plugin();
		$plugin->register_content_types();
	}
);

add_action(
	'plugins_loaded',
	static function (): void {
		$plugin = new ContactWorkflow\Plugin();
		$plugin->register_hooks();
	}
);
