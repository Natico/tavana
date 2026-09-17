<?php
/**
 * Contact submission admin fields.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Contact;

use ArtCms\PostTypes\ContactSubmissionPostType;
use WP_Post;

/**
 * Adds admin-only fields for stored contact submissions.
 */
final class ContactSubmissionFields {
	public const META_NAME = '_art_contact_name';
	public const META_EMAIL = '_art_contact_email';
	public const META_PHONE = '_art_contact_phone';
	public const META_MESSAGE = '_art_contact_message';

	private const NONCE_ACTION = 'art_contact_submission_fields';
	private const NONCE_NAME = 'art_contact_submission_fields_nonce';

	/**
	 * Register field hooks.
	 */
	public function register_hooks(): void {
		add_action('add_meta_boxes_' . ContactSubmissionPostType::KEY, array($this, 'add_meta_boxes'), 10, 0);
		add_action('save_post_' . ContactSubmissionPostType::KEY, array($this, 'save'), 10, 2);
	}

	/**
	 * Register the submission details meta box.
	 */
	public function add_meta_boxes(): void {
		add_meta_box(
			'art-contact-submission-details',
			__('Submission Details', 'art-cms'),
			array($this, 'render_meta_box'),
			ContactSubmissionPostType::KEY,
			'normal',
			'high'
		);
	}

	/**
	 * Render the submission details meta box.
	 *
	 * @param WP_Post $post Current post.
	 */
	public function render_meta_box(WP_Post $post): void {
		wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

		$name = $this->get_meta_value($post->ID, self::META_NAME);
		$email = $this->get_meta_value($post->ID, self::META_EMAIL);
		$phone = $this->get_meta_value($post->ID, self::META_PHONE);
		$message = $this->get_meta_value($post->ID, self::META_MESSAGE);
		?>
		<div class="art-cms-field-grid">
			<p>
				<label for="art-contact-name"><?php esc_html_e('Name', 'art-cms'); ?></label>
				<input id="art-contact-name" class="widefat" type="text" name="art_contact_name" value="<?php echo esc_attr($name); ?>">
			</p>

			<p>
				<label for="art-contact-email"><?php esc_html_e('Email', 'art-cms'); ?></label>
				<input id="art-contact-email" class="widefat" type="email" name="art_contact_email" value="<?php echo esc_attr($email); ?>">
			</p>

			<p>
				<label for="art-contact-phone"><?php esc_html_e('Phone', 'art-cms'); ?></label>
				<input id="art-contact-phone" class="widefat" type="text" name="art_contact_phone" value="<?php echo esc_attr($phone); ?>">
			</p>

			<p class="art-cms-field-grid__full">
				<label for="art-contact-message"><?php esc_html_e('Message', 'art-cms'); ?></label>
				<textarea id="art-contact-message" class="widefat" name="art_contact_message" rows="8"><?php echo esc_textarea($message); ?></textarea>
			</p>
		</div>
		<?php
	}

	/**
	 * Save submission fields.
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post Current post.
	 */
	public function save(int $post_id, WP_Post $post): void {
		if (! $this->can_save($post_id, $post)) {
			return;
		}

		$this->save_text_meta($post_id, self::META_NAME, 'art_contact_name');
		$this->save_email_meta($post_id, self::META_EMAIL, 'art_contact_email');
		$this->save_text_meta($post_id, self::META_PHONE, 'art_contact_phone');
		$this->save_textarea_meta($post_id, self::META_MESSAGE, 'art_contact_message');
	}

	/**
	 * Get a post meta value as a string.
	 *
	 * @param int    $post_id Current post ID.
	 * @param string $meta_key Meta key.
	 */
	private function get_meta_value(int $post_id, string $meta_key): string {
		$value = get_post_meta($post_id, $meta_key, true);

		return is_string($value) ? $value : '';
	}

	/**
	 * Check whether fields can be saved.
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post Current post.
	 */
	private function can_save(int $post_id, WP_Post $post): bool {
		if (ContactSubmissionPostType::KEY !== $post->post_type) {
			return false;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return false;
		}

		if (! isset($_POST[self::NONCE_NAME]) || ! is_string($_POST[self::NONCE_NAME])) {
			return false;
		}

		if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
			return false;
		}

		return current_user_can('edit_post', $post_id);
	}

	/**
	 * Save a text input meta field.
	 *
	 * @param int    $post_id Current post ID.
	 * @param string $meta_key Meta key.
	 * @param string $request_key Request key.
	 */
	private function save_text_meta(int $post_id, string $meta_key, string $request_key): void {
		$value = isset($_POST[$request_key]) && is_string($_POST[$request_key])
			? sanitize_text_field(wp_unslash($_POST[$request_key]))
			: '';

		$this->update_or_delete_meta($post_id, $meta_key, $value);
	}

	/**
	 * Save an email input meta field.
	 *
	 * @param int    $post_id Current post ID.
	 * @param string $meta_key Meta key.
	 * @param string $request_key Request key.
	 */
	private function save_email_meta(int $post_id, string $meta_key, string $request_key): void {
		$value = isset($_POST[$request_key]) && is_string($_POST[$request_key])
			? sanitize_email(wp_unslash($_POST[$request_key]))
			: '';

		$this->update_or_delete_meta($post_id, $meta_key, $value);
	}

	/**
	 * Save a textarea meta field.
	 *
	 * @param int    $post_id Current post ID.
	 * @param string $meta_key Meta key.
	 * @param string $request_key Request key.
	 */
	private function save_textarea_meta(int $post_id, string $meta_key, string $request_key): void {
		$value = isset($_POST[$request_key]) && is_string($_POST[$request_key])
			? sanitize_textarea_field(wp_unslash($_POST[$request_key]))
			: '';

		$this->update_or_delete_meta($post_id, $meta_key, $value);
	}

	/**
	 * Update a meta value or delete empty meta.
	 *
	 * @param int    $post_id Current post ID.
	 * @param string $meta_key Meta key.
	 * @param string $value Meta value.
	 */
	private function update_or_delete_meta(int $post_id, string $meta_key, string $value): void {
		if ('' === $value) {
			delete_post_meta($post_id, $meta_key);
			return;
		}

		update_post_meta($post_id, $meta_key, $value);
	}
}
