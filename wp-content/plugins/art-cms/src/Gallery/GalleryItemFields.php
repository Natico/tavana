<?php
/**
 * Gallery item admin fields.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Gallery;

use ArtCms\PostTypes\GalleryItemPostType;
use WP_Post;

/**
 * Adds admin-only gallery item detail fields.
 */
final class GalleryItemFields {
	public const META_SUBTITLE = '_art_gallery_subtitle';
	public const META_ARTWORK_DATE = '_art_gallery_artwork_date';
	public const META_MEDIUM = '_art_gallery_medium';
	public const META_DIMENSIONS = '_art_gallery_dimensions';
	public const META_CREDIT_LINE = '_art_gallery_credit_line';

	private const NONCE_ACTION = 'art_gallery_item_fields';
	private const NONCE_NAME = 'art_gallery_item_fields_nonce';

	/**
	 * Register field hooks.
	 */
	public function register_hooks(): void {
		add_action('add_meta_boxes_' . GalleryItemPostType::KEY, array($this, 'add_meta_boxes'), 10, 0);
		add_action('save_post_' . GalleryItemPostType::KEY, array($this, 'save'), 10, 2);
	}

	/**
	 * Register gallery item details meta box.
	 */
	public function add_meta_boxes(): void {
		add_meta_box(
			'art-gallery-item-details',
			__('Gallery Item Details', 'art-cms'),
			array($this, 'render_meta_box'),
			GalleryItemPostType::KEY,
			'normal',
			'high'
		);
	}

	/**
	 * Render gallery item details meta box.
	 *
	 * @param WP_Post $post Current post.
	 */
	public function render_meta_box(WP_Post $post): void {
		wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

		$subtitle = $this->get_meta_value($post->ID, self::META_SUBTITLE);
		$artwork_date = $this->get_meta_value($post->ID, self::META_ARTWORK_DATE);
		$medium = $this->get_meta_value($post->ID, self::META_MEDIUM);
		$dimensions = $this->get_meta_value($post->ID, self::META_DIMENSIONS);
		$credit_line = $this->get_meta_value($post->ID, self::META_CREDIT_LINE);
		?>
		<div class="art-cms-field-grid">
			<p class="art-cms-field-grid__full">
				<label for="art-gallery-subtitle"><?php esc_html_e('Subtitle', 'art-cms'); ?></label>
				<input id="art-gallery-subtitle" class="widefat" type="text" name="art_gallery_subtitle" value="<?php echo esc_attr($subtitle); ?>">
			</p>

			<p>
				<label for="art-gallery-artwork-date"><?php esc_html_e('Artwork Date', 'art-cms'); ?></label>
				<input id="art-gallery-artwork-date" class="widefat" type="text" name="art_gallery_artwork_date" value="<?php echo esc_attr($artwork_date); ?>">
			</p>

			<p>
				<label for="art-gallery-medium"><?php esc_html_e('Medium', 'art-cms'); ?></label>
				<input id="art-gallery-medium" class="widefat" type="text" name="art_gallery_medium" value="<?php echo esc_attr($medium); ?>">
			</p>

			<p>
				<label for="art-gallery-dimensions"><?php esc_html_e('Dimensions', 'art-cms'); ?></label>
				<input id="art-gallery-dimensions" class="widefat" type="text" name="art_gallery_dimensions" value="<?php echo esc_attr($dimensions); ?>">
			</p>

			<p>
				<label for="art-gallery-credit-line"><?php esc_html_e('Credit Line', 'art-cms'); ?></label>
				<input id="art-gallery-credit-line" class="widefat" type="text" name="art_gallery_credit_line" value="<?php echo esc_attr($credit_line); ?>">
			</p>
		</div>
		<?php
	}

	/**
	 * Save gallery item fields.
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post Current post.
	 */
	public function save(int $post_id, WP_Post $post): void {
		if (! $this->can_save($post_id, $post)) {
			return;
		}

		$this->save_text_meta($post_id, self::META_SUBTITLE, 'art_gallery_subtitle');
		$this->save_text_meta($post_id, self::META_ARTWORK_DATE, 'art_gallery_artwork_date');
		$this->save_text_meta($post_id, self::META_MEDIUM, 'art_gallery_medium');
		$this->save_text_meta($post_id, self::META_DIMENSIONS, 'art_gallery_dimensions');
		$this->save_text_meta($post_id, self::META_CREDIT_LINE, 'art_gallery_credit_line');
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
		if (GalleryItemPostType::KEY !== $post->post_type) {
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

		if ('' === $value) {
			delete_post_meta($post_id, $meta_key);
			return;
		}

		update_post_meta($post_id, $meta_key, $value);
	}
}
