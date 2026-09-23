<?php
/**
 * Gallery collection media manager admin UI.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Gallery;

use ArtCms\PostTypes\GalleryItemPostType;
use WP_Post;

/**
 * Manages the gallery collection media editor.
 */
final class GalleryMediaEditor {
	private const NONCE_ACTION = 'art_gallery_media_editor';
	private const NONCE_NAME = 'art_gallery_media_editor_nonce';
	private const FIELD_MEDIA_ITEMS = 'art_gallery_media_items';
	private const FIELD_COVER_MEDIA_ITEM_ID = 'art_gallery_cover_media_item_id';

	/**
	 * Gallery media data contract.
	 */
	private GalleryMedia $media;

	/**
	 * Constructor.
	 *
	 * @param GalleryMedia $media Gallery media data contract.
	 */
	public function __construct(GalleryMedia $media) {
		$this->media = $media;
	}

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_action('add_meta_boxes_' . GalleryItemPostType::KEY, array($this, 'add_meta_box'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
		add_action('save_post_' . GalleryItemPostType::KEY, array($this, 'save'), 10, 2);
	}

	/**
	 * Register the Media meta box.
	 */
	public function add_meta_box(): void {
		add_meta_box(
			'art-gallery-media',
			__('Media', 'art-cms'),
			array($this, 'render_meta_box'),
			GalleryItemPostType::KEY,
			'normal',
			'high'
		);
	}

	/**
	 * Enqueue media manager admin assets on gallery collection edit screens.
	 */
	public function enqueue_assets(): void {
		$screen = get_current_screen();

		if (null === $screen || GalleryItemPostType::KEY !== $screen->post_type || ! in_array($screen->base, array('post', 'post-new'), true)) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script('jquery-ui-sortable');

		wp_enqueue_style(
			'art-gallery-media-editor',
			plugins_url('assets/admin/gallery-media.css', ART_CMS_FILE),
			array(),
			ART_CMS_VERSION
		);

		wp_enqueue_script(
			'art-gallery-media-editor',
			plugins_url('assets/admin/gallery-media.js', ART_CMS_FILE),
			array('jquery', 'jquery-ui-sortable'),
			ART_CMS_VERSION,
			true
		);

		wp_localize_script(
			'art-gallery-media-editor',
			'artGalleryMediaEditorSettings',
			array(
				'labels' => array(
					'addImageTitle'         => __('Select Image', 'art-cms'),
					'addImageButton'        => __('Use this image', 'art-cms'),
					'addVideoTitle'         => __('Select Uploaded Video', 'art-cms'),
					'addVideoButton'        => __('Use this video', 'art-cms'),
					'image'                 => __('Image', 'art-cms'),
					'uploadedVideo'         => __('Uploaded Video', 'art-cms'),
					'externalVideo'         => __('External Video', 'art-cms'),
					'title'                 => __('Title', 'art-cms'),
					'caption'               => __('Caption', 'art-cms'),
					'credit'                => __('Credit', 'art-cms'),
					'url'                   => __('URL', 'art-cms'),
					'setAsCover'            => __('Set as Cover', 'art-cms'),
					'remove'                => __('Remove', 'art-cms'),
					'drag'                  => __('Drag', 'art-cms'),
					'moveUp'                => __('Move Up', 'art-cms'),
					'moveDown'              => __('Move Down', 'art-cms'),
					'noMedia'               => __('No media added yet.', 'art-cms'),
					'confirmRemove'         => __('Remove this media item from the Gallery Collection?', 'art-cms'),
					'attachment'            => __('Attachment', 'art-cms'),
					'externalVideoNoUrl'    => __('External video URL not set.', 'art-cms'),
					'uploadedVideoNoSource' => __('Uploaded video attachment', 'art-cms'),
				),
			)
		);
	}

	/**
	 * Render the Gallery Media Manager meta box.
	 *
	 * @param WP_Post $post Current gallery collection post.
	 */
	public function render_meta_box(WP_Post $post): void {
		wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

		$media_items = $this->get_media_items_for_script($post->ID);
		$cover_id = $this->media->sanitize_media_item_id(get_post_meta($post->ID, GalleryMedia::META_COVER_MEDIA_ITEM_ID, true));
		$media_items_json = wp_json_encode($media_items);

		if (! is_string($media_items_json)) {
			$media_items_json = '[]';
		}
		?>
		<div
			class="art-gallery-media-manager"
			data-art-gallery-media-manager
			data-initial-items="<?php echo esc_attr($media_items_json); ?>"
			data-initial-cover-id="<?php echo esc_attr($cover_id); ?>"
		>
			<input
				type="hidden"
				name="<?php echo esc_attr(self::FIELD_MEDIA_ITEMS); ?>"
				value=""
				data-art-gallery-media-items-field
			>
			<input
				type="hidden"
				name="<?php echo esc_attr(self::FIELD_COVER_MEDIA_ITEM_ID); ?>"
				value="<?php echo esc_attr($cover_id); ?>"
				data-art-gallery-cover-field
			>

			<div class="art-gallery-media-manager__actions">
				<button type="button" class="button" data-art-gallery-add-image><?php esc_html_e('+ Image', 'art-cms'); ?></button>
				<button type="button" class="button" data-art-gallery-add-video><?php esc_html_e('+ Uploaded Video', 'art-cms'); ?></button>
				<button type="button" class="button" data-art-gallery-add-external-video><?php esc_html_e('+ External Video', 'art-cms'); ?></button>
			</div>

			<p class="description"><?php esc_html_e('Manage ordered Gallery Collection media. Media Library attachments are referenced, not copied.', 'art-cms'); ?></p>

			<div class="art-gallery-media-manager__empty" data-art-gallery-empty>
				<?php esc_html_e('No media added yet.', 'art-cms'); ?>
			</div>

			<div class="art-gallery-media-manager__items" data-art-gallery-media-items></div>
		</div>
		<?php
	}

	/**
	 * Save gallery media items and cover selection.
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post Current post object.
	 */
	public function save(int $post_id, WP_Post $post): void {
		if (! $this->can_save($post_id, $post)) {
			return;
		}

		$raw_items = isset($_POST[self::FIELD_MEDIA_ITEMS]) && is_string($_POST[self::FIELD_MEDIA_ITEMS])
			? wp_unslash($_POST[self::FIELD_MEDIA_ITEMS])
			: '[]';

		$decoded_items = json_decode($raw_items, true);
		$media_items = $this->media->sanitize_media_items(is_array($decoded_items) ? $decoded_items : array());

		if (array() === $media_items) {
			delete_post_meta($post_id, GalleryMedia::META_MEDIA_ITEMS);
			delete_post_meta($post_id, GalleryMedia::META_COVER_MEDIA_ITEM_ID);
			return;
		}

		update_post_meta($post_id, GalleryMedia::META_MEDIA_ITEMS, $media_items);

		$cover_id = isset($_POST[self::FIELD_COVER_MEDIA_ITEM_ID]) && is_string($_POST[self::FIELD_COVER_MEDIA_ITEM_ID])
			? $this->media->sanitize_media_item_id(wp_unslash($_POST[self::FIELD_COVER_MEDIA_ITEM_ID]))
			: '';
		$cover_id = $this->get_valid_cover_id($cover_id, $media_items);

		if ('' === $cover_id) {
			delete_post_meta($post_id, GalleryMedia::META_COVER_MEDIA_ITEM_ID);
			return;
		}

		update_post_meta($post_id, GalleryMedia::META_COVER_MEDIA_ITEM_ID, $cover_id);
	}

	/**
	 * Check whether the gallery media can be saved.
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

	/**
	 * Get saved gallery media items prepared with admin display data.
	 *
	 * @param int $post_id Gallery collection post ID.
	 * @return array<int, array<string, int|string>>
	 */
	private function get_media_items_for_script(int $post_id): array {
		$items = $this->media->sanitize_media_items(get_post_meta($post_id, GalleryMedia::META_MEDIA_ITEMS, true));

		foreach ($items as $index => $item) {
			if (isset($item['attachment_id']) && is_int($item['attachment_id'])) {
				$items[$index] = array_merge($item, $this->get_attachment_display_data($item['attachment_id'], (string) $item['type']));
			}
		}

		return $items;
	}

	/**
	 * Get attachment display data for the admin UI.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $type Gallery media item type.
	 * @return array<string, string>
	 */
	private function get_attachment_display_data(int $attachment_id, string $type): array {
		$attachment_title = get_the_title($attachment_id);
		$attachment_url = wp_get_attachment_url($attachment_id);
		$display = array(
			'attachment_label' => '' !== $attachment_title ? $attachment_title : basename((string) $attachment_url),
			'preview_url'      => '',
		);

		if ('image' === $type) {
			$image = wp_get_attachment_image_src($attachment_id, 'thumbnail');
			$display['preview_url'] = is_array($image) && isset($image[0]) ? (string) $image[0] : '';
		}

		return $display;
	}

	/**
	 * Return the cover ID only when it references a surviving Image media item.
	 *
	 * @param string                              $cover_id Selected cover media item ID.
	 * @param array<int, array<string, int|string>> $media_items Sanitized media items.
	 */
	private function get_valid_cover_id(string $cover_id, array $media_items): string {
		if ('' === $cover_id) {
			return '';
		}

		foreach ($media_items as $item) {
			if ($cover_id === ($item['id'] ?? '') && 'image' === ($item['type'] ?? '')) {
				return $cover_id;
			}
		}

		return '';
	}
}
