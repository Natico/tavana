<?php
/**
 * Product data fields.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Product;

use ArtCms\PostTypes\ProductPostType;
use ArtCms\Template\ProductTemplateRegistry;
use WP_Post;

/**
 * Registers and manages basic product data fields.
 */
final class ProductFields {
	public const META_SHORT_DESCRIPTION = '_art_product_short_description';
	public const META_PRODUCT_CODE = '_art_product_code';
	public const META_DESIGN_YEAR = '_art_product_design_year';

	private const NONCE_ACTION = 'art_product_fields';
	private const NONCE_NAME = 'art_product_fields_nonce';

	/**
	 * Product template registry.
	 */
	private ProductTemplateRegistry $templates;

	/**
	 * Constructor.
	 *
	 * @param ProductTemplateRegistry $templates Product template registry.
	 */
	public function __construct(ProductTemplateRegistry $templates) {
		$this->templates = $templates;
	}

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_action('init', array($this, 'register_meta'));
		add_action('add_meta_boxes_' . ProductPostType::KEY, array($this, 'add_meta_box'));
		add_action('save_post_' . ProductPostType::KEY, array($this, 'save'), 10, 2);
	}

	/**
	 * Register product post meta.
	 */
	public function register_meta(): void {
		register_post_meta(
			ProductPostType::KEY,
			self::META_SHORT_DESCRIPTION,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => array($this, 'sanitize_short_description'),
				'auth_callback'     => array($this, 'can_edit_meta'),
			)
		);

		register_post_meta(
			ProductPostType::KEY,
			self::META_PRODUCT_CODE,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => array($this, 'sanitize_text_meta'),
				'auth_callback'     => array($this, 'can_edit_meta'),
			)
		);

		register_post_meta(
			ProductPostType::KEY,
			self::META_DESIGN_YEAR,
			array(
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => array($this, 'sanitize_integer_meta'),
				'auth_callback'     => array($this, 'can_edit_meta'),
			)
		);
	}

	/**
	 * Register the Product Details meta box.
	 */
	public function add_meta_box(): void {
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
	 * Render the Product Details meta box.
	 *
	 * @param WP_Post $post Current product post.
	 */
	public function render_meta_box(WP_Post $post): void {
		wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

		$selected_template = $this->templates->get_selected_template_key($post->ID);
		$short_description = $this->get_string_meta($post->ID, self::META_SHORT_DESCRIPTION);
		$product_code = $this->get_string_meta($post->ID, self::META_PRODUCT_CODE);
		$design_year = $this->get_string_meta($post->ID, self::META_DESIGN_YEAR);
		$short_description_state = $this->templates->get_field_state($selected_template, ProductTemplateRegistry::FIELD_SHORT_DESCRIPTION);
		$product_code_state = $this->templates->get_field_state($selected_template, ProductTemplateRegistry::FIELD_PRODUCT_CODE);
		$design_year_state = $this->templates->get_field_state($selected_template, ProductTemplateRegistry::FIELD_DESIGN_YEAR);
		?>
		<p <?php echo $this->get_field_wrapper_attributes(ProductTemplateRegistry::FIELD_SHORT_DESCRIPTION, $short_description_state); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<label for="art-product-short-description">
				<?php esc_html_e('Short Description', 'art-cms'); ?>
				<?php echo $this->get_required_marker($short_description_state); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</label>
			<textarea id="art-product-short-description" class="widefat" name="art_product_short_description" rows="4"><?php echo esc_textarea($short_description); ?></textarea>
		</p>

		<p <?php echo $this->get_field_wrapper_attributes(ProductTemplateRegistry::FIELD_PRODUCT_CODE, $product_code_state); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<label for="art-product-code">
				<?php esc_html_e('Product Code', 'art-cms'); ?>
				<?php echo $this->get_required_marker($product_code_state); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</label>
			<input id="art-product-code" class="widefat" type="text" name="art_product_code" value="<?php echo esc_attr($product_code); ?>">
		</p>

		<p <?php echo $this->get_field_wrapper_attributes(ProductTemplateRegistry::FIELD_DESIGN_YEAR, $design_year_state); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<label for="art-product-design-year">
				<?php esc_html_e('Design Year', 'art-cms'); ?>
				<?php echo $this->get_required_marker($design_year_state); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</label>
			<input id="art-product-design-year" class="widefat" type="text" inputmode="numeric" name="art_product_design_year" value="<?php echo esc_attr($design_year); ?>">
		</p>
		<?php
	}

	/**
	 * Save product fields.
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post Current post object.
	 */
	public function save(int $post_id, WP_Post $post): void {
		if (! $this->can_save($post_id, $post)) {
			return;
		}

		$this->save_textarea_meta($post_id, self::META_SHORT_DESCRIPTION, 'art_product_short_description');
		$this->save_text_meta($post_id, self::META_PRODUCT_CODE, 'art_product_code');
		$this->save_integer_meta($post_id, self::META_DESIGN_YEAR, 'art_product_design_year');
	}

	/**
	 * Sanitize textarea meta.
	 *
	 * @param mixed $value Raw value.
	 */
	public function sanitize_short_description($value): string {
		return sanitize_textarea_field((string) $value);
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
	 * Sanitize integer meta.
	 *
	 * @param mixed $value Raw value.
	 */
	public function sanitize_integer_meta($value): int {
		return (int) $value;
	}

	/**
	 * Authorize editing registered product meta.
	 *
	 * @param mixed  $allowed Existing authorization result.
	 * @param string $meta_key Meta key.
	 * @param int    $post_id Post ID.
	 * @param int    $user_id User ID.
	 */
	public function can_edit_meta($allowed, string $meta_key, int $post_id, int $user_id): bool {
		unset($allowed, $meta_key);

		return user_can($user_id, 'edit_post', $post_id);
	}

	/**
	 * Get a meta value as a string.
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
	 * Check whether product fields can be saved.
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post Current post object.
	 */
	private function can_save(int $post_id, WP_Post $post): bool {
		if (ProductPostType::KEY !== $post->post_type) {
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

		return current_user_can('edit_post', $post_id);
	}

	/**
	 * Save textarea meta, deleting empty values.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Meta key.
	 * @param string $request_key Request key.
	 */
	private function save_textarea_meta(int $post_id, string $meta_key, string $request_key): void {
		$value = isset($_POST[$request_key]) && is_string($_POST[$request_key])
			? $this->sanitize_short_description(wp_unslash($_POST[$request_key]))
			: '';

		$this->update_or_delete_meta($post_id, $meta_key, $value);
	}

	/**
	 * Save text meta, deleting empty values.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Meta key.
	 * @param string $request_key Request key.
	 */
	private function save_text_meta(int $post_id, string $meta_key, string $request_key): void {
		$value = isset($_POST[$request_key]) && is_string($_POST[$request_key])
			? $this->sanitize_text_meta(wp_unslash($_POST[$request_key]))
			: '';

		$this->update_or_delete_meta($post_id, $meta_key, $value);
	}

	/**
	 * Save integer meta, deleting empty values.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Meta key.
	 * @param string $request_key Request key.
	 */
	private function save_integer_meta(int $post_id, string $meta_key, string $request_key): void {
		if (! isset($_POST[$request_key]) || ! is_scalar($_POST[$request_key])) {
			delete_post_meta($post_id, $meta_key);
			return;
		}

		$raw_value = trim((string) wp_unslash($_POST[$request_key]));

		if ('' === $raw_value) {
			delete_post_meta($post_id, $meta_key);
			return;
		}

		update_post_meta($post_id, $meta_key, $this->sanitize_integer_meta($raw_value));
	}

	/**
	 * Update non-empty meta or delete empty meta.
	 *
	 * @param int    $post_id Post ID.
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

	/**
	 * Get field wrapper attributes.
	 *
	 * @param string $field_key Field key.
	 * @param string $field_state Field state.
	 */
	private function get_field_wrapper_attributes(string $field_key, string $field_state): string {
		$attributes = sprintf(
			'data-art-product-field="%s"',
			esc_attr($field_key)
		);

		if (ProductTemplateRegistry::STATE_HIDDEN === $field_state) {
			$attributes .= ' style="display: none;"';
		}

		return $attributes;
	}

	/**
	 * Get a required marker for field labels.
	 *
	 * @param string $field_state Field state.
	 */
	private function get_required_marker(string $field_state): string {
		return sprintf(
			'<span data-art-product-required aria-hidden="true"%s>*</span>',
			ProductTemplateRegistry::STATE_REQUIRED === $field_state ? '' : ' hidden'
		);
	}
}
