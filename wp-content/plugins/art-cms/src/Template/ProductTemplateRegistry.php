<?php
/**
 * Product template registry.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Template;

/**
 * Owns developer-defined product template definitions.
 */
final class ProductTemplateRegistry {
	public const META_SELECTED_TEMPLATE = '_art_product_template';

	public const FIELD_DESCRIPTION = 'description';
	public const FIELD_SHORT_DESCRIPTION = 'short_description';
	public const FIELD_PRODUCT_CODE = 'product_code';
	public const FIELD_DESIGN_YEAR = 'design_year';
	public const FIELD_COVER = 'cover';
	public const FIELD_CATEGORIES = 'categories';

	public const STATE_REQUIRED = 'required';
	public const STATE_OPTIONAL = 'optional';
	public const STATE_HIDDEN = 'hidden';

	/**
	 * Get all product templates.
	 *
	 * @return array<string, array{label: string, fields: array<string, string>}>
	 */
	public function get_templates(): array {
		return array(
			'standard' => array(
				'label'  => __('Standard', 'art-cms'),
				'fields' => array(
					self::FIELD_DESCRIPTION       => self::STATE_REQUIRED,
					self::FIELD_SHORT_DESCRIPTION => self::STATE_OPTIONAL,
					self::FIELD_PRODUCT_CODE      => self::STATE_OPTIONAL,
					self::FIELD_DESIGN_YEAR       => self::STATE_OPTIONAL,
					self::FIELD_COVER             => self::STATE_REQUIRED,
					self::FIELD_CATEGORIES        => self::STATE_OPTIONAL,
				),
			),
			'minimal'  => array(
				'label'  => __('Minimal', 'art-cms'),
				'fields' => array(
					self::FIELD_DESCRIPTION       => self::STATE_OPTIONAL,
					self::FIELD_SHORT_DESCRIPTION => self::STATE_REQUIRED,
					self::FIELD_PRODUCT_CODE      => self::STATE_HIDDEN,
					self::FIELD_DESIGN_YEAR       => self::STATE_HIDDEN,
					self::FIELD_COVER             => self::STATE_REQUIRED,
					self::FIELD_CATEGORIES        => self::STATE_OPTIONAL,
				),
			),
		);
	}

	/**
	 * Check whether a product template exists.
	 *
	 * @param string $template_key Template key.
	 */
	public function has_template(string $template_key): bool {
		return array_key_exists($template_key, $this->get_templates());
	}

	/**
	 * Sanitize a selected product template key.
	 *
	 * @param mixed $template_key Raw template key.
	 */
	public function sanitize_template_key($template_key): string {
		$template_key = sanitize_key((string) $template_key);

		return $this->has_template($template_key) ? $template_key : '';
	}

	/**
	 * Get the selected template key for a product.
	 *
	 * @param int $post_id Product post ID.
	 */
	public function get_selected_template_key(int $post_id): string {
		$template_key = get_post_meta($post_id, self::META_SELECTED_TEMPLATE, true);

		return $this->sanitize_template_key(is_string($template_key) ? $template_key : '');
	}

	/**
	 * Get a field state for a template.
	 *
	 * @param string $template_key Template key.
	 * @param string $field_key Field key.
	 */
	public function get_field_state(string $template_key, string $field_key): string {
		$templates = $this->get_templates();

		if (! isset($templates[$template_key]['fields'][$field_key])) {
			return self::STATE_HIDDEN;
		}

		$state = $templates[$template_key]['fields'][$field_key];

		if (! in_array($state, array(self::STATE_REQUIRED, self::STATE_OPTIONAL, self::STATE_HIDDEN), true)) {
			return self::STATE_HIDDEN;
		}

		return $state;
	}

	/**
	 * Get template definitions for admin JavaScript.
	 *
	 * @return array<string, array{label: string, fields: array<string, string>}>
	 */
	public function get_templates_for_script(): array {
		return $this->get_templates();
	}
}
