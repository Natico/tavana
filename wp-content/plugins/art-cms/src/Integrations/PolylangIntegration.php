<?php
/**
 * Polylang integration.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Integrations;

use ArtCms\PostTypes\ProductPostType;
use ArtCms\Product\ProductFields;
use ArtCms\Product\ProductHomepageFields;
use ArtCms\Template\ProductTemplateRegistry;

/**
 * Integrates shared Product metadata with Polylang.
 */
final class PolylangIntegration {
	private const META_FEATURED_IMAGE = '_thumbnail_id';

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks(): void {
		add_filter('pll_copy_post_metas', array($this, 'add_product_metas_to_sync'), 10, 5);
		add_filter('pll_translate_post_meta', array($this, 'translate_attachment_meta'), 10, 3);
	}

	/**
	 * Add shared Product metadata to Polylang's copy/sync list.
	 *
	 * @param array<int, string> $meta_keys Meta keys selected by Polylang.
	 * @param bool              $sync Whether Polylang is synchronizing an existing translation.
	 * @param int|null          $from Source post ID.
	 * @param int|null          $to Target post ID.
	 * @param string|null       $lang Target language slug.
	 * @return array<int, string>
	 */
	public function add_product_metas_to_sync(array $meta_keys, bool $sync = false, ?int $from = null, ?int $to = null, ?string $lang = null): array {
		unset($sync, $to, $lang);

		if (null !== $from && ! $this->is_product_post($from)) {
			return $meta_keys;
		}

		return array_values(
			array_unique(
				array_merge(
					$meta_keys,
					array(
						ProductFields::META_PRODUCT_CODE,
						ProductFields::META_DESIGN_YEAR,
						ProductTemplateRegistry::META_SELECTED_TEMPLATE,
						ProductHomepageFields::META_FEATURED,
						ProductHomepageFields::META_IMAGE_ID,
						self::META_FEATURED_IMAGE,
					)
				)
			)
		);
	}

	/**
	 * Translate attachment IDs when Polylang copies/synchronizes Product attachment meta.
	 *
	 * @param mixed  $value Meta value.
	 * @param string $meta_key Meta key.
	 * @param string $lang Target language slug.
	 * @return mixed
	 */
	public function translate_attachment_meta($value, string $meta_key, string $lang) {
		if (! in_array($meta_key, array(self::META_FEATURED_IMAGE, ProductHomepageFields::META_IMAGE_ID), true)) {
			return $value;
		}

		$attachment_id = is_numeric($value) ? absint($value) : 0;

		if (0 === $attachment_id || ! function_exists('pll_get_post')) {
			return $value;
		}

		$translated_attachment_id = pll_get_post($attachment_id, $lang);

		return $translated_attachment_id ? $translated_attachment_id : $value;
	}

	/**
	 * Check whether a post ID belongs to a Product.
	 *
	 * @param int $post_id Post ID.
	 */
	private function is_product_post(int $post_id): bool {
		return ProductPostType::KEY === get_post_type($post_id);
	}
}
