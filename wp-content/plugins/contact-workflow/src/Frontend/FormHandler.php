<?php
/**
 * Public contact form request handling.
 *
 * @package ContactWorkflow
 */

declare(strict_types=1);

namespace ContactWorkflow\Frontend;

use ContactWorkflow\Admin\DepartmentFields;
use ContactWorkflow\Data\SubmissionFields;
use ContactWorkflow\PostTypes\DepartmentPostType;
use ContactWorkflow\PostTypes\SubmissionPostType;
use WP_Post;

/**
 * Processes public Contact Workflow form submissions.
 */
final class FormHandler {
	public const ACTION_FIELD = 'contact_workflow_form_action';
	public const ACTION_VALUE = 'submit_contact_workflow_form';
	public const NONCE_ACTION = 'contact_workflow_form';
	public const NONCE_FIELD = 'contact_workflow_form_nonce';
	public const SUCCESS_QUERY_ARG = 'contact_workflow_success';
	public const RETURN_POST_ID_FIELD = 'contact_workflow_return_post_id';

	public const FIELD_NAME = 'contact_workflow_name';
	public const FIELD_EMAIL = 'contact_workflow_email';
	public const FIELD_PHONE = 'contact_workflow_phone';
	public const FIELD_DEPARTMENT = 'contact_workflow_department';
	public const FIELD_SUBJECT = 'contact_workflow_subject';
	public const FIELD_MESSAGE = 'contact_workflow_message';

	/**
	 * Sanitized values from the current failed request.
	 *
	 * @var array<string, string>
	 */
	private array $values = array();

