<?php
/**
 * Gallery collection media data contract.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Gallery;

use ArtCms\PostTypes\GalleryItemPostType;

/**
 * Registers and sanitizes gallery media meta.
 */
final class GalleryMedia {
	public const META_MEDIA_ITEMS = '_art_gallery_media_items';
	public const META_COVER_MEDIA_ITEM_ID = '_art_gallery_cover_media_item_id';

	private const TYPE_IMAGE = 'image';
	private const TYPE_UPLOADED_VIDEO = 'uploaded_video';
	private const TYPE_EXTERNAL_VIDEO = 'external_video';

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_action('init', array($this, 'register_meta'));
	}

	/**
	 * Register gallery media post meta.
	 */
	public function register_meta(): void {
		register_post_meta(
			GalleryItemPostType::KEY,
			self::META_MEDIA_ITEMS,
			array(
				'type'              => 'array',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => array($this, 'sanitize_media_items'),
				'auth_callback'     => array($this, 'can_edit_meta'),
				'revisions_enabled' => true,
			)
		);

		register_post_meta(
			GalleryItemPostType::KEY,
			self::META_COVER_MEDIA_ITEM_ID,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => array($this, 'sanitize_media_item_id'),
				'auth_callback'     => array($this, 'can_edit_meta'),
				'revisions_enabled' => true,
			)
		);
	}

	/**
	 * Sanitize ordered gallery media items.
	 *
	 * @param mixed $items Raw media item list.
	 * @return array<int, array<string, int|string>>
	 */
	public function sanitize_media_items($items): array {
		if (! is_array($items)) {
			return array();
		}

		$sanitized_items = array();

		foreach ($items as $item) {
			if (! is_array($item)) {
				continue;
			}

			$sanitized_item = $this->sanitize_media_item($item);

			if (array() === $sanitized_item) {
				continue;
			}

			$sanitized_items[] = $sanitized_item;
		}

		return $sanitized_items;
	}

	/**
	 * Sanitize a gallery media item stable ID.
	 *
	 * @param mixed $item_id Raw item ID.
	 */
	public function sanitize_media_item_id($item_id): string {
		return sanitize_key((string) $item_id);
	}

	/**
	 * Authorize editing registered gallery media meta.
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
	 * Sanitize one gallery media item.
	 *
	 * @param array<mixed> $item Raw media item.
	 * @return array<string, int|string>
	 */
	private function sanitize_media_item(array $item): array {
		$id = isset($item['id']) ? $this->sanitize_media_item_id($item['id']) : '';
		$type = isset($item['type']) ? sanitize_key((string) $item['type']) : '';

		if ('' === $id || ! in_array($type, $this->get_supported_types(), true)) {
			return array();
		}

		$base_item = array(
			'id'      => $id,
			'type'    => $type,
			'title'   => isset($item['title']) ? sanitize_text_field((string) $item['title']) : '',
			'caption' => isset($item['caption']) ? sanitize_textarea_field((string) $item['caption']) : '',
			'credit'  => isset($item['credit']) ? sanitize_text_field((string) $item['credit']) : '',
		);

		if (self::TYPE_EXTERNAL_VIDEO === $type) {
			$url = isset($item['url']) ? esc_url_raw((string) $item['url']) : '';

			if ('' === $url) {
				return array();
			}

			$base_item['url'] = $url;

			return $base_item;
		}

		$attachment_id = isset($item['attachment_id']) ? absint($item['attachment_id']) : 0;

		if (0 === $attachment_id) {
			return array();
		}

		if (self::TYPE_IMAGE === $type && function_exists('wp_attachment_is_image') && ! wp_attachment_is_image($attachment_id)) {
			return array();
		}

		if (self::TYPE_UPLOADED_VIDEO === $type && function_exists('wp_attachment_is')) {
			$is_video = wp_attachment_is('video', $attachment_id);

			if (! $is_video) {
				return array();
			}
		}

		$base_item['attachment_id'] = $attachment_id;

		return $base_item;
	}

	/**
	 * Get supported gallery media item types.
	 *
	 * @return array<int, string>
	 */
	private function get_supported_types(): array {
		return array(
			self::TYPE_IMAGE,
			self::TYPE_UPLOADED_VIDEO,
			self::TYPE_EXTERNAL_VIDEO,
		);
	}
}
