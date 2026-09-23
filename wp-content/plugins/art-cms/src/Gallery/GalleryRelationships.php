<?php
/**
 * Gallery collection relationships.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Gallery;

use ArtCms\PostTypes\GalleryItemPostType;
use ArtCms\PostTypes\ProductPostType;
use WP_Post;

/**
 * Manages Gallery Collection related Product data and admin UI.
 */
final class GalleryRelationships {
	public const META_RELATED_PRODUCTS = '_art_gallery_related_products';

	private const NONCE_ACTION = 'art_gallery_relationships';
	private const NONCE_NAME = 'art_gallery_relationships_nonce';
	private const FIELD_RELATED_PRODUCTS = 'art_gallery_related_products';

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_action('init', array($this, 'register_meta'));
		add_action('add_meta_boxes_' . GalleryItemPostType::KEY, array($this, 'add_meta_box'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
		add_action('save_post_' . GalleryItemPostType::KEY, array($this, 'save'), 10, 2);
	}

	/**
	 * Register gallery relationship post meta.
	 */
	public function register_meta(): void {
		register_post_meta(
			GalleryItemPostType::KEY,
			self::META_RELATED_PRODUCTS,
			array(
				'type'              => 'array',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => array($this, 'sanitize_product_ids'),
				'auth_callback'     => array($this, 'can_edit_meta'),
				'revisions_enabled' => true,
			)
		);
	}

	/**
	 * Register the Related Products meta box.
	 */
	public function add_meta_box(): void {
		add_meta_box(
			'art-gallery-related-products',
			__('Related Products', 'art-cms'),
			array($this, 'render_meta_box'),
			GalleryItemPostType::KEY,
			'side',
			'default'
		);
	}

	/**
	 * Enqueue admin filtering script on gallery collection edit screens.
	 */
	public function enqueue_assets(): void {
		$screen = get_current_screen();

		if (null === $screen || GalleryItemPostType::KEY !== $screen->post_type || ! in_array($screen->base, array('post', 'post-new'), true)) {
			return;
		}

		wp_enqueue_script(
			'art-gallery-relationships',
			plugins_url('assets/admin/gallery-relationships.js', ART_CMS_FILE),
			array(),
			ART_CMS_VERSION,
			true
		);
	}

	/**
	 * Render the Related Products selector.
	 *
	 * @param WP_Post $post Current gallery collection post.
	 */
	public function render_meta_box(WP_Post $post): void {
		wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

		$selected_ids = $this->get_related_product_ids($post->ID);
		$products = $this->get_available_products();
		?>
		<div data-art-gallery-related-products>
			<p>
				<label for="art-gallery-related-products-search"><?php esc_html_e('Search', 'art-cms'); ?></label>
				<input
					id="art-gallery-related-products-search"
					type="search"
					class="widefat"
					data-art-gallery-related-products-search
				>
			</p>

			<?php if (empty($products)) : ?>
				<p><?php esc_html_e('No Products found.', 'art-cms'); ?></p>
			<?php else : ?>
				<div data-art-gallery-related-products-list>
					<?php foreach ($products as $product) : ?>
						<?php $product_title = get_the_title($product); ?>
						<label
							style="display: block; margin-block-end: 0.5rem;"
							data-art-gallery-related-product-row
							data-art-gallery-related-product-title="<?php echo esc_attr(strtolower($product_title)); ?>"
						>
							<input
								type="checkbox"
								name="<?php echo esc_attr(self::FIELD_RELATED_PRODUCTS); ?>[]"
								value="<?php echo esc_attr((string) $product->ID); ?>"
								<?php checked(in_array($product->ID, $selected_ids, true)); ?>
							>
							<?php echo esc_html($product_title); ?>
						</label>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Save selected related Products.
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post Current post object.
	 */
	public function save(int $post_id, WP_Post $post): void {
		if (! $this->can_save($post_id, $post)) {
			return;
		}

		$product_ids = isset($_POST[self::FIELD_RELATED_PRODUCTS]) && is_array($_POST[self::FIELD_RELATED_PRODUCTS])
			? $this->sanitize_product_ids(wp_unslash($_POST[self::FIELD_RELATED_PRODUCTS]))
			: array();

		if (empty($product_ids)) {
			delete_post_meta($post_id, self::META_RELATED_PRODUCTS);
			return;
		}

		update_post_meta($post_id, self::META_RELATED_PRODUCTS, $product_ids);
	}

	/**
	 * Sanitize Product IDs.
	 *
	 * @param mixed $product_ids Raw product IDs.
	 * @return array<int, int>
	 */
	public function sanitize_product_ids($product_ids): array {
		if (! is_array($product_ids)) {
			return array();
		}

		$sanitized_ids = array();

		foreach ($product_ids as $product_id) {
			$product_id = absint($product_id);

			if (0 === $product_id || isset($sanitized_ids[$product_id])) {
				continue;
			}

			if (! $this->is_valid_product_id($product_id)) {
				continue;
			}

			$sanitized_ids[$product_id] = $product_id;
		}

		return array_values($sanitized_ids);
	}

	/**
	 * Authorize editing registered gallery relationship meta.
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
	 * Get selected related Product IDs.
	 *
	 * @param int $post_id Gallery collection post ID.
	 * @return array<int, int>
	 */
	private function get_related_product_ids(int $post_id): array {
		return $this->sanitize_product_ids(get_post_meta($post_id, self::META_RELATED_PRODUCTS, true));
	}

	/**
	 * Get Products available for relationship selection.
	 *
	 * @return array<int, WP_Post>
	 */
	private function get_available_products(): array {
		$products = get_posts(
			array(
				'post_type'      => ProductPostType::KEY,
				'post_status'    => array('publish', 'draft', 'pending', 'future', 'private'),
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		return array_values(
			array_filter(
				$products,
				static function ($product): bool {
					return $product instanceof WP_Post;
				}
			)
		);
	}

	/**
	 * Check whether a Product ID can be related to a Gallery Collection.
	 *
	 * @param int $product_id Product post ID.
	 */
	private function is_valid_product_id(int $product_id): bool {
		$product = get_post($product_id);

		return $product instanceof WP_Post
			&& ProductPostType::KEY === $product->post_type
			&& 'trash' !== $product->post_status;
	}

	/**
	 * Check whether relationships can be saved.
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post Current post object.
	 */
	private function can_save(int $post_id, WP_Post $post): bool {
		if (GalleryItemPostType::KEY !== $post->post_type) {
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
}
