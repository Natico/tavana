<?php
/**
 * Product editor behavior.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Product;

use ArtCms\PostTypes\ProductPostType;
use ArtCms\Taxonomies\ProductCategoryTaxonomy;
use ArtCms\Template\ProductTemplateRegistry;
use WP_Post;

/**
 * Manages the product editing screen.
 */
final class ProductEditor {
	private const TEMPLATE_FIELD = 'art_product_template';
	private const DESCRIPTION_FIELD = 'art_product_description';
	private const NONCE_ACTION = 'art_product_editor';
	private const NONCE_NAME = 'art_product_editor_nonce';

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
		add_filter('use_block_editor_for_post_type', array($this, 'disable_block_editor'), 10, 2);
		add_action('add_meta_boxes_' . ProductPostType::KEY, array($this, 'add_template_meta_box'));
		add_action('add_meta_boxes_' . ProductPostType::KEY, array($this, 'add_description_meta_box'));
		add_filter('wp_insert_post_data', array($this, 'map_description_to_post_content'), 10, 4);
		add_action('save_post_' . ProductPostType::KEY, array($this, 'save_selected_template'), 10, 2);
		add_action('admin_footer-post.php', array($this, 'render_visibility_script'));
		add_action('admin_footer-post-new.php', array($this, 'render_visibility_script'));
	}

	/**
	 * Register product editor meta.
	 */
	public function register_meta(): void {
		register_post_meta(
			ProductPostType::KEY,
			ProductTemplateRegistry::META_SELECTED_TEMPLATE,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => array($this->templates, 'sanitize_template_key'),
				'auth_callback'     => array($this, 'can_edit_template_meta'),
			)
		);
	}

	/**
	 * Disable the block editor for products only.
	 *
	 * @param bool   $use_block_editor Whether to use the block editor.
	 * @param string $post_type Post type key.
	 */
	public function disable_block_editor(bool $use_block_editor, string $post_type): bool {
		if (ProductPostType::KEY !== $post_type) {
			return $use_block_editor;
		}

		return false;
	}

	/**
	 * Register the Product Template meta box.
	 */
	public function add_template_meta_box(): void {
		add_meta_box(
			'art-product-template',
			__('Template', 'art-cms'),
			array($this, 'render_template_meta_box'),
			ProductPostType::KEY,
			'normal',
			'high'
		);
	}

	/**
	 * Render the product template selector.
	 *
	 * @param WP_Post $post Current product post.
	 */
	public function render_template_meta_box(WP_Post $post): void {
		wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

		$selected_template = $this->templates->get_selected_template_key($post->ID);
		?>
		<p>
			<label for="art-product-template"><?php esc_html_e('Template', 'art-cms'); ?> <span aria-hidden="true">*</span></label>
			<select id="art-product-template" class="widefat" name="<?php echo esc_attr(self::TEMPLATE_FIELD); ?>" data-art-product-template-select>
				<option value=""><?php esc_html_e('Select Template', 'art-cms'); ?></option>
				<?php foreach ($this->templates->get_templates() as $template_key => $template) : ?>
					<option value="<?php echo esc_attr($template_key); ?>" <?php selected($selected_template, $template_key); ?>>
						<?php echo esc_html($template['label']); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	/**
	 * Register the Product Description meta box.
	 */
	public function add_description_meta_box(): void {
		add_meta_box(
			'art-product-description',
			__('Description', 'art-cms'),
			array($this, 'render_description_meta_box'),
			ProductPostType::KEY,
			'normal',
			'high'
		);
	}

	/**
	 * Render a plain-text product description textarea.
	 *
	 * @param WP_Post $post Current product post.
	 */
	public function render_description_meta_box(WP_Post $post): void {
		$selected_template = $this->templates->get_selected_template_key($post->ID);
		$field_state = $this->templates->get_field_state($selected_template, ProductTemplateRegistry::FIELD_DESCRIPTION);
		?>
		<p <?php echo $this->get_field_wrapper_attributes(ProductTemplateRegistry::FIELD_DESCRIPTION, $field_state); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<label for="art-product-description">
				<?php esc_html_e('Description', 'art-cms'); ?>
				<?php echo $this->get_required_marker($field_state); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</label>
			<textarea id="art-product-description" class="widefat" name="<?php echo esc_attr(self::DESCRIPTION_FIELD); ?>" rows="8"><?php echo esc_textarea($post->post_content); ?></textarea>
		</p>
		<?php
	}

	/**
	 * Map the submitted plain-text description to core post_content.
	 *
	 * @param array<string, mixed> $data Sanitized post data.
	 * @param array<string, mixed> $postarr Raw post data.
	 * @param array<string, mixed> $unsanitized_postarr Unsanitized post data.
	 * @param bool                 $update Whether this is an existing post update.
	 * @return array<string, mixed>
	 */
	public function map_description_to_post_content(array $data, array $postarr, array $unsanitized_postarr, bool $update): array {
		unset($unsanitized_postarr);

		if (! $this->can_map_description($data, $postarr, $update)) {
			return $data;
		}

		$data['post_content'] = sanitize_textarea_field(wp_unslash($_POST[self::DESCRIPTION_FIELD]));

		return $data;
	}

	/**
	 * Save selected product template.
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post Current post object.
	 */
	public function save_selected_template(int $post_id, WP_Post $post): void {
		if (! $this->can_save_template($post_id, $post)) {
			return;
		}

		$raw_template = isset($_POST[self::TEMPLATE_FIELD]) && is_string($_POST[self::TEMPLATE_FIELD])
			? wp_unslash($_POST[self::TEMPLATE_FIELD])
			: '';

		$template_key = $this->templates->sanitize_template_key($raw_template);

		if ('' === trim((string) $raw_template)) {
			delete_post_meta($post_id, ProductTemplateRegistry::META_SELECTED_TEMPLATE);
			return;
		}

		if ('' === $template_key) {
			return;
		}

		update_post_meta($post_id, ProductTemplateRegistry::META_SELECTED_TEMPLATE, $template_key);
	}

	/**
	 * Authorize editing the selected template meta.
	 *
	 * @param mixed  $allowed Existing authorization result.
	 * @param string $meta_key Meta key.
	 * @param int    $post_id Post ID.
	 * @param int    $user_id User ID.
	 */
	public function can_edit_template_meta($allowed, string $meta_key, int $post_id, int $user_id): bool {
		unset($allowed, $meta_key);

		return user_can($user_id, 'edit_post', $post_id);
	}

	/**
	 * Render the small product field visibility script.
	 */
	public function render_visibility_script(): void {
		$screen = get_current_screen();

		if (null === $screen || ProductPostType::KEY !== $screen->post_type) {
			return;
		}

		$template_data = wp_json_encode($this->templates->get_templates_for_script());

		if (! is_string($template_data)) {
			return;
		}
		?>
		<script>
			window.artProductTemplates = <?php echo $template_data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
			(function () {
				var nativeBoxes = {
					cover: 'postimagediv',
					categories: '<?php echo esc_js(ProductCategoryTaxonomy::KEY); ?>div'
				};

				function getState(template, field) {
					if (!template || !template.fields || !template.fields[field]) {
						return 'hidden';
					}

					return template.fields[field];
				}

				function setRequiredMarkers(container, isRequired) {
					var markers = container.querySelectorAll('[data-art-product-required]');

					markers.forEach(function (marker) {
						marker.hidden = !isRequired;
					});
				}

				function setNativeRequiredMarker(box, isRequired) {
					var title = box.querySelector('.hndle, h2, h3');

					if (!title) {
						return;
					}

					var marker = title.querySelector('[data-art-product-native-required]');

					if (!marker) {
						marker = document.createElement('span');
						marker.setAttribute('data-art-product-native-required', 'true');
						marker.setAttribute('aria-hidden', 'true');
						marker.textContent = ' *';
						title.appendChild(marker);
					}

					marker.hidden = !isRequired;
				}

				function applyTemplateVisibility() {
					var select = document.querySelector('[data-art-product-template-select]');

					if (!select) {
						return;
					}

					var template = window.artProductTemplates[select.value] || null;

					document.querySelectorAll('[data-art-product-field]').forEach(function (fieldElement) {
						var field = fieldElement.getAttribute('data-art-product-field');
						var state = getState(template, field);

						fieldElement.style.display = 'hidden' === state ? 'none' : '';
						setRequiredMarkers(fieldElement, 'required' === state);
					});

					Object.keys(nativeBoxes).forEach(function (field) {
						var box = document.getElementById(nativeBoxes[field]);

						if (!box) {
							return;
						}

						var state = getState(template, field);

						box.style.display = 'hidden' === state ? 'none' : '';
						setNativeRequiredMarker(box, 'required' === state);
					});
				}

				document.addEventListener('DOMContentLoaded', function () {
					var select = document.querySelector('[data-art-product-template-select]');

					if (select) {
						select.addEventListener('change', applyTemplateVisibility);
					}

					applyTemplateVisibility();
				});
			}());
		</script>
		<?php
	}

	/**
	 * Check whether the submitted description should be mapped.
	 *
	 * @param array<string, mixed> $data Sanitized post data.
	 * @param array<string, mixed> $postarr Raw post data.
	 * @param bool                 $update Whether this is an existing post update.
	 */
	private function can_map_description(array $data, array $postarr, bool $update): bool {
		$post_type = isset($data['post_type']) && is_string($data['post_type'])
			? $data['post_type']
			: '';

		if (ProductPostType::KEY !== $post_type) {
			return false;
		}

		if (! isset($_POST[self::DESCRIPTION_FIELD]) || ! is_string($_POST[self::DESCRIPTION_FIELD])) {
			return false;
		}

		if (! isset($_POST[self::NONCE_NAME]) || ! is_string($_POST[self::NONCE_NAME])) {
			return false;
		}

		if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
			return false;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return false;
		}

		return $this->current_user_can_edit_product($postarr, $update);
	}

	/**
	 * Check whether the selected template can be saved.
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post Current post object.
	 */
	private function can_save_template(int $post_id, WP_Post $post): bool {
		if (ProductPostType::KEY !== $post->post_type) {
			return false;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return false;
		}

		if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
			return false;
		}

		if (! isset($_POST[self::TEMPLATE_FIELD]) || ! is_string($_POST[self::TEMPLATE_FIELD])) {
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

	/**
	 * Check the current user's product edit capability.
	 *
	 * @param array<string, mixed> $postarr Raw post data.
	 * @param bool                 $update Whether this is an existing post update.
	 */
	private function current_user_can_edit_product(array $postarr, bool $update): bool {
		$post_id = isset($postarr['ID']) ? absint($postarr['ID']) : 0;

		if ($update && $post_id > 0) {
			return current_user_can('edit_post', $post_id);
		}

		$post_type_object = get_post_type_object(ProductPostType::KEY);

		if (null === $post_type_object) {
			return false;
		}

		return current_user_can($post_type_object->cap->edit_posts);
	}
}
