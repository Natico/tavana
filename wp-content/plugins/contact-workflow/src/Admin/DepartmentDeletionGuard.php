<?php
/**
 * Contact department deletion protection.
 *
 * @package ContactWorkflow
 */

declare(strict_types=1);

namespace ContactWorkflow\Admin;

use ContactWorkflow\Capabilities;
use ContactWorkflow\Data\SubmissionFields;
use ContactWorkflow\PostTypes\DepartmentPostType;
use ContactWorkflow\PostTypes\SubmissionPostType;
use WP_Post;

/**
 * Prevents deleting departments referenced by contact submissions.
 */
final class DepartmentDeletionGuard {
	private const NOTICE_TRANSIENT_PREFIX = 'contact_workflow_department_delete_blocked_';

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_action('admin_action_trash', array($this, 'redirect_referenced_department_action'));
		add_action('admin_action_delete', array($this, 'redirect_referenced_department_action'));
		add_filter('pre_trash_post', array($this, 'block_trash_when_referenced'), 10, 2);
		add_filter('pre_delete_post', array($this, 'block_delete_when_referenced'), 10, 3);
		add_action('admin_notices', array($this, 'render_admin_notice'));
	}

	/**
	 * Redirect expected blocked delete/trash actions back to the Department list.
	 */
	public function redirect_referenced_department_action(): void {
		$post_id = isset($_GET['post']) && is_scalar($_GET['post']) ? absint(wp_unslash($_GET['post'])) : 0;

		if ($post_id <= 0) {
			return;
		}

		$post = get_post($post_id);

		if (! $post instanceof WP_Post || ! $this->should_block($post)) {
			return;
		}

		$action = isset($_GET['action']) && is_scalar($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';

		if ('trash' === $action) {
			check_admin_referer('trash-post_' . $post_id);
		} elseif ('delete' === $action) {
			check_admin_referer('delete-post_' . $post_id);
		} else {
			return;
		}

		$this->queue_notice();

		wp_safe_redirect(admin_url('edit.php?post_type=' . DepartmentPostType::KEY));
		exit;
	}

	/**
	 * Block trashing a referenced Contact Department.
	 *
	 * @param bool|null $trash Whether to go forward with trashing.
	 * @param WP_Post   $post Post object.
	 * @return bool|null
	 */
	public function block_trash_when_referenced($trash, WP_Post $post) {
		if (! $this->should_block($post)) {
			return $trash;
		}

		$this->queue_notice();

		return false;
	}

	/**
	 * Block permanently deleting a referenced Contact Department.
	 *
	 * @param bool|null $delete Whether to go forward with deletion.
	 * @param WP_Post   $post Post object.
	 * @param bool      $force_delete Whether this is a forced deletion.
	 * @return bool|null
	 */
	public function block_delete_when_referenced($delete, WP_Post $post, bool $force_delete) {
		unset($force_delete);

		if (! $this->should_block($post)) {
			return $delete;
		}

		$this->queue_notice();

		return false;
	}

	/**
	 * Render the deletion-blocked notice.
	 */
	public function render_admin_notice(): void {
		$user_id = get_current_user_id();

		if ($user_id <= 0) {
			return;
		}

		$transient_key = self::NOTICE_TRANSIENT_PREFIX . $user_id;

		if (! get_transient($transient_key)) {
			return;
		}

		delete_transient($transient_key);
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e('This department cannot be deleted because it is referenced by one or more contact submissions. Mark the department inactive instead.', 'contact-workflow'); ?></p>
		</div>
		<?php
	}

	/**
	 * Check whether the operation should be blocked.
	 *
	 * @param WP_Post $post Post object.
	 */
	private function should_block(WP_Post $post): bool {
		if (DepartmentPostType::KEY !== $post->post_type) {
			return false;
		}

		if (! current_user_can(Capabilities::MANAGE_DEPARTMENTS)) {
			return false;
		}

		return $this->has_referencing_submission($post->ID);
	}

	/**
	 * Check whether any Contact Submission references this Department.
	 *
	 * @param int $department_id Department post ID.
	 */
	private function has_referencing_submission(int $department_id): bool {
		$posts = get_posts(
			array(
				'post_type'      => SubmissionPostType::KEY,
				'post_status'    => array_keys(get_post_stati()),
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'   => SubmissionFields::META_DEPARTMENT_ID,
						'value' => $department_id,
						'type'  => 'NUMERIC',
					),
				),
			)
		);

		return array() !== $posts;
	}

	/**
	 * Queue the admin notice for the current user.
	 */
	private function queue_notice(): void {
		$user_id = get_current_user_id();

		if ($user_id <= 0) {
			return;
		}

		set_transient(self::NOTICE_TRANSIENT_PREFIX . $user_id, true, MINUTE_IN_SECONDS);
	}
}
