<?php
/**
 * Admin list table columns.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Admin;

use ArtCms\Contact\ContactSubmissionFields;
use ArtCms\PostTypes\ContactSubmissionPostType;
use ArtCms\PostTypes\GalleryItemPostType;
use ArtCms\PostTypes\ProductPostType;

/**
 * Improves admin list tables for project content.
 */
final class AdminColumns {
	private const THUMBNAIL_COLUMN = 'art_thumbnail';
	private const SUBMISSION_NAME_COLUMN = 'art_submission_name';
	private const SUBMISSION_EMAIL_COLUMN = 'art_submission_email';
	private const SUBMISSION_STATUS_COLUMN = 'art_submission_status';

	/**
	 * Register column hooks.
	 */
	public function register_hooks(): void {
		add_filter('manage_' . ProductPostType::KEY . '_posts_columns', array($this, 'add_thumbnail_column'));
		add_filter('manage_' . GalleryItemPostType::KEY . '_posts_columns', array($this, 'add_thumbnail_column'));

		add_action('manage_' . ProductPostType::KEY . '_posts_custom_column', array($this, 'render_thumbnail_column'), 10, 2);
		add_action('manage_' . GalleryItemPostType::KEY . '_posts_custom_column', array($this, 'render_thumbnail_column'), 10, 2);

		add_filter('manage_' . ContactSubmissionPostType::KEY . '_posts_columns', array($this, 'add_contact_submission_columns'));
		add_action('manage_' . ContactSubmissionPostType::KEY . '_posts_custom_column', array($this, 'render_contact_submission_column'), 10, 2);

		add_action('admin_head', array($this, 'render_admin_styles'));
	}

	/**
	 * Add a thumbnail column after the checkbox column.
	 *
	 * @param array<string, string> $columns Existing columns.
	 * @return array<string, string>
	 */
	public function add_thumbnail_column(array $columns): array {
		return $this->insert_after_checkbox(
			$columns,
			self::THUMBNAIL_COLUMN,
			__('Image', 'art-cms')
		);
	}

	/**
	 * Render the thumbnail column.
	 *
	 * @param string $column_name Current column name.
	 * @param int    $post_id Current post ID.
	 */
	public function render_thumbnail_column(string $column_name, int $post_id): void {
		if (self::THUMBNAIL_COLUMN !== $column_name) {
			return;
		}

		if (has_post_thumbnail($post_id)) {
			echo get_the_post_thumbnail($post_id, array(56, 56), array('class' => 'art-cms-admin-thumbnail'));
			return;
		}

		echo '<span class="art-cms-admin-muted">' . esc_html__('No image', 'art-cms') . '</span>';
	}

	/**
	 * Add contact submission admin columns.
	 *
	 * @param array<string, string> $columns Existing columns.
	 * @return array<string, string>
	 */
	public function add_contact_submission_columns(array $columns): array {
		$columns = $this->insert_after_checkbox(
			$columns,
			self::SUBMISSION_NAME_COLUMN,
			__('Name', 'art-cms')
		);

		$columns = $this->insert_after_column(
			$columns,
			self::SUBMISSION_NAME_COLUMN,
			self::SUBMISSION_EMAIL_COLUMN,
			__('Email', 'art-cms')
		);

		return $this->insert_after_column(
			$columns,
			self::SUBMISSION_EMAIL_COLUMN,
			self::SUBMISSION_STATUS_COLUMN,
			__('Status', 'art-cms')
		);
	}

	/**
	 * Render contact submission columns.
	 *
	 * @param string $column_name Current column name.
	 * @param int    $post_id Current post ID.
	 */
	public function render_contact_submission_column(string $column_name, int $post_id): void {
		if (self::SUBMISSION_NAME_COLUMN === $column_name) {
			$this->render_meta_value($post_id, ContactSubmissionFields::META_NAME);
			return;
		}

		if (self::SUBMISSION_EMAIL_COLUMN === $column_name) {
			$email = $this->get_meta_value($post_id, ContactSubmissionFields::META_EMAIL);

			if ('' === $email) {
				$this->render_empty_value();
				return;
			}

			printf(
				'<a href="%s">%s</a>',
				esc_url('mailto:' . $email),
				esc_html($email)
			);
			return;
		}

		if (self::SUBMISSION_STATUS_COLUMN === $column_name) {
			$this->render_post_status($post_id);
		}
	}

