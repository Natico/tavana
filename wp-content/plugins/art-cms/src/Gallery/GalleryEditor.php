<?php
/**
 * Gallery collection editor behavior.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Gallery;

use ArtCms\PostTypes\GalleryItemPostType;
use ArtCms\Template\GalleryTemplateRegistry;
use WP_Post;

/**
 * Manages the gallery collection editing screen.
 */
final class GalleryEditor {
	private const TEMPLATE_FIELD = 'art_gallery_template';
	private const DESCRIPTION_FIELD = 'art_gallery_description';
	private const NONCE_ACTION = 'art_gallery_editor';
	private const NONCE_NAME = 'art_gallery_editor_nonce';

	/**
	 * Gallery template registry.
	 */
	private GalleryTemplateRegistry $templates;

	/**
	 * Constructor.
	 *
	 * @param GalleryTemplateRegistry $templates Gallery template registry.
	 */
	public function __construct(GalleryTemplateRegistry $templates) {
		$this->templates = $templates;
	}

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_action('init', array($this, 'register_meta'));
		add_filter('use_block_editor_for_post_type', array($this, 'disable_block_editor'), 10, 2);
		add_action('add_meta_boxes_' . GalleryItemPostType::KEY, array($this, 'add_template_meta_box'));
		add_action('add_meta_boxes_' . GalleryItemPostType::KEY, array($this, 'add_description_meta_box'));
		add_filter('wp_insert_post_data', array($this, 'map_description_to_post_content'), 10, 4);
		add_action('save_post_' . GalleryItemPostType::KEY, array($this, 'save_selected_template'), 10, 2);
	}

	/**
	 * Register gallery editor meta.
	 */
	public function register_meta(): void {
		register_post_meta(
			GalleryItemPostType::KEY,
			GalleryTemplateRegistry::META_SELECTED_TEMPLATE,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => array($this->templates, 'sanitize_template_key'),
				'auth_callback'     => array($this, 'can_edit_template_meta'),
				'revisions_enabled' => true,
			)
		);
	}

	/**
	 * Disable the block editor for gallery collections only.
	 *
	 * @param bool   $use_block_editor Whether to use the block editor.
	 * @param string $post_type Post type key.
	 */
	public function disable_block_editor(bool $use_block_editor, string $post_type): bool {
		if (GalleryItemPostType::KEY !== $post_type) {
			return $use_block_editor;
		}

		return false;
	}

	/**
	 * Register the Gallery Template meta box.
	 */
	public function add_template_meta_box(): void {
		add_meta_box(
			'art-gallery-template',
			__('Template', 'art-cms'),
			array($this, 'render_template_meta_box'),
			GalleryItemPostType::KEY,
			'normal',
			'high'
		);
	}

	/**
	 * Render the gallery template selector.
	 *
	 * @param WP_Post $post Current gallery collection post.
	 */
	public function render_template_meta_box(WP_Post $post): void {
		wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

		$selected_template = $this->templates->get_selected_template_key($post->ID);
		?>
		<p>
			<label for="art-gallery-template"><?php esc_html_e('Template', 'art-cms'); ?> <span aria-hidden="true">*</span></label>
			<select id="art-gallery-template" class="widefat" name="<?php echo esc_attr(self::TEMPLATE_FIELD); ?>">
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
	 * Register the Gallery Description meta box.
	 */
	public function add_description_meta_box(): void {
		add_meta_box(
			'art-gallery-description',
			__('Description', 'art-cms'),
			array($this, 'render_description_meta_box'),
			GalleryItemPostType::KEY,
			'normal',
			'high'
		);
	}

	/**
	 * Render a plain-text gallery description textarea.
	 *
	 * @param WP_Post $post Current gallery collection post.
	 */
	public function render_description_meta_box(WP_Post $post): void {
		?>
		<p>
			<label for="art-gallery-description"><?php esc_html_e('Description', 'art-cms'); ?></label>
			<textarea id="art-gallery-description" class="widefat" name="<?php echo esc_attr(self::DESCRIPTION_FIELD); ?>" rows="8"><?php echo esc_textarea($post->post_content); ?></textarea>
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
	 * Save selected gallery template.
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
			: GalleryTemplateRegistry::DEFAULT_TEMPLATE;

		$template_key = $this->templates->sanitize_template_key($raw_template);

		if ('' === $template_key) {
			return;
		}

		update_post_meta($post_id, GalleryTemplateRegistry::META_SELECTED_TEMPLATE, $template_key);
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

		if (GalleryItemPostType::KEY !== $post_type) {
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

		return $this->current_user_can_edit_gallery($postarr, $update);
	}

	/**
	 * Check whether the selected template can be saved.
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post Current post object.
	 */
	private function can_save_template(int $post_id, WP_Post $post): bool {
		if (GalleryItemPostType::KEY !== $post->post_type) {
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
	 * Check the current user's gallery collection edit capability.
	 *
	 * @param array<string, mixed> $postarr Raw post data.
	 * @param bool                 $update Whether this is an existing post update.
	 */
	private function current_user_can_edit_gallery(array $postarr, bool $update): bool {
		$post_id = isset($postarr['ID']) ? absint($postarr['ID']) : 0;

		if ($update && $post_id > 0) {
			return current_user_can('edit_post', $post_id);
		}

		$post_type_object = get_post_type_object(GalleryItemPostType::KEY);

		if (null === $post_type_object) {
			return false;
		}

		return current_user_can($post_type_object->cap->edit_posts);
	}
}
