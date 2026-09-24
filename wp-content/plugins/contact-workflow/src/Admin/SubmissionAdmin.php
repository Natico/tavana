<?php
/**
 * Contact submission admin UI.
 *
 * @package ContactWorkflow
 */

declare(strict_types=1);

namespace ContactWorkflow\Admin;

use ContactWorkflow\Capabilities;
use ContactWorkflow\Data\SubmissionFields;
use ContactWorkflow\PostTypes\SubmissionPostType;
use WP_Post;
use WP_Query;

/**
 * Manages the Contact Submission inbox and detail screen.
 */
final class SubmissionAdmin {
	private const NONCE_ACTION = 'contact_workflow_submission_status';
	private const NONCE_NAME = 'contact_workflow_submission_status_nonce';
	private const STATUS_FIELD = 'cw_contact_status';

	/**
	 * Whether the generated title is currently being updated.
	 */
	private bool $updating_title = false;

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_filter('use_block_editor_for_post_type', array($this, 'disable_block_editor'), 10, 2);
		add_action('admin_init', array($this, 'remove_editorial_supports'));
		add_action('add_meta_boxes_' . SubmissionPostType::KEY, array($this, 'add_meta_boxes'));
		add_action('save_post_' . SubmissionPostType::KEY, array($this, 'save_status'), 10, 2);
		add_action('save_post_' . SubmissionPostType::KEY, array($this, 'refresh_generated_title'), 20, 2);
		add_filter('manage_' . SubmissionPostType::KEY . '_posts_columns', array($this, 'filter_columns'));
		add_action('manage_' . SubmissionPostType::KEY . '_posts_custom_column', array($this, 'render_column'), 10, 2);
		add_action('restrict_manage_posts', array($this, 'render_status_filter'));
		add_action('pre_get_posts', array($this, 'filter_admin_query_by_status'));
	}

	/**
	 * Disable the block editor for contact submissions only.
	 *
	 * @param bool   $use_block_editor Whether to use the block editor.
	 * @param string $post_type Post type key.
	 */
	public function disable_block_editor(bool $use_block_editor, string $post_type): bool {
		if (SubmissionPostType::KEY !== $post_type) {
			return $use_block_editor;
		}

		return false;
	}

	/**
	 * Remove editorial supports from contact submissions.
	 */
	public function remove_editorial_supports(): void {
		foreach (array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields') as $support) {
			remove_post_type_support(SubmissionPostType::KEY, $support);
		}
	}

	/**
	 * Register Contact Submission detail meta boxes.
	 */
	public function add_meta_boxes(): void {
		remove_meta_box('submitdiv', SubmissionPostType::KEY, 'side');
		remove_meta_box('slugdiv', SubmissionPostType::KEY, 'normal');

		add_meta_box(
			'contact-workflow-submission-details',
			__('Submission Details', 'contact-workflow'),
			array($this, 'render_details_meta_box'),
			SubmissionPostType::KEY,
			'normal',
			'high'
		);

		add_meta_box(
			'contact-workflow-submission-status',
			__('Contact Status', 'contact-workflow'),
			array($this, 'render_status_meta_box'),
			SubmissionPostType::KEY,
			'side',
			'high'
		);
	}

	/**
	 * Render the read-only submission detail box.
	 *
	 * @param WP_Post $post Current submission post.
	 */
	public function render_details_meta_box(WP_Post $post): void {
		$rows = array(
			__('Name', 'contact-workflow')         => $this->get_string_meta($post->ID, SubmissionFields::META_NAME),
			__('Email', 'contact-workflow')        => $this->get_string_meta($post->ID, SubmissionFields::META_EMAIL),
			__('Phone', 'contact-workflow')        => $this->get_string_meta($post->ID, SubmissionFields::META_PHONE),
			__('Department', 'contact-workflow')   => $this->get_string_meta($post->ID, SubmissionFields::META_DEPARTMENT_NAME),
			__('Subject', 'contact-workflow')      => $this->get_string_meta($post->ID, SubmissionFields::META_SUBJECT),
			__('Created Date', 'contact-workflow') => get_the_date('', $post),
			__('Created Time', 'contact-workflow') => get_the_time('', $post),
		);
		?>
		<table class="widefat striped">
			<tbody>
				<?php foreach ($rows as $label => $value) : ?>
					<tr>
						<th scope="row"><?php echo esc_html($label); ?></th>
						<td><?php echo esc_html('' !== $value ? $value : __('Not provided', 'contact-workflow')); ?></td>
					</tr>
				<?php endforeach; ?>
				<tr>
					<th scope="row"><?php esc_html_e('Message', 'contact-workflow'); ?></th>
					<td><?php echo nl2br(esc_html($this->get_string_meta($post->ID, SubmissionFields::META_MESSAGE))); ?></td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render the editable status box.
	 *
	 * @param WP_Post $post Current submission post.
	 */
	public function render_status_meta_box(WP_Post $post): void {
		wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

		$status = SubmissionFields::normalize_status(get_post_meta($post->ID, SubmissionFields::META_STATUS, true));
		$can_edit_status = current_user_can(Capabilities::EDIT_SUBMISSION_STATUS);
		?>
		<p>
			<label for="contact-workflow-submission-status"><?php esc_html_e('Status', 'contact-workflow'); ?></label>
			<select id="contact-workflow-submission-status" class="widefat" name="<?php echo esc_attr(self::STATUS_FIELD); ?>" <?php disabled(! $can_edit_status); ?>>
				<?php foreach (SubmissionFields::get_status_labels() as $status_key => $label) : ?>
					<option value="<?php echo esc_attr($status_key); ?>" <?php selected($status, $status_key); ?>>
						<?php echo esc_html($label); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php $button_attributes = $can_edit_status ? array() : array('disabled' => 'disabled'); ?>
		<div id="major-publishing-actions">
			<div id="publishing-action">
				<?php submit_button(__('Update', 'contact-workflow'), 'primary', 'save', false, $button_attributes); ?>
			</div>
			<div class="clear"></div>
		</div>
		<?php
	}

	/**
	 * Save the explicit Contact Status selection.
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post Current post object.
	 */
	public function save_status(int $post_id, WP_Post $post): void {
		if (! $this->can_save_status($post_id, $post)) {
			return;
		}

		$status = SubmissionFields::normalize_status(wp_unslash($_POST[self::STATUS_FIELD]));
		update_post_meta($post_id, SubmissionFields::META_STATUS, $status);
	}

	/**
	 * Refresh the generated administrative title.
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post Current post object.
	 */
	public function refresh_generated_title(int $post_id, WP_Post $post): void {
		if ($this->updating_title || SubmissionPostType::KEY !== $post->post_type) {
			return;
		}

		if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
			return;
		}

		$title = $this->build_generated_title($post_id);

		if ($title === $post->post_title) {
			return;
		}

		$this->updating_title = true;
		wp_update_post(
			array(
				'ID'         => $post_id,
				'post_title' => $title,
			)
		);
		$this->updating_title = false;
	}

	/**
	 * Replace the default list table columns.
	 *
	 * @param array<string, string> $columns Existing columns.
	 * @return array<string, string>
	 */
	public function filter_columns(array $columns): array {
		unset($columns);

		return array(
			'cb'                 => '<input type="checkbox" />',
			'cw_contact_name'    => __('Name', 'contact-workflow'),
			'cw_contact_email'   => __('Email', 'contact-workflow'),
			'cw_contact_dept'    => __('Department', 'contact-workflow'),
			'cw_contact_subject' => __('Subject', 'contact-workflow'),
			'cw_contact_status'  => __('Status', 'contact-workflow'),
			'date'               => __('Date', 'contact-workflow'),
		);
	}

	/**
	 * Render custom list table columns.
	 *
	 * @param string $column_name Column key.
	 * @param int    $post_id Current post ID.
	 */
	public function render_column(string $column_name, int $post_id): void {
		if ('cw_contact_status' === $column_name) {
			$status = SubmissionFields::normalize_status(get_post_meta($post_id, SubmissionFields::META_STATUS, true));
			$labels = SubmissionFields::get_status_labels();
			echo esc_html($labels[$status]);
			return;
		}

		$meta_map = array(
			'cw_contact_name'    => SubmissionFields::META_NAME,
			'cw_contact_email'   => SubmissionFields::META_EMAIL,
			'cw_contact_dept'    => SubmissionFields::META_DEPARTMENT_NAME,
			'cw_contact_subject' => SubmissionFields::META_SUBJECT,
		);

		if (! array_key_exists($column_name, $meta_map)) {
			return;
		}

		$value = $this->get_string_meta($post_id, $meta_map[$column_name]);

		if ('cw_contact_name' === $column_name) {
			$edit_link = get_edit_post_link($post_id);

			if (is_string($edit_link) && '' !== $edit_link) {
				printf(
					'<strong><a class="row-title" href="%s">%s</a></strong>',
					esc_url($edit_link),
					esc_html('' !== $value ? $value : __('Contact Submission', 'contact-workflow'))
				);
				return;
			}
		}

		echo esc_html('' !== $value ? $value : __('Not provided', 'contact-workflow'));
	}

	/**
	 * Render the Contact Status filter.
	 */
	public function render_status_filter(): void {
		global $typenow;

		if (SubmissionPostType::KEY !== $typenow) {
			return;
		}

		$selected = isset($_GET[self::STATUS_FIELD]) && is_string($_GET[self::STATUS_FIELD])
			? SubmissionFields::normalize_status(wp_unslash($_GET[self::STATUS_FIELD]))
			: '';
		?>
		<label class="screen-reader-text" for="filter-by-contact-status"><?php esc_html_e('Filter by contact status', 'contact-workflow'); ?></label>
		<select id="filter-by-contact-status" name="<?php echo esc_attr(self::STATUS_FIELD); ?>">
			<option value=""><?php esc_html_e('All statuses', 'contact-workflow'); ?></option>
			<?php foreach (SubmissionFields::get_status_labels() as $status_key => $label) : ?>
				<option value="<?php echo esc_attr($status_key); ?>" <?php selected($selected, $status_key); ?>>
					<?php echo esc_html($label); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Apply the Contact Status list filter.
	 *
	 * @param WP_Query $query Current query.
	 */
	public function filter_admin_query_by_status(WP_Query $query): void {
		if (! is_admin() || ! $query->is_main_query()) {
			return;
		}

		if (SubmissionPostType::KEY !== $query->get('post_type')) {
			return;
		}

		if (! isset($_GET[self::STATUS_FIELD]) || ! is_string($_GET[self::STATUS_FIELD])) {
			return;
		}

		$raw_status = sanitize_key(wp_unslash($_GET[self::STATUS_FIELD]));

		if ('' === $raw_status || ! array_key_exists($raw_status, SubmissionFields::get_status_labels())) {
			return;
		}

		$query->set(
			'meta_query',
			array(
				array(
					'key'   => SubmissionFields::META_STATUS,
					'value' => $raw_status,
				),
			)
		);
	}

	/**
	 * Get meta as a string.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Meta key.
	 */
	private function get_string_meta(int $post_id, string $meta_key): string {
		$value = get_post_meta($post_id, $meta_key, true);

		if (is_int($value)) {
			return (string) $value;
		}

		return is_string($value) ? $value : '';
	}

	/**
	 * Build the generated administrative post title.
	 *
	 * @param int $post_id Submission post ID.
	 */
	private function build_generated_title(int $post_id): string {
		$name = $this->get_string_meta($post_id, SubmissionFields::META_NAME);
		$subject = $this->get_string_meta($post_id, SubmissionFields::META_SUBJECT);

		if ('' !== $name && '' !== $subject) {
			return sprintf('%s — %s', $name, $subject);
		}

		if ('' !== $name) {
			return $name;
		}

		if ('' !== $subject) {
			return $subject;
		}

		return sprintf(__('Contact Submission #%d', 'contact-workflow'), $post_id);
	}

	/**
	 * Check whether status can be saved.
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post Current post object.
	 */
	private function can_save_status(int $post_id, WP_Post $post): bool {
		if (SubmissionPostType::KEY !== $post->post_type) {
			return false;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return false;
		}

		if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
			return false;
		}

		if (! isset($_POST[self::STATUS_FIELD]) || ! is_string($_POST[self::STATUS_FIELD])) {
			return false;
		}

		if (! isset($_POST[self::NONCE_NAME]) || ! is_string($_POST[self::NONCE_NAME])) {
			return false;
		}

		if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
			return false;
		}

		return current_user_can(Capabilities::EDIT_SUBMISSION_STATUS);
	}
}
