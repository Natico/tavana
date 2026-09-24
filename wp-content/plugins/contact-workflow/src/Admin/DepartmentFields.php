<?php
/**
 * Contact department fields.
 *
 * @package ContactWorkflow
 */

declare(strict_types=1);

namespace ContactWorkflow\Admin;

use ContactWorkflow\Capabilities;
use ContactWorkflow\PostTypes\DepartmentPostType;
use WP_Post;

/**
 * Registers and manages Contact Department configuration fields.
 */
final class DepartmentFields {
	public const META_EMAILS = '_cw_department_emails';
	public const META_ACTIVE = '_cw_department_active';
	public const META_ORDER = '_cw_department_order';

	private const NONCE_ACTION = 'contact_workflow_department_fields';
	private const NONCE_NAME = 'contact_workflow_department_fields_nonce';
	private const FIELD_EMAILS = 'cw_department_emails';
	private const FIELD_ACTIVE = 'cw_department_active';
	private const FIELD_ORDER = 'cw_department_order';
	private const ERROR_ARG = 'contact_workflow_department_error';

	/**
	 * Email validation error for the current save request.
	 */
	private string $email_error = '';

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_action('init', array($this, 'register_meta'));
		add_action('add_meta_boxes_' . DepartmentPostType::KEY, array($this, 'add_meta_box'));
		add_action('save_post_' . DepartmentPostType::KEY, array($this, 'save'), 10, 2);
		add_filter('redirect_post_location', array($this, 'add_error_to_redirect'));
		add_action('admin_notices', array($this, 'render_admin_notice'));
	}

	/**
	 * Register Contact Department post meta.
	 */
	public function register_meta(): void {
		register_post_meta(
			DepartmentPostType::KEY,
			self::META_EMAILS,
			array(
				'type'              => 'array',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => array($this, 'sanitize_email_list'),
				'auth_callback'     => array($this, 'can_manage_department_meta'),
			)
		);

		register_post_meta(
			DepartmentPostType::KEY,
			self::META_ACTIVE,
			array(
				'type'              => 'boolean',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => array($this, 'sanitize_active'),
				'auth_callback'     => array($this, 'can_manage_department_meta'),
			)
		);

		register_post_meta(
			DepartmentPostType::KEY,
			self::META_ORDER,
			array(
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => 'absint',
				'auth_callback'     => array($this, 'can_manage_department_meta'),
			)
		);
	}

	/**
	 * Add the Department Configuration meta box.
	 */
	public function add_meta_box(): void {
		add_meta_box(
			'contact-workflow-department-fields',
			__('Department Configuration', 'contact-workflow'),
			array($this, 'render_meta_box'),
			DepartmentPostType::KEY,
			'normal',
			'high'
		);
	}

	/**
	 * Render the Department Configuration meta box.
	 *
	 * @param WP_Post $post Current department post.
	 */
	public function render_meta_box(WP_Post $post): void {
		wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

		$emails = $this->get_email_list($post->ID);
		$active = $this->is_active($post->ID);
		$order = $this->get_order($post->ID);
		?>
		<p>
			<label for="contact-workflow-department-emails"><?php esc_html_e('Recipient Emails', 'contact-workflow'); ?> <span aria-hidden="true">*</span></label>
			<textarea id="contact-workflow-department-emails" class="widefat" name="<?php echo esc_attr(self::FIELD_EMAILS); ?>" rows="5"><?php echo esc_textarea(implode("\n", $emails)); ?></textarea>
			<span class="description"><?php esc_html_e('Enter one recipient email per line.', 'contact-workflow'); ?></span>
		</p>

		<p>
			<label>
				<input type="checkbox" name="<?php echo esc_attr(self::FIELD_ACTIVE); ?>" value="1" <?php checked($active); ?>>
				<?php esc_html_e('Active', 'contact-workflow'); ?>
			</label>
		</p>

		<p>
			<label for="contact-workflow-department-order"><?php esc_html_e('Manual Order', 'contact-workflow'); ?></label>
			<input id="contact-workflow-department-order" class="small-text" type="number" min="0" step="1" name="<?php echo esc_attr(self::FIELD_ORDER); ?>" value="<?php echo esc_attr((string) $order); ?>">
		</p>
		<?php
	}

	/**
	 * Save Contact Department fields.
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post Current post object.
	 */
	public function save(int $post_id, WP_Post $post): void {
		if (! $this->can_save($post_id, $post)) {
			return;
		}

		$email_result = $this->parse_email_input(
			isset($_POST[self::FIELD_EMAILS]) && is_string($_POST[self::FIELD_EMAILS])
				? wp_unslash($_POST[self::FIELD_EMAILS])
				: ''
		);

		if ('' === $email_result['error']) {
			update_post_meta($post_id, self::META_EMAILS, $email_result['emails']);
		} else {
			$this->email_error = $email_result['error'];
		}

		$active = isset($_POST[self::FIELD_ACTIVE]) && '1' === (string) wp_unslash($_POST[self::FIELD_ACTIVE]) ? '1' : '0';
		update_post_meta($post_id, self::META_ACTIVE, $active);

		$order = isset($_POST[self::FIELD_ORDER]) && is_scalar($_POST[self::FIELD_ORDER])
			? absint(wp_unslash($_POST[self::FIELD_ORDER]))
			: 0;
		update_post_meta($post_id, self::META_ORDER, $order);
	}

	/**
	 * Add validation error state to the post redirect URL.
	 *
	 * @param string $location Redirect location.
	 */
	public function add_error_to_redirect(string $location): string {
		if ('' === $this->email_error) {
			return $location;
		}

		return add_query_arg(self::ERROR_ARG, rawurlencode($this->email_error), $location);
	}

	/**
	 * Render department validation notices.
	 */
	public function render_admin_notice(): void {
		if (! isset($_GET[self::ERROR_ARG]) || ! is_string($_GET[self::ERROR_ARG])) {
			return;
		}

		$screen = get_current_screen();

		if (null === $screen || DepartmentPostType::KEY !== $screen->post_type) {
			return;
		}

		$error = sanitize_key(wp_unslash($_GET[self::ERROR_ARG]));
		$message = 'required' === $error
			? __('Contact Department was saved, but recipient emails were not updated because at least one valid email is required.', 'contact-workflow')
			: __('Contact Department was saved, but recipient emails were not updated because one or more addresses were invalid.', 'contact-workflow');
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php echo esc_html($message); ?></p>
		</div>
		<?php
	}

	/**
	 * Sanitize a recipient email list.
	 *
	 * @param mixed $value Raw value.
	 * @return string[]
	 */
	public function sanitize_email_list($value): array {
		if (! is_array($value)) {
			return array();
		}

		$emails = array();

		foreach ($value as $email) {
			if (! is_scalar($email)) {
				continue;
			}

			$normalized = sanitize_email(strtolower(trim((string) $email)));

			if ('' !== $normalized && is_email($normalized)) {
				$emails[] = $normalized;
			}
		}

		return array_values(array_unique($emails));
	}

	/**
	 * Sanitize active state.
	 *
	 * @param mixed $value Raw value.
	 */
	public function sanitize_active($value): string {
		return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
	}

	/**
	 * Authorize Contact Department meta edits.
	 *
	 * @param mixed  $allowed Existing authorization result.
	 * @param string $meta_key Meta key.
	 * @param int    $post_id Post ID.
	 * @param int    $user_id User ID.
	 */
	public function can_manage_department_meta($allowed, string $meta_key, int $post_id, int $user_id): bool {
		unset($allowed, $meta_key, $post_id);

		return user_can($user_id, Capabilities::MANAGE_DEPARTMENTS);
	}

	/**
	 * Parse submitted recipient email lines.
	 *
	 * @param string $raw_value Raw textarea value.
	 * @return array{emails: string[], error: string}
	 */
	private function parse_email_input(string $raw_value): array {
		$lines = preg_split('/\r\n|\r|\n/', $raw_value);
		$emails = array();
		$has_invalid = false;

		foreach (false === $lines ? array() : $lines as $line) {
			$value = strtolower(trim((string) $line));

			if ('' === $value) {
				continue;
			}

			$normalized = sanitize_email($value);

			if ('' === $normalized || ! is_email($normalized) || $normalized !== $value) {
				$has_invalid = true;
				continue;
			}

			$emails[] = $normalized;
		}

		$emails = array_values(array_unique($emails));

		if ($has_invalid) {
			return array(
				'emails' => array(),
				'error'  => 'invalid',
			);
		}

		if (array() === $emails) {
			return array(
				'emails' => array(),
				'error'  => 'required',
			);
		}

		return array(
			'emails' => $emails,
			'error'  => '',
		);
	}

	/**
	 * Get recipient emails.
	 *
	 * @param int $post_id Department post ID.
	 * @return string[]
	 */
	private function get_email_list(int $post_id): array {
		$emails = get_post_meta($post_id, self::META_EMAILS, true);

		return is_array($emails) ? $this->sanitize_email_list($emails) : array();
	}

	/**
	 * Get active state.
	 *
	 * @param int $post_id Department post ID.
	 */
	private function is_active(int $post_id): bool {
		$value = get_post_meta($post_id, self::META_ACTIVE, true);

		return '' === $value ? true : '1' === $this->sanitize_active($value);
	}

	/**
	 * Get manual order.
	 *
	 * @param int $post_id Department post ID.
	 */
	private function get_order(int $post_id): int {
		$value = get_post_meta($post_id, self::META_ORDER, true);

		return is_numeric($value) ? absint($value) : 0;
	}

	/**
	 * Check whether department fields can be saved.
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post Current post object.
	 */
	private function can_save(int $post_id, WP_Post $post): bool {
		if (DepartmentPostType::KEY !== $post->post_type) {
			return false;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return false;
		}

		if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
			return false;
		}

		if (! isset($_POST[self::NONCE_NAME]) || ! is_string($_POST[self::NONCE_NAME])) {
			return false;
		}

		if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
			return false;
		}

		return current_user_can(Capabilities::MANAGE_DEPARTMENTS);
	}
}