	/**
	 * Validation errors from the current failed request.
	 *
	 * @var array<string, string>
	 */
	private array $errors = array();

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_action('template_redirect', array($this, 'handle_request'));
	}

	/**
	 * Process a public form POST request.
	 */
	public function handle_request(): void {
		if (! $this->is_form_post()) {
			return;
		}

		$result = $this->validate_request();

		if (array() !== $result['errors']) {
			$this->values = $result['values'];
			$this->errors = $result['errors'];
			return;
		}

		$submission_id = $this->create_submission($result['values'], $result['department']);

		if ($submission_id <= 0) {
			$this->values = $result['values'];
			$this->errors = array(
				'general' => __('Your message could not be saved. Please try again.', 'contact-workflow'),
			);
			return;
		}

		wp_safe_redirect($this->get_success_redirect_url());
		exit;
	}

	/**
	 * Get current sanitized field values.
	 *
	 * @return array<string, string>
	 */
	public function get_values(): array {
		return $this->values;
	}

	/**
	 * Get current validation errors.
	 *
	 * @return array<string, string>
	 */
	public function get_errors(): array {
		return $this->errors;
	}

	/**
	 * Check whether the current request is a Contact Workflow form POST.
	 */
	private function is_form_post(): bool {
		if ('POST' !== strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? ''))) {
			return false;
		}

		$action = isset($_POST[self::ACTION_FIELD]) && is_scalar($_POST[self::ACTION_FIELD])
			? sanitize_key(wp_unslash($_POST[self::ACTION_FIELD]))
			: '';

		return self::ACTION_VALUE === $action;
	}

	/**
	 * Validate and sanitize the current form request.
	 *
	 * @return array{values: array<string, string>, errors: array<string, string>, department: WP_Post|null}
	 */
	private function validate_request(): array {
		$values = $this->sanitize_request_values();
		$errors = array();
		$department = null;

		if (! isset($_POST[self::NONCE_FIELD]) || ! is_string($_POST[self::NONCE_FIELD]) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_FIELD])), self::NONCE_ACTION)) {
			$errors['general'] = __('The form could not be verified. Please try again.', 'contact-workflow');
		}

		if ('' === $values['name']) {
			$errors['name'] = __('Please enter your name.', 'contact-workflow');
		}

		if ('' === $values['email'] || ! is_email($values['email'])) {
			$errors['email'] = __('Please enter a valid email address.', 'contact-workflow');
		}

		if ('' === $values['department']) {
			$errors['department'] = __('Please select a department.', 'contact-workflow');
		} else {
			$department = $this->get_valid_department(absint($values['department']));

			if (! $department instanceof WP_Post) {
				$errors['department'] = __('Please select an available department.', 'contact-workflow');
			}
		}

		if ('' === $values['subject']) {
			$errors['subject'] = __('Please enter a subject.', 'contact-workflow');
		}

		if ('' === $values['message']) {
			$errors['message'] = __('Please enter a message.', 'contact-workflow');
		}

		return array(
			'values'     => $values,
			'errors'     => $errors,
			'department' => $department,
		);
	}

	/**
	 * Sanitize submitted form values.
	 *
	 * @return array<string, string>
	 */
	private function sanitize_request_values(): array {
		$email = isset($_POST[self::FIELD_EMAIL]) && is_scalar($_POST[self::FIELD_EMAIL])
			? sanitize_email(strtolower(trim((string) wp_unslash($_POST[self::FIELD_EMAIL]))))
			: '';

		return array(
			'name'       => isset($_POST[self::FIELD_NAME]) && is_scalar($_POST[self::FIELD_NAME]) ? sanitize_text_field(wp_unslash($_POST[self::FIELD_NAME])) : '',
			'email'      => $email,
			'phone'      => isset($_POST[self::FIELD_PHONE]) && is_scalar($_POST[self::FIELD_PHONE]) ? sanitize_text_field(wp_unslash($_POST[self::FIELD_PHONE])) : '',
			'department' => isset($_POST[self::FIELD_DEPARTMENT]) && is_scalar($_POST[self::FIELD_DEPARTMENT]) ? (string) absint(wp_unslash($_POST[self::FIELD_DEPARTMENT])) : '',
			'subject'    => isset($_POST[self::FIELD_SUBJECT]) && is_scalar($_POST[self::FIELD_SUBJECT]) ? sanitize_text_field(wp_unslash($_POST[self::FIELD_SUBJECT])) : '',
			'message'    => isset($_POST[self::FIELD_MESSAGE]) && is_scalar($_POST[self::FIELD_MESSAGE]) ? sanitize_textarea_field(wp_unslash($_POST[self::FIELD_MESSAGE])) : '',
		);
	}

	/**
	 * Get a Department if it exists and is active.
	 *
	 * @param int $department_id Department post ID.
	 */
	private function get_valid_department(int $department_id): ?WP_Post {
		if ($department_id <= 0) {
			return null;
		}

		$post = get_post($department_id);

		if (! $post instanceof WP_Post || DepartmentPostType::KEY !== $post->post_type || 'publish' !== $post->post_status) {
			return null;
		}

		return $this->is_department_active($post->ID) ? $post : null;
	}

	/**
	 * Check Department active state using existing plugin semantics.
	 *
	 * @param int $department_id Department post ID.
	 */
	private function is_department_active(int $department_id): bool {
		$value = get_post_meta($department_id, DepartmentFields::META_ACTIVE, true);

		return '' === $value || filter_var($value, FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 * Create a Contact Submission.
	 *
	 * @param array<string, string> $values Sanitized values.
	 * @param WP_Post|null         $department Valid department.
	 */
	private function create_submission(array $values, ?WP_Post $department): int {
		if (! $department instanceof WP_Post) {
			return 0;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => SubmissionPostType::KEY,
				'post_status' => 'publish',
				'post_title'  => $this->build_submission_title($values),
			),
			true
		);

		if (is_wp_error($post_id) || ! is_int($post_id) || $post_id <= 0) {
			return 0;
		}

		update_post_meta($post_id, SubmissionFields::META_NAME, $values['name']);
		update_post_meta($post_id, SubmissionFields::META_EMAIL, $values['email']);
		update_post_meta($post_id, SubmissionFields::META_PHONE, $values['phone']);
		update_post_meta($post_id, SubmissionFields::META_DEPARTMENT_ID, $department->ID);
		update_post_meta($post_id, SubmissionFields::META_DEPARTMENT_NAME, get_the_title($department));
		update_post_meta($post_id, SubmissionFields::META_SUBJECT, $values['subject']);
		update_post_meta($post_id, SubmissionFields::META_MESSAGE, $values['message']);
		update_post_meta($post_id, SubmissionFields::META_STATUS, SubmissionFields::STATUS_NEW);

		return $post_id;
	}

	/**
	 * Build a generated administrative title matching admin behavior.
	 *
	 * @param array<string, string> $values Sanitized values.
	 */
	private function build_submission_title(array $values): string {
		$name = $values['name'];
		$subject = $values['subject'];

		if ('' !== $name && '' !== $subject) {
			return sprintf('%s — %s', $name, $subject);
		}

		if ('' !== $name) {
			return $name;
		}

		if ('' !== $subject) {
			return $subject;
		}

		return __('Contact Submission', 'contact-workflow');
	}

	/**
	 * Get success redirect URL for POST/Redirect/GET.
	 */
	private function get_success_redirect_url(): string {
		$fallback = home_url('/');
		$return_post_id = isset($_POST[self::RETURN_POST_ID_FIELD]) && is_scalar($_POST[self::RETURN_POST_ID_FIELD])
			? absint(wp_unslash($_POST[self::RETURN_POST_ID_FIELD]))
			: 0;
		$permalink = $return_post_id > 0 ? get_permalink($return_post_id) : false;
		$redirect_url = is_string($permalink) && '' !== $permalink ? $permalink : $fallback;
		$redirect_url = remove_query_arg(self::SUCCESS_QUERY_ARG, $redirect_url);

		return add_query_arg(self::SUCCESS_QUERY_ARG, '1', wp_validate_redirect($redirect_url, $fallback));
	}
}
