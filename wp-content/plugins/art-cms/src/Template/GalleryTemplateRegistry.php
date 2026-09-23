<?php
/**
 * Gallery template registry.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Template;

/**
 * Owns developer-defined gallery template definitions.
 */
final class GalleryTemplateRegistry {
	public const META_SELECTED_TEMPLATE = '_art_gallery_template';

	public const DEFAULT_TEMPLATE = 'standard';

	/**
	 * Get all gallery templates.
	 *
	 * @return array<string, array{label: string}>
	 */
	public function get_templates(): array {
		return array(
			'standard' => array(
				'label' => __('Standard', 'art-cms'),
			),
			'minimal'  => array(
				'label' => __('Minimal', 'art-cms'),
			),
		);
	}

	/**
	 * Check whether a gallery template exists.
	 *
	 * @param string $template_key Template key.
	 */
	public function has_template(string $template_key): bool {
		return array_key_exists($template_key, $this->get_templates());
	}

	/**
	 * Sanitize a selected gallery template key.
	 *
	 * @param mixed $template_key Raw template key.
	 */
	public function sanitize_template_key($template_key): string {
		$template_key = sanitize_key((string) $template_key);

		return $this->has_template($template_key) ? $template_key : '';
	}

	/**
	 * Get the selected template key for a gallery collection.
	 *
	 * @param int $post_id Gallery collection post ID.
	 */
	public function get_selected_template_key(int $post_id): string {
		$template_key = get_post_meta($post_id, self::META_SELECTED_TEMPLATE, true);
		$template_key = $this->sanitize_template_key(is_string($template_key) ? $template_key : '');

		return '' === $template_key ? self::DEFAULT_TEMPLATE : $template_key;
	}
}
