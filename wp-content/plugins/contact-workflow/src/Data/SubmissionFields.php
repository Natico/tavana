<?php
/**
 * Contact submission fields.
 *
 * @package ContactWorkflow
 */

declare(strict_types=1);

namespace ContactWorkflow\Data;

use ContactWorkflow\Capabilities;
use ContactWorkflow\PostTypes\SubmissionPostType;

/**
 * Registers Contact Submission structured data fields.
 */
final class SubmissionFields {
	public const META_NAME = '_cw_contact_name';
	public const META_EMAIL = '_cw_contact_email';
	public const META_PHONE = '_cw_contact_phone';
	public const META_DEPARTMENT_ID = '_cw_contact_department_id';
	public const META_DEPARTMENT_NAME = '_cw_contact_department_name';
	public const META_SUBJECT = '_cw_contact_subject';
	public const META_MESSAGE = '_cw_contact_message';
	public const META_STATUS = '_cw_contact_status';

	public const STATUS_NEW = 'new';
	public const STATUS_READ = 'read';
	public const STATUS_REPLIED = 'replied';
	public const STATUS_ARCHIVED = 'archived';

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_action('init', array($this, 'register_meta'));
	}

	/**
	 * Register Contact Submission post meta.
	 */
	public function register_meta(): void {
		$this->register_string_meta(self::META_NAME, array($this, 'sanitize_text_meta'));
		$this->register_string_meta(self::META_EMAIL, array($this, 'sanitize_email_meta'));
		$this->register_string_meta(self::META_PHONE, array($this, 'sanitize_text_meta'));
		$this->register_integer_meta(self::META_DEPARTMENT_ID);
		$this->register_string_meta(self::META_DEPARTMENT_NAME, array($this, 'sanitize_text_meta'));
		$this->register_string_meta(self::META_SUBJECT, array($this, 'sanitize_text_meta'));
		$this->register_string_meta(self::META_MESSAGE, array($this, 'sanitize_message_meta'));
		$this->register_string_meta(self::META_STATUS, array($this, 'sanitize_status_meta'));
	}

	/**
	 * Get allowed status labels.
	 *
	 * @return array<string, string>
	 */
	public static function get_status_labels(): array {
		return array(
			self::STATUS_NEW      => __('New', 'contact-workflow'),
			self::STATUS_READ     => __('Read', 'contact-workflow'),
			self::STATUS_REPLIED  => __('Replied', 'contact-workflow'),
			self::STATUS_ARCHIVED => __('Archived', 'contact-workflow'),
		);
	}

	/**
	 * Get the normalized status value.
	 *
	 * @param mixed $value Raw value.
	 */
	public static function normalize_status($value): string {
		$status = is_scalar($value) ? sanitize_key((string) $value) : '';

		return array_key_exists($status, self::get_status_labels()) ? $status : self::STATUS_NEW;
	}

	/**
	 * Sanitize text meta.
	 *
	 * @param mixed $value Raw value.
	 */
	public function sanitize_text_meta($value): string {
		return sanitize_text_field((string) $value);
	}

	/**
	 * Sanitize email meta.
	 *
	 * @param mixed $value Raw value.
	 */
	public function sanitize_email_meta($value): string {
		$email = sanitize_email(strtolower(trim((string) $value)));

		return is_email($email) ? $email : '';
	}

	/**
	 * Sanitize message meta while preserving line breaks.
	 *
	 * @param mixed $value Raw value.
	 */
	public function sanitize_message_meta($value): string {
		return sanitize_textarea_field((string) $value);
	}

	/**
	 * Sanitize status meta.
	 *
	 * @param mixed $value Raw value.
	 */
	public function sanitize_status_meta($value): string {
		return self::normalize_status($value);
	}

	/**
	 * Authorize editing Contact Submission meta.
	 *
	 * @param mixed  $allowed Existing authorization result.
	 * @param string $meta_key Meta key.
	 * @param int    $post_id Post ID.
	 * @param int    $user_id User ID.
	 */
	public function can_edit_meta($allowed, string $meta_key, int $post_id, int $user_id): bool {
		unset($allowed, $post_id);

		if (self::META_STATUS === $meta_key) {
			return user_can($user_id, Capabilities::EDIT_SUBMISSION_STATUS);
		}

		return user_can($user_id, Capabilities::VIEW_SUBMISSIONS);
	}

	/**
	 * Register a string meta field.
	 *
	 * @param string   $meta_key Meta key.
	 * @param callable $sanitize_callback Sanitize callback.
	 */
	private function register_string_meta(string $meta_key, callable $sanitize_callback): void {
		register_post_meta(
			SubmissionPostType::KEY,
			$meta_key,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => $sanitize_callback,
				'auth_callback'     => array($this, 'can_edit_meta'),
			)
		);
	}

	/**
	 * Register an integer meta field.
	 *
	 * @param string $meta_key Meta key.
	 */
	private function register_integer_meta(string $meta_key): void {
		register_post_meta(
			SubmissionPostType::KEY,
			$meta_key,
			array(
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => 'absint',
				'auth_callback'     => array($this, 'can_edit_meta'),
			)
		);
	}
}