	/**
	 * Render post status label.
	 *
	 * @param int $post_id Current post ID.
	 */
	private function render_post_status(int $post_id): void {
		$post_status = get_post_status($post_id);

		if (! is_string($post_status) || '' === $post_status) {
			$this->render_empty_value(__('Unknown', 'art-cms'));
			return;
		}

		$status_object = get_post_status_object($post_status);
		$status_label = null === $status_object ? ucfirst($post_status) : $status_object->label;

		echo esc_html($status_label);
	}

	/**
	 * Render small admin-only styles for custom columns.
	 */
	public function render_admin_styles(): void {
		$screen = get_current_screen();

		if (
			null === $screen
			|| ! in_array($screen->post_type, array(ProductPostType::KEY, GalleryItemPostType::KEY, ContactSubmissionPostType::KEY), true)
		) {
			return;
		}
		?>
		<style>
			.fixed .column-art_thumbnail {
				width: 84px;
			}

			.art-cms-admin-thumbnail {
				display: block;
				width: 56px;
				height: 56px;
				object-fit: cover;
				border-radius: 4px;
				background: #f0f0f1;
			}

			.art-cms-admin-muted {
				color: #646970;
			}

			.art-cms-field-grid {
				display: grid;
				grid-template-columns: repeat(2, minmax(0, 1fr));
				gap: 12px 16px;
			}

			.art-cms-field-grid p {
				margin: 0;
			}

			.art-cms-field-grid label {
				display: block;
				margin-bottom: 6px;
				font-weight: 600;
			}

			.art-cms-field-grid__full {
				grid-column: 1 / -1;
			}

			@media (max-width: 782px) {
				.art-cms-field-grid {
					grid-template-columns: 1fr;
				}
			}
		</style>
		<?php
	}

	/**
	 * Insert a column after the checkbox column when possible.
	 *
	 * @param array<string, string> $columns Existing columns.
	 * @param string                $column_key New column key.
	 * @param string                $column_label New column label.
	 * @return array<string, string>
	 */
	private function insert_after_checkbox(array $columns, string $column_key, string $column_label): array {
		return $this->insert_after_column($columns, 'cb', $column_key, $column_label);
	}

	/**
	 * Insert a column after a target column when possible.
	 *
	 * @param array<string, string> $columns Existing columns.
	 * @param string                $target_column Target column key.
	 * @param string                $column_key New column key.
	 * @param string                $column_label New column label.
	 * @return array<string, string>
	 */
	private function insert_after_column(array $columns, string $target_column, string $column_key, string $column_label): array {
		$updated_columns = array();
		$inserted = false;

		foreach ($columns as $key => $label) {
			$updated_columns[$key] = $label;

			if ($target_column === $key) {
				$updated_columns[$column_key] = $column_label;
				$inserted = true;
			}
		}

		if (! $inserted) {
			$updated_columns[$column_key] = $column_label;
		}

		return $updated_columns;
	}

	/**
	 * Render a post meta value.
	 *
	 * @param int    $post_id Current post ID.
	 * @param string $meta_key Meta key.
	 */
	private function render_meta_value(int $post_id, string $meta_key): void {
		$value = $this->get_meta_value($post_id, $meta_key);

		if ('' === $value) {
			$this->render_empty_value();
			return;
		}

		echo esc_html($value);
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
	 * Render a muted empty value.
	 *
	 * @param string|null $label Optional label.
	 */
	private function render_empty_value(?string $label = null): void {
		$text = null === $label ? __('Not set', 'art-cms') : $label;

		echo '<span class="art-cms-admin-muted">' . esc_html($text) . '</span>';
	}
}
