<?php
/**
 * Product category custom fields.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Taxonomies;

use WP_Term;

/**
 * Adds Product Category data fields.
 */
final class ProductCategoryFields {
	public const META_COVER_ID = '_art_product_category_cover_id';

	private const NONCE_ACTION = 'art_product_category_fields';
	private const NONCE_NAME = 'art_product_category_fields_nonce';
	private const FIELD_COVER_ID = 'art_product_category_cover_id';

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_action('init', array($this, 'register_meta'));
		add_action(ProductCategoryTaxonomy::KEY . '_add_form_fields', array($this, 'render_add_cover_field'));
		add_action(ProductCategoryTaxonomy::KEY . '_edit_form_fields', array($this, 'render_edit_cover_field'));
		add_action('created_' . ProductCategoryTaxonomy::KEY, array($this, 'save_cover_field'));
		add_action('edited_' . ProductCategoryTaxonomy::KEY, array($this, 'save_cover_field'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_media'));
		add_action('admin_footer-edit-tags.php', array($this, 'render_media_script'));
		add_action('admin_footer-term.php', array($this, 'render_media_script'));
	}

	/**
	 * Register Product Category term meta.
	 */
	public function register_meta(): void {
		register_term_meta(
			ProductCategoryTaxonomy::KEY,
			self::META_COVER_ID,
			array(
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => 'absint',
				'auth_callback'     => array($this, 'can_edit_term_meta'),
			)
		);
	}

	/**
	 * Render cover field on the add term screen.
	 */
	public function render_add_cover_field(): void {
		wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
		?>
		<div class="form-field term-art-category-cover-wrap">
			<label for="art-product-category-cover-id"><?php esc_html_e('Cover / Hero Image', 'art-cms'); ?></label>
			<?php $this->render_cover_control(0); ?>
			<p><?php esc_html_e('Select an image from the WordPress Media Library.', 'art-cms'); ?></p>
		</div>
		<?php
	}

	/**
	 * Render cover field on the edit term screen.
	 *
	 * @param WP_Term $term Current term.
	 */
	public function render_edit_cover_field(WP_Term $term): void {
		$cover_id = $this->get_cover_id($term->term_id);
		?>
		<tr class="form-field term-art-category-cover-wrap">
			<th scope="row">
				<label for="art-product-category-cover-id"><?php esc_html_e('Cover / Hero Image', 'art-cms'); ?></label>
			</th>
			<td>
				<?php
				wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
				$this->render_cover_control($cover_id);
				?>
				<p class="description"><?php esc_html_e('Select an image from the WordPress Media Library.', 'art-cms'); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save the cover attachment ID.
	 *
	 * @param int $term_id Term ID.
	 */
	public function save_cover_field(int $term_id): void {
		if (! $this->can_save($term_id)) {
			return;
		}

		$cover_id = isset($_POST[self::FIELD_COVER_ID]) && is_scalar($_POST[self::FIELD_COVER_ID])
			? absint(wp_unslash($_POST[self::FIELD_COVER_ID]))
			: 0;

		if (0 === $cover_id) {
			delete_term_meta($term_id, self::META_COVER_ID);
			return;
		}

		if (! wp_attachment_is_image($cover_id)) {
			return;
		}

		update_term_meta($term_id, self::META_COVER_ID, $cover_id);
	}

	/**
	 * Enqueue native media scripts on Product Category admin screens.
	 */
	public function enqueue_media(): void {
		if (! $this->is_product_category_screen()) {
			return;
		}

		wp_enqueue_media();
	}

	/**
	 * Render small scoped media picker script.
	 */
	public function render_media_script(): void {
		if (! $this->is_product_category_screen()) {
			return;
		}
		?>
		<script>
			(function () {
				var frame;

				function updatePreview(container, attachmentId, imageUrl) {
					var input = container.querySelector('[data-art-category-cover-input]');
					var preview = container.querySelector('[data-art-category-cover-preview]');
					var removeButton = container.querySelector('[data-art-category-cover-remove]');

					if (input) {
						input.value = attachmentId || '';
					}

					if (preview) {
						preview.innerHTML = imageUrl ? '<img src="' + imageUrl + '" alt="" style="max-width: 160px; height: auto;" />' : '';
					}

					if (removeButton) {
						removeButton.hidden = !attachmentId;
					}
				}

				document.addEventListener('click', function (event) {
					var selectButton = event.target.closest('[data-art-category-cover-select]');
					var removeButton = event.target.closest('[data-art-category-cover-remove]');

					if (selectButton) {
						event.preventDefault();

						var container = selectButton.closest('[data-art-category-cover]');

						if (!container) {
							return;
						}

						frame = wp.media({
							title: '<?php echo esc_js(__('Select Cover / Hero Image', 'art-cms')); ?>',
							button: {
								text: '<?php echo esc_js(__('Use this image', 'art-cms')); ?>'
							},
							library: {
								type: 'image'
							},
							multiple: false
						});

						frame.on('select', function () {
							var attachment = frame.state().get('selection').first().toJSON();
							var imageUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

							updatePreview(container, attachment.id, imageUrl);
						});

						frame.open();
					}

					if (removeButton) {
						event.preventDefault();

						var removeContainer = removeButton.closest('[data-art-category-cover]');

						if (removeContainer) {
							updatePreview(removeContainer, '', '');
						}
					}
				});
			}());
		</script>
		<?php
	}

	/**
	 * Authorize editing registered term meta.
	 *
	 * @param mixed  $allowed Existing authorization result.
	 * @param string $meta_key Meta key.
	 * @param int    $term_id Term ID.
	 * @param int    $user_id User ID.
	 */
	public function can_edit_term_meta($allowed, string $meta_key, int $term_id, int $user_id): bool {
		unset($allowed, $meta_key, $term_id);

		return user_can($user_id, 'manage_categories');
	}

	/**
	 * Render the shared cover control.
	 *
	 * @param int $cover_id Cover attachment ID.
	 */
	private function render_cover_control(int $cover_id): void {
		$image = $cover_id > 0 ? wp_get_attachment_image($cover_id, 'thumbnail') : '';
		?>
		<div data-art-category-cover>
			<input
				id="art-product-category-cover-id"
				type="hidden"
				name="<?php echo esc_attr(self::FIELD_COVER_ID); ?>"
				value="<?php echo esc_attr((string) $cover_id); ?>"
				data-art-category-cover-input
			>
			<div data-art-category-cover-preview>
				<?php echo wp_kses_post($image); ?>
			</div>
			<p>
				<button type="button" class="button" data-art-category-cover-select>
					<?php esc_html_e('Select Image', 'art-cms'); ?>
				</button>
				<button type="button" class="button" data-art-category-cover-remove <?php echo 0 === $cover_id ? 'hidden' : ''; ?>>
					<?php esc_html_e('Remove Image', 'art-cms'); ?>
				</button>
			</p>
		</div>
		<?php
	}

	/**
	 * Get the cover attachment ID.
	 *
	 * @param int $term_id Term ID.
	 */
	private function get_cover_id(int $term_id): int {
		$cover_id = get_term_meta($term_id, self::META_COVER_ID, true);

		return is_numeric($cover_id) ? absint($cover_id) : 0;
	}

	/**
	 * Check whether cover field can be saved.
	 *
	 * @param int $term_id Term ID.
	 */
	private function can_save(int $term_id): bool {
		if (! isset($_POST[self::NONCE_NAME]) || ! is_string($_POST[self::NONCE_NAME])) {
			return false;
		}

		if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
			return false;
		}

		return current_user_can('edit_term', $term_id);
	}

	/**
	 * Check whether the current admin screen is a Product Category screen.
	 */
	private function is_product_category_screen(): bool {
		$screen = get_current_screen();

		return null !== $screen && ProductCategoryTaxonomy::KEY === $screen->taxonomy;
	}
}
