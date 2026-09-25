<?php
/**
 * Product homepage fields.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Product;

use ArtCms\PostTypes\ProductPostType;
use WP_Post;

/**
 * Registers and manages Product homepage data.
 */
final class ProductHomepageFields {
	public const META_FEATURED = '_art_product_featured_on_homepage';
	public const META_IMAGE_ID = '_art_product_homepage_image_id';

	private const NONCE_ACTION = 'art_product_homepage_fields';
	private const NONCE_NAME = 'art_product_homepage_fields_nonce';
	private const FIELD_FEATURED = 'art_product_featured_on_homepage';
	private const FIELD_IMAGE_ID = 'art_product_homepage_image_id';

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_action('init', array($this, 'register_meta'));
		add_action('add_meta_boxes_' . ProductPostType::KEY, array($this, 'add_meta_box'));
		add_action('save_post_' . ProductPostType::KEY, array($this, 'save'), 10, 2);
		add_action('admin_enqueue_scripts', array($this, 'enqueue_media'));
		add_action('admin_footer-post.php', array($this, 'render_media_script'));
		add_action('admin_footer-post-new.php', array($this, 'render_media_script'));
	}

	/**
	 * Register Product homepage meta.
	 */
	public function register_meta(): void {
		register_post_meta(
			ProductPostType::KEY,
			self::META_FEATURED,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => array($this, 'sanitize_featured_meta'),
				'auth_callback'     => array($this, 'can_edit_meta'),
			)
		);

		register_post_meta(
			ProductPostType::KEY,
			self::META_IMAGE_ID,
			array(
				'type'              => 'integer',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => 'absint',
				'auth_callback'     => array($this, 'can_edit_meta'),
			)
		);
	}

	/**
	 * Register the Homepage meta box.
	 */
	public function add_meta_box(): void {
		add_meta_box(
			'art-product-homepage',
			__('Homepage', 'art-cms'),
			array($this, 'render_meta_box'),
			ProductPostType::KEY,
			'side',
			'default'
		);
	}

	/**
	 * Render the Homepage meta box.
	 *
	 * @param WP_Post $post Current product post.
	 */
	public function render_meta_box(WP_Post $post): void {
		wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

		$featured = '1' === get_post_meta($post->ID, self::META_FEATURED, true);
		$image_id = $this->get_homepage_image_id($post->ID);
		$image = $image_id > 0 ? wp_get_attachment_image($image_id, 'thumbnail') : '';
		?>
		<p>
			<label>
				<input type="checkbox" name="<?php echo esc_attr(self::FIELD_FEATURED); ?>" value="1" <?php checked($featured); ?>>
				<?php esc_html_e('Featured on homepage', 'art-cms'); ?>
			</label>
		</p>

		<div data-art-product-homepage-image>
			<p><strong><?php esc_html_e('Homepage Image', 'art-cms'); ?></strong></p>
			<input
				type="hidden"
				name="<?php echo esc_attr(self::FIELD_IMAGE_ID); ?>"
				value="<?php echo esc_attr((string) $image_id); ?>"
				data-art-product-homepage-image-input
			>
			<div data-art-product-homepage-image-preview>
				<?php echo wp_kses_post($image); ?>
			</div>
			<p>
				<button type="button" class="button" data-art-product-homepage-image-select>
					<?php esc_html_e('Select Image', 'art-cms'); ?>
				</button>
				<button type="button" class="button" data-art-product-homepage-image-remove <?php echo 0 === $image_id ? 'hidden' : ''; ?>>
					<?php esc_html_e('Remove Image', 'art-cms'); ?>
				</button>
			</p>
			<p class="description"><?php esc_html_e('Optional image for homepage presentation. Falls back to the Product cover / hero image.', 'art-cms'); ?></p>
		</div>
		<?php
	}

	/**
	 * Save Product homepage fields.
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post Current post object.
	 */
	public function save(int $post_id, WP_Post $post): void {
		if (! $this->can_save($post_id, $post)) {
			return;
		}

		$featured = isset($_POST[self::FIELD_FEATURED]) && '1' === (string) wp_unslash($_POST[self::FIELD_FEATURED]);

		if ($featured) {
			update_post_meta($post_id, self::META_FEATURED, '1');
		} else {
			delete_post_meta($post_id, self::META_FEATURED);
		}

		$image_id = isset($_POST[self::FIELD_IMAGE_ID]) && is_scalar($_POST[self::FIELD_IMAGE_ID])
			? absint(wp_unslash($_POST[self::FIELD_IMAGE_ID]))
			: 0;

		if (0 === $image_id) {
			delete_post_meta($post_id, self::META_IMAGE_ID);
			return;
		}

		if (! wp_attachment_is_image($image_id)) {
			return;
		}

		update_post_meta($post_id, self::META_IMAGE_ID, $image_id);
	}

	/**
	 * Enqueue native media scripts on Product edit screens.
	 */
	public function enqueue_media(): void {
		if (! $this->is_product_edit_screen()) {
			return;
		}

		wp_enqueue_media();
	}

	/**
	 * Render the scoped media picker script.
	 */
	public function render_media_script(): void {
		if (! $this->is_product_edit_screen()) {
			return;
		}
		?>
		<script>
			(function () {
				var frame;

				function updatePreview(container, attachmentId, imageUrl) {
					var input = container.querySelector('[data-art-product-homepage-image-input]');
					var preview = container.querySelector('[data-art-product-homepage-image-preview]');
					var removeButton = container.querySelector('[data-art-product-homepage-image-remove]');

					if (input) {
						input.value = attachmentId || '';
					}

					if (preview) {
						preview.innerHTML = imageUrl ? '<img src="' + imageUrl + '" alt="" style="max-width: 160px; height: auto;" />' : '';
					}

					if (removeButton) {
						removeButton.hidden = !attachmentId;
						removeButton.classList.toggle('hidden', !attachmentId);
					}
				}

				document.addEventListener('click', function (event) {
					var selectButton = event.target.closest('[data-art-product-homepage-image-select]');
					var removeButton = event.target.closest('[data-art-product-homepage-image-remove]');

					if (selectButton) {
						event.preventDefault();

						var container = selectButton.closest('[data-art-product-homepage-image]');

						if (!container) {
							return;
						}

						frame = wp.media({
							title: '<?php echo esc_js(__('Select Homepage Image', 'art-cms')); ?>',
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

						var removeContainer = removeButton.closest('[data-art-product-homepage-image]');

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
	 * Sanitize featured homepage meta.
	 *
	 * @param mixed $value Raw value.
	 */
	public function sanitize_featured_meta($value): string {
		return '1' === (string) $value ? '1' : '';
	}

	/**
	 * Authorize editing registered Product homepage meta.
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
	 * Get homepage image attachment ID.
	 *
	 * @param int $post_id Product post ID.
	 */
	private function get_homepage_image_id(int $post_id): int {
		$image_id = get_post_meta($post_id, self::META_IMAGE_ID, true);
		$image_id = is_numeric($image_id) ? absint($image_id) : 0;

		return $image_id > 0 && wp_attachment_is_image($image_id) ? $image_id : 0;
	}

	/**
	 * Check whether homepage fields can be saved.
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
	 * Check whether the current admin screen is a Product edit screen.
	 */
	private function is_product_edit_screen(): bool {
		$screen = get_current_screen();

		return null !== $screen && ProductPostType::KEY === $screen->post_type && in_array($screen->base, array('post', 'post-new'), true);
	}
}
