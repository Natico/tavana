<?php
/**
 * Product admin fields.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Product;

use ArtCms\PostTypes\ProductPostType;
use WP_Post;

/**
 * Adds admin-only product detail fields.
 */
final class ProductFields {
	public const META_SUBTITLE = '_art_product_subtitle';
	public const META_MATERIAL = '_art_product_material';
	public const META_DIMENSIONS = '_art_product_dimensions';
	public const META_PRODUCTION_YEAR = '_art_product_production_year';
	public const META_AVAILABILITY_NOTE = '_art_product_availability_note';

	private const NONCE_ACTION = 'art_product_fields';
	private const NONCE_NAME = 'art_product_fields_nonce';

	/**
	 * Register field hooks.
	 */
	public function register_hooks(): void {
		add_action('add_meta_boxes_' . ProductPostType::KEY, array($this, 'add_meta_boxes'), 10, 0);
		add_action('save_post_' . ProductPostType::KEY, array($this, 'save'), 10, 2);
	}

	/**
	 * Register product details meta box.
	 */
	public function add_meta_boxes(): void {
		add_meta_box(
			'art-product-details',
			__('Product Details', 'art-cms'),
			array($this, 'render_meta_box'),
			ProductPostType::KEY,
			'normal',
			'high'
		);
	}

	/**
	 * Render product details meta box.
	 *
	 * @param WP_Post $post Current post.
	 */
	public function render_meta_box(WP_Post $post): void {
		wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

		$subtitle = $this->get_meta_value($post->ID, self::META_SUBTITLE);
		$material = $this->get_meta_value($post->ID, self::META_MATERIAL);
		$dimensions = $this->get_meta_value($post->ID, self::META_DIMENSIONS);
		$production_year = $this->get_meta_value($post->ID, self::META_PRODUCTION_YEAR);
		$availability_note = $this->get_meta_value($post->ID, self::META_AVAILABILITY_NOTE);
		?>
		<div class="art-cms-field-grid">
			<p class="art-cms-field-grid__full">
				<label for="art-product-subtitle"><?php esc_html_e('Subtitle', 'art-cms'); ?></label>
				<input id="art-product-subtitle" class="widefat" type="text" name="art_product_subtitle" value="<?php echo esc_attr($subtitle); ?>">
			</p>

			<p>
				<label for="art-product-material"><?php esc_html_e('Material', 'art-cms'); ?></label>
				<input id="art-product-material" class="widefat" type="text" name="art_product_material" value="<?php echo esc_attr($material); ?>">
			</p>

			<p>
				<label for="art-product-dimensions"><?php esc_html_e('Dimensions', 'art-cms'); ?></label>
				<input id="art-product-dimensions" class="widefat" type="text" name="art_product_dimensions" value="<?php echo esc_attr($dimensions); ?>">
			</p>

			<p>
				<label for="art-product-production-year"><?php esc_html_e('Production Year', 'art-cms'); ?></label>
				<input id="art-product-production-year" class="widefat" type="text" name="art_product_production_year" value="<?php echo esc_attr($production_year); ?>">
			</p>

			<p>
				<label for="art-product-availability-note"><?php esc_html_e('Availability Note', 'art-cms'); ?></label>
				<input id="art-product-availability-note" class="widefat" type="text" name="art_product_availability_note" value="<?php echo esc_attr($availability_note); ?>">
			</p>
		</div>
		<?php
	}

	/**
	 * Save product fields.
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post Current post.
	 */
	public function save(int $post_id, WP_Post $post): void {
		if (! $this->can_save($post_id, $post)) {
			return;
		}

		$this->save_text_meta($post_id, self::META_SUBTITLE, 'art_product_subtitle');
		$this->save_text_meta($post_id, self::META_MATERIAL, 'art_product_material');
		$this->save_text_meta($post_id, self::META_DIMENSIONS, 'art_product_dimensions');
		$this->save_text_meta($post_id, self::META_PRODUCTION_YEAR, 'art_product_production_year');
		$this->save_text_meta($post_id, self::META_AVAILABILITY_NOTE, 'art_product_availability_note');
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
		if (ProductPostType::KEY !== $post->post_type) {
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
