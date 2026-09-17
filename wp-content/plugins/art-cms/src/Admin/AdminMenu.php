<?php
/**
 * Admin menu registration.
 *
 * @package ArtCms
 */

declare(strict_types=1);

namespace ArtCms\Admin;

/**
 * Registers the plugin parent admin menu.
 */
final class AdminMenu {
	public const SLUG = 'art-cms';

	/**
	 * Register the parent menu used by Art CMS post types.
	 */
	public function register(): void {
		add_menu_page(
			__('ART', 'art-cms'),
			__('ART', 'art-cms'),
			'edit_posts',
			self::SLUG,
			array($this, 'render'),
			'dashicons-art',
			26
		);

		remove_submenu_page(self::SLUG, self::SLUG);
	}

	/**
	 * Render the parent menu landing page.
	 */
	public function render(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e('ART', 'art-cms'); ?></h1>
			<p><?php esc_html_e('Manage ART project content from the menu items below.', 'art-cms'); ?></p>
		</div>
		<?php
	}
}
